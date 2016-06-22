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
                    $xml=DeltaOutputTemplate::getElementalRest()->getTemplate(
                            ConfigConsts::DELTA_PREMIUM_EVENT_OUTPUT_TEMPLATE, "DeltaPremiumLiveOutputTemplate");                
            } else {
                $xml=DeltaOutputTemplate::getElementalRest()->getTemplate(
                        ConfigConsts::DELTA_STD_EVENT_OUTPUT_TEMPLATE, "DeltaStdLiveOutputTemplate");
            }
        } else {
            if( $level == 'premium' ){
                $xml=DeltaOutputTemplate::getElementalRest()->getTemplate(
                        ConfigConsts::DELTA_PREMIUM_VOD_OUTPUT_TEMPLATE, "DeltaPremiumVODOutputTemplate");                
            } else {
                $xml=DeltaOutputTemplate::getElementalRest()->getTemplate(
                        ConfigConsts::DELTA_STD_VOD_OUTPUT_TEMPLATE, "DeltaStdVODOutputTemplate");
            }
        }
            $axClientID = cleanClientID( $clientID );
            $xml->name = $axClientID.'_'.$type;
            //acerta custom url para cada output filter
            foreach( $xml->filter as $filter ){
                if( (boolean)$filter->endpoint ) {
                    $filter->output_url = $axClientID.'/'.$type.'/$fn$.$ex$';
                }
            } 
//             print($xml->/*asXml*/());
            return new self(DeltaOutputTemplate::getElementalRest()->postRecord(null, null, $xml));
        }

        
        // 567161777222444 Raimara
        
        public function setPropertiesFromXML( $xml ) {
            $this->name = (string)$xml->name;
            $this->xml = $xml;
            $this->href = (string)$xml['href'];
            $this->id = end(explode('/', $xml['href']));
            $this->filters = [];
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
            foreach( DeltaOutputTemplate::getOutputTemplateList() as $xmlOutpTpl ){
                if( $axClientID.'_'.$type === $xmlOutpTpl->name ) {
                    return $xmlOutpTpl;
                }
            }
            if( ! $create ) {
                return null;
            }
            return DeltaOutputTemplate::newOutputTemplate($clientID, $type, $level);
        }
        
        public static function delete($clientID, $type) {
        
            $outTemp = DeltaOutputTemplate::getClientOutputTemplate( $clientID, $type, null, $create=false );
            if( $outTemp == null )
                return false;
//             printf("Deletando %s",$outTemp->id);
            DeltaOutputTemplate::getElementalRest()->restDelete($outTemp->id);
        }

        public static function getOutputTemplate($id) {
            return new self( DeltaOutputTemplate::getElementalRest()->restGet($id));
        }

        public static function getOutputTemplateList($id=null) {
            $templates=[];
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

        protected static $elementalRest = null;
        public static function getElementalRest() {
            if (DeltaOutputTemplate::$elementalRest == null){
                DeltaOutputTemplate::$elementalRest = new ElementalRest($hostname=ConfigConsts::DELTA_HOST,
                        $apiEndpoint='output_templates', $port=ConfigConsts::DELTA_PORT);
            }
            return DeltaOutputTemplate::$elementalRest;
        }
    }
?>
