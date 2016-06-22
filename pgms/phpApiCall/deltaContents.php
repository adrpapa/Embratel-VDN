<?php
    require_once "configConsts.php";
    require_once "elementalRest.php";
    require_once "utils.php";

    class DeltaContents {
    /*
    ** Classe que Lista conteúdos do cliente
    */
        public function __construct( $xml ) {
            $this->setPropertiesFromXML($xml);
        }
        
        public function setPropertiesFromXML( $xml ) {
            $this->name = (string)$xml->name;
            $this->path = (string)$xml->path;
            $this->storage_location = (string)$xml->storage_location;
            $this->href = (string)$xml['href'];
            $this->id = end(explode('/', $xml['href']));
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
