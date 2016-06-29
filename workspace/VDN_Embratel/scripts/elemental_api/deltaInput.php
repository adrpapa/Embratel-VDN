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
        ** cria objeto DeltaInputFilter para Live, e monta XML para inclusão do mesmo
        **
        */
        public static function newUdpInputFilter( $clientID, $label, $level ) {
            $xml=DeltaInputFilter::getElementalRest()->getTemplate(
                    ConfigConsts::DELTA_UDP_INPUT_FILTER_TEMPLATE, "DeltaUDPInputFilter");
            $axClientID = cleanClientID($clientID);
            $axLabel = cleanName($label);
            $xml->label = $axLabel;
            $xml->filter_settings->storage_location = 
                ConfigConsts::DELTA_LIVE_STORAGE_LOCATION.'/'.$axClientID.'/'.$axLabel.'/';
            // busca o output template para eventos live do cliente - cria se não existir
            $outputTemplate = DeltaOutputTemplate::getClientOutputTemplate( $axClientID, 'live', $level, true );
            $xml->filter_settings->template_id = $outputTemplate->id;
            $xml->filter_settings->udp_input->uri = 'udp://127.0.0.1:'
                    .DeltaInputFilter::getNextAvilableUdpPort();
            return new self(DeltaInputFilter::getElementalRest()->postRecord(null, null, $xml));
        }

        /*
        ** cria objeto DeltaInputFilter para VOD, e monta XML para inclusão do mesmo
        **
        */
        public static function newVodInputFilter( $clientID, $level ) {
            $xml=DeltaInputFilter::getElementalRest()->getTemplate(
                    ConfigConsts::DELTA_WF_INPUT_FILTER_TEMPLATE, "DeltaWFInputFilter");
            $axClientID = cleanClientID($clientID);
            $xml->label = $axClientID.'_vod_'.$level;
            $xml->filter_settings->incoming->uri = 
                ConfigConsts::DELTA_WF_INCOMMING_URI.'/'.$axClientID.'/'.$level.'/';
            // busca o output template para eventos VOD do cliente - cria se não existir
            $outputTemplate = DeltaOutputTemplate::getClientOutputTemplate( $axClientID, 'vod', $level, true );
            $xml->filter_settings->template_id = $outputTemplate->id;
            print '\n\n'.$xml->asXml().'\n\n';
            return new self(DeltaInputFilter::getElementalRest()->postRecord(null, null, $xml));
        }
        
        /*
        ** Obtem / cria input filter para o cliente std/premium ($level)
        ** Parametros:
        **      $clientID
        **      $level: std | premium
        */
        public static function getVodClientInputFilter( $clientID, $level, $create=true ) {
            $axClientID = cleanClientID( $clientID );
            $label = $axClientID.'_VOD_'.$level;
            foreach( DeltaInputFilter::getInputFilterList() as $xmlInpFilter ){
                if( $label === $xmlInpFilter->label ) {
                    return $xmlInpFilter;
                }
            }
            if( ! $create ) {
                return null;
            }
            return DeltaInputFilter::newVodInputFilter($clientID, $level);
        }
        
        
        public function setPropertiesFromXML( $xml ) {
            $this->label = (string)$xml->label."";
            $this->inputURI = (string)$xml->filter_settings->udp_input->uri;
            $this->href = (string)$xml['href'];
            $this->id = end(explode('/', $xml['href']));
            $this->udpPort = end(explode(':', $this->inputURI));
            $this->storage_location = (string)$xml->filter_settings->storage_location;
            $this->template_id = (string)$xml->filter_settings->template_id;
            $storageTokens = explode('/', trim($this->storage_location,'/'));
            $this->clientID = $storageTokens[count($this->clientID=$storageTokens)-2];
            print_r($this);
        }

        public static function delete($id) {
            DeltaInputFilter::getElementalRest()->restDelete($id);
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
 //    DeltaInputFilter::getVodClientInputFilter( "Cliente_teste_Api", "premium", $create=true );
?>
