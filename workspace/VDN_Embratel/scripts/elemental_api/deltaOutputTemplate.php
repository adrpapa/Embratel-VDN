<?php
    require_once "configConsts.php";
    require_once "elementalRest.php";
    require_once "utils.php";

    class DeltaOutputFilter {
        public function __construct($xml) {
            $this->xml=$xml;
            $this->id = end(explode('/', $xml->filter['href']));
            $this->endpoint = $xml->endpoint;
            $this->output_url = $xml->output_url;            
        }
    }

    class DeltaOutputTemplate {
    /*
    ** Classe que gera Input Filters para o Elemental Delta
    */
        public function __construct( $xml ) {
            $this->setPropertiesFromXML($xml);
        }
        // cria objeto DeltaOutputTemplate, e monta XML para inclusão do mesmo
        // Teremos um output template para live, e outro para VOD, a fim de
        // separar os streams para obtenção pelo CDN(VDS).
        // A modalidade do plano (std/live) determina (através do template) 
        // a quantidade e formato dos output filters
        // Parametros: 
        //  $clientID: ID do cliente - será usado no nome do templa/te e nas URLs de output
        //  $type: live | vod
        //  $level: std | premium

        public static function newOutputTemplate( $clientID, $type, $level ) {
            if( $type == 'live' ){
                if( $level == 'premium') {
                    $templateId = ConfigConsts::DELTA_PREMIUM_EVENT_OUTPUT_TEMPLATE;
                } else {
                    $templateId = ConfigConsts::DELTA_STD_EVENT_OUTPUT_TEMPLATE;
                }
            } else {
                if( $level == 'premium' ){
                    $templateId = ConfigConsts::DELTA_PREMIUM_VOD_OUTPUT_TEMPLATE;
                } else {
                    $templateId = ConfigConsts::DELTA_STD_VOD_OUTPUT_TEMPLATE;
                }
            }
            $xml=DeltaOutputTemplate::getElementalRest()->getTemplate($templateId, "DeltaOutputTemplate");
            $axClientID = cleanClientID( $clientID );
            $xml->name = $axClientID.'_'.$type.'_'.$level;

            //acerta custom url para cada output filter
            foreach( $xml->filter as $filter ){
                if( (boolean)$filter->endpoint ) {
                    $filter->output_url = $axClientID.'/'.$type.'/'.$level.'/$fn$.$ex$';
                }
            } 
//          print($xml->/*asXml*/());
            return new self(DeltaOutputTemplate::getElementalRest()->postRecord(null, null, $xml));
        }

        
        // 567161777222444 Raimara
        
        public function setPropertiesFromXML( $xml ) {
            $this->name = (string)$xml->name;
            $this->xml = $xml;
            $this->href = (string)$xml['href'];
            $this->id = end(explode('/', $xml['href']));
            $this->filters = array();
            foreach( $xml->filter as $filter ){
                $deltaOutputFilter = new DeltaOutputFilter($xml);
                $this->filters[$deltaOutputFilter->id]=$deltaOutputFilter;
            }
//             print_r($this);
        }

        /*
        ** Obtem / cria output template para o cliente std/premium ($level)
        ** Parametros:
        **      $clientID
        **      $type: event | vod
        **      $level: std | premium
        */
        public static function getClientOutputTemplate( $clientID, $type, $level, $create=true ) {
            $outputTpl = null;
            $axClientID = cleanClientID( $clientID );
            $templateName = $axClientID.'_'.$type.'_'.$level;
            foreach( DeltaOutputTemplate::getOutputTemplateList() as $xmlOutpTpl ){
                if( $templateName === $xmlOutpTpl->name ) {
                    return $xmlOutpTpl;
                }
            }
            if( ! $create ) {
                return null;
            }
            return DeltaOutputTemplate::newOutputTemplate($clientID, $type, $level);
        }
        
        public static function deleteClient($clientID, $type) {
        
            $outTemp = DeltaOutputTemplate::getClientOutputTemplate( $clientID, $type, null, $create=false );
            if( $outTemp == null )
                return false;
//          printf("Deletando %s",$outTemp->id);
            DeltaOutputTemplate::delete($outTemp->id);
        }
        
        public static function delete($id) {
            DeltaOutputTemplate::getElementalRest()->restDelete($id);
        }

        public static function getOutputTemplate($id) {
            return new self( DeltaOutputTemplate::getElementalRest()->restGet($id));
        }

        public static function getOutputTemplateList($id=null) {
            $templates=array();
            $xml = DeltaOutputTemplate::getElementalRest()->restGet($id);
            if($xml->getName() == 'output_template_list') {
                foreach( $xml as $subxml){
                    $template = new self($subxml);
                    $templates[$template->id] = $template;
                }
            } else {
                $template = new self($xml);
                $templates[$template->id] = $template;
            }
            return $templates;
        }

        public static function getElementalRest() {
            return new ElementalRest($hostname=ConfigConsts::DELTA_HOST,
                        $apiEndpoint='output_templates', $port=ConfigConsts::DELTA_PORT);
        }
    }
?>
