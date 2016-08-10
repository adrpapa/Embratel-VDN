<?php
    require_once "configConsts.php";
    require_once "elementalRest.php";
    require_once "deltaOutputTemplate.php";
    require_once "utils.php";

    class DeltaInputFilter {
    /*
    ** Classe que gera Input Filters para o Elemental Delta
    */
        public function __construct( $xml ) {
            $this->setPropertiesFromXML($xml);
        }

        /*
        ** cria objeto DeltaInputFilter para WebDav
        **
        */
        public static function newWebDavInputFilter( $clientID, $name, $dvr=false, $live=true ) {
            $axClientID = cleanClientID($clientID);
            $axName = cleanClientID($name);
            $input_filter = new SimpleXMLElement("<input_filter></input_filter>");
            $input_filter->addAttribute('product',"Delta");
            $input_filter->addAttribute('version', "1.6.1.34713");
            $input_filter->addChild('filter_type','webdav_input');
            $input_filter->addChild('label', $axClientID.'_'.$name);
            $baseloc = ConfigConsts::DELTA_WEBDAV_STORAGE_LOCATION.$axClientID;
            $settings = $input_filter->addChild("filter_settings");
            $settings->addChild("webdav_user_id", 1);

            if( $live ) {
                $settings->template_id = '';
                $settings->content_window_type = 'keep_seconds';
                $settings->seconds_to_keep = $dvr ? 7200 : 120;
                $settings->storage_location = "$baseloc/live/$axName/";
                $settings->relative_uri="$axClientID/live/$axName";
                $settings->vod_content = "false";
            } else {
                $settings->template_id = '';
                $settings->content_window_type = 'keep_all';
                $settings->storage_location = "$baseloc/vod";
                $settings->relative_uri="$axClientID/vod/$axName";
                $settings->vod_content = "true";
            }
            $deltaInputFilter = DeltaInputFilter::getElementalRest()->postRecord(null, null, $input_filter);
            if(ConfigConsts::debug)
                echo $deltaInputFilter->asXML();
            return new self($deltaInputFilter);
        }
        
        /*
        ** cria objeto DeltaInputFilter para VOD, e monta XML para inclusão do mesmo
        **
        */
        public static function newWfInputFilter( $clientID, $type, $proto, $level ) {
            $tpl = ConfigConsts::TEMPLATE_PATH."/DeltaWFInputFilter.xml";
            if( ! file_exists($tpl) )
                throw new Exception("File $tpl does not exist \n");
            $xml = simplexml_load_file($tpl);

            $axClientID = cleanClientID($clientID);
            $xml->label = $axClientID.'_'.$type.'_'.$proto.'_'.$level;
            $xml->filter_settings->incoming->uri = 
                ConfigConsts::DELTA_WF_INCOMMING_URI.'/'.$axClientID.'/'.$type.'/'.$proto.'/'.$level.'/';
            $xml->filter_settings->search_subfolders = "true";
            // busca o output template para eventos VOD do cliente - cria se não existir
            $outputTemplate = DeltaOutputTemplate::getClientOutputTemplate( $axClientID, $type, $proto, $level, true );
            $xml->filter_settings->template_id = $outputTemplate->id;
//             print '\n\n'.$xml->asXml().'\n\n';
            return new self(DeltaInputFilter::getElementalRest()->postRecord(null, null, $xml));
        }
        
//         /*
//         ** cria objeto DeltaInputFilter para Live, e monta XML para inclusão do mesmo
//         **
//         */
//         public static function newUdpInputFilter( $clientID, $label, $level ) {
//             $xml=DeltaInputFilter::getElementalRest()->getTemplate(
//                     ConfigConsts::DELTA_UDP_INPUT_FILTER_TEMPLATE, "DeltaUDPInputFilter");
//             $axClientID = cleanClientID($clientID);
//             $axLabel = cleanName($label);
//             $xml->label = $axLabel;
//             $xml->filter_settings->storage_location = 
//                 ConfigConsts::DELTA_LIVE_STORAGE_LOCATION.'/'.$axClientID.'/'.$axLabel.'/';
//             // busca o output template para eventos live do cliente - cria se não existir
//             $outputTemplate = DeltaOutputTemplate::getClientOutputTemplate( $axClientID, 'live', $level, true );
//             $xml->filter_settings->template_id = $outputTemplate->id;
//             $xml->filter_settings->udp_input->uri = 'udp://127.0.0.1:'
//                     .DeltaInputFilter::getNextAvilableUdpPort();
//             return new self(DeltaInputFilter::getElementalRest()->postRecord(null, null, $xml));
//         }

        /*
        ** Obtem / cria input filter para o cliente std/prm ($level)
        ** Parametros:
        **      $clientID
        **      $level: std | prm
        */
        public static function getClientInputFilter( $clientID, $type, $proto, $level, $create=true ) {
            $axClientID = cleanClientID( $clientID );
            $label =  $axClientID.'_'.$type.'_'.$proto.'_'.$level;
            foreach( DeltaInputFilter::getInputFilterList() as $xmlInpFilter ){
                if( $label == $xmlInpFilter->label ) {
                    return $xmlInpFilter;
                }
//                 echo "$label # $xmlInpFilter->label\n";
            }
            if( ! $create ) {
                return null;
            }
            return DeltaInputFilter::newWfInputFilter($clientID, $type, $proto, $level);
        }
        
        
        public function setPropertiesFromXML( $xml ) {
            $this->filter_type = $xml->filter_type."";
            $this->label = (string)$xml->label."";
            //$this->inputURI = (string)$xml->filter_settings->udp_input->uri;
            $this->href = (string)$xml['href'];
            $this->id = end(explode('/', $xml['href']));
            //$this->udpPort = end(explode(':', $this->inputURI));
            $this->template_id = $xml->filter_settings->template_id."";
            $this->content_window_type = (string)$xml->filter_settings->content_window_type;
            $this->seconds_to_keep = (string)$xml->filter_settings->seconds_to_keep;
            $this->storage_location = (string)$xml->filter_settings->storage_location;
            $this->incoming_uri = (string)$xml->filter_settings->incoming->uri;
            $storageTokens = explode('/', trim($this->incoming_uri,'/'));
            if( count($storageTokens) < 2 ) {
                $storageTokens = explode('/', trim($this->storage_location,'/'));
            }
            $this->clientID = $storageTokens[count($this->clientID=$storageTokens)-2];
//             print_r($this);
        }

        public static function delete($id) {
            try {
                DeltaInputFilter::getElementalRest()->restDelete($id);
            }
            catch(Exception $fault) {
                echo "\nError deleting Input Filter id:".$id.". Error Message:".$fault->getMessage()."\n\n";
            }
        }

        public static function deleteClientFilters($clientID) {
            
        }
        
        public static function getInputFilterList($id="") {
            return DeltaInputFilter::getElementalRest()->restGet($id);
        }

        public static function getNextAvilableUdpPort() {
            $nextUdpPort = 5000;
            foreach ( DeltaInputFilter::getInputFilterList()->input_filter as $inputFilter) {
                if( $inputFilter->filter_type != 'udp_input' ) {
                    continue;
                }
                $port = end(explode(':', $inputFilter->filter_settings->udp_input->uri));
                if( (int)$port > $nextUdpPort && $port < 8000) {
                    $nextUdpPort = (int)$port;
                }
            }
            return $nextUdpPort + 1;
        }

        public static function getElementalRest() {
            return new ElementalRest($hostname=ConfigConsts::DELTA_HOST,
                        $apiEndpoint='input_filters', $port=ConfigConsts::DELTA_PORT);
        }
    }
//    DeltaInputFilter::getVodClientInputFilter( "Cliente_teste_Api", "std", $create=true );
//    DeltaInputFilter::getVodClientInputFilter( "Cliente_teste_Api", "prm", $create=true );
//    DeltaInputFilter::newWebDavInputFilter( "Cliente_teste_Api", "channel01" )
?>
