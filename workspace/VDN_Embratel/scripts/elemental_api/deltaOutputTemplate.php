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
        //  $clientID: ID do cliente - será usado no nome do template e nas URLs de output
        //  $type: live | vod
        //  $level: std | prm

        public static function newOutputTemplate( $clientID, $type, $proto, $level ) {
            $tpl = ConfigConsts::TEMPLATE_PATH."/DeltaOutput_".$level."_".$proto.".xml";
            if( ! file_exists($tpl) )
                throw new Exception("File $tpl does not exist \n");
            $xml = simplexml_load_file($tpl);
            //$xml=DeltaOutputTemplate::getElementalRest()->getTemplate($templateId, "DeltaOutputTemplate");
            $axClientID = cleanClientID( $clientID );
            $xml->name = $axClientID.'_'.$type.'_'.$proto.'_'.$level;

            //acerta custom url para cada output filter
            foreach( $xml->filter as $filter ){
                if( (boolean)$filter->endpoint ) {
                    $filter->output_url = $axClientID.'/'.$type.'/'.$proto.'/'.$level.'/$fn$';
                }
            } 
            print($xml->asXml()); //$xml->asXml());
            return new self(DeltaOutputTemplate::getElementalRest()->postRecord(null, null, $xml));
        }

        public function setPropertiesFromXML( $xml ) {
            $this->name = (string)$xml->name;
            $this->xml = $xml;
            $this->href = (string)$xml['href'];
            $vl_tmp = explode('/', $xml['href']);
            $this->id = end( $vl_tmp );
            $this->filters = array();
            foreach( $xml->filter as $filter ){
                $deltaOutputFilter = new DeltaOutputFilter($xml);
                $this->filters[$deltaOutputFilter->id]=$deltaOutputFilter;
            }
            print($xml->asXml());
            print_r($this);
        }

        /*
        ** Obtem / cria output template para o cliente std/prm ($level)
        ** Parametros:
        **      $clientID
        **      $type: event | vod
        **      $level: std | prm
        */
        public static function getClientOutputTemplate( $clientID, $type, $proto, $level, $create=true ) {
            $outputTpl = null;
            $axClientID = cleanClientID( $clientID );
            $templateName = $axClientID.'_'.$type.'_'.$proto.'_'.$level;
            foreach( DeltaOutputTemplate::getOutputTemplateList() as $xmlOutpTpl ){
                if( $templateName === $xmlOutpTpl->name ) {
                    return $xmlOutpTpl;
                }
            }
            if( ! $create ) {
                return null;
            }
            return DeltaOutputTemplate::newOutputTemplate($clientID, $type, $proto, $level);
        }
        
        public static function delete($id) {
            try {
                DeltaOutputTemplate::getElementalRest()->restDelete($id);
            }
            catch(Exception $fault) {
                print_r($fault);
            }
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
// $clientid="Client101010";
// $type="vod";
// $proto="http";
// $level="std";
// DeltaOutputTemplate::getCli


/*
curl -H"Accept: text/xml" http://201.31.12.36:8080/api/output_templates/1?clean=true > /var/www/html/ebtvdn/templates/DeltaOutput_std_http.xml
curl -H"Accept: text/xml" http://201.31.12.36:8080/api/output_templates/2?clean=true > /var/www/html/ebtvdn/templates/DeltaOutput_prm_http.xml
curl -H"Accept: text/xml" http://201.31.12.36:8080/api/output_templates/3?clean=true > /var/www/html/ebtvdn/templates/DeltaOutput_std_https.xml
curl -H"Accept: text/xml" http://201.31.12.36:8080/api/output_templates/4?clean=true > /var/www/html/ebtvdn/templates/DeltaOutput_prm_https.xml
*/

?>
