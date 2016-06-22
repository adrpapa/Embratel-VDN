<?php
/* 
** Classe base para todas as chamadas de API Elemental
*/
   require_once "configConsts.php";

   class ElementalRest {

        public function __construct( $hostname, $apiEndpoint, $port=null, $protocol='http' ) {
            $credentials = "elemental:elemental";
            $this->uri = $protocol.'://'.$hostname;
            if( $port != null ){
                $this->uri .= ':'.$port;
            }
//             $this->uri = $baseURI.'/'.ConfigConsts::API_VERSION.'/api/'.'$apiEndpoint;
            $this->uri .= '/'.'/api/'.$apiEndpoint;
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        }
        
        public function __destruct() {
            curl_close($this->ch);
        }
        
        // Busca template para o evento 
        function getTemplate($templateID, $templateName) {
            $templateFilename = ConfigConsts::TEMPLATE_PATH.'/'.$templateName.'_'.$templateID.'.xml';
            if(! file_exists($templateFilename)) {
                print "Criando Template ".$templateFilename.PHP_EOL;
                $xml = $this->restGet( $templateID, 'clean=true');
                //$xml = $this->restGet( $templateID );
                if(! is_dir(ConfigConsts::TEMPLATE_PATH)) {
                	mkdir(ConfigConsts::TEMPLATE_PATH,0755, true);
                }
                $xml->asXml($templateFilename);
            }
            return simplexml_load_file($templateFilename);
        }

        function restGet( $id=null, $params=null){
            $this->headers = Array();
            return $this->restCall( $id, null, $params );
        }

        function postRecord($id, $command, $data=null){
            curl_setopt($this->ch, CURLOPT_POST, 1);
            //var_dump($data);
            if( $data ){
                if( $data instanceof SimpleXMLElement ){
                    $content = $data->asXml();
                } else {
                    $content = $data;
                }
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $content);
                $this->headers = Array("Content-Type: text/xml");
                $this->headers[] = "Content-Length: ".strlen($content);
            }
            
            return $this->restCall($id, $command);
        }
        
        function restDelete( $id ) {
            if( ! $id ) die ("DELETE operation must be called with ID\n");
            $this->headers = Array();
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            return $this->restCall( $id );
        }

        function restCall( $id=null, $command=null, $params=null)
        {
            $urlFinal=$this->uri;
            if( $id ) {
                $urlFinal .= '/'.$id;
            }
            if( $command ) {
                $urlFinal .= '/'.$command;
            }
            if( $params ) {
                $urlFinal .= '?'.$params;
            }
            curl_setopt($this->ch, CURLOPT_URL, $urlFinal);
            if( ConfigConsts::debug ) {
                curl_setopt($this->ch, CURLOPT_VERBOSE, true);
            }
            $this->headers[] = "Accept: application/xml";
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers );
            $data = curl_exec($this->ch);
            $httpRC = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            if (curl_errno($this->ch)) {
                print("\nERRO na execução do CURL\n");
                die( "Error: " . curl_error($this->ch));
            }
            if ( ! in_array ($httpRC, array(100, 200, 201, 202)) ) {
                $errMsg = sprintf( "HTTP Error: %s\nData: %s", $httpRC, $data);
                try {
                    $xml=simplexml_load_string($data);
                    $errMsg = sprintf( "Erro na chamada da API: %s\n", $xml->error);
                } catch(Exception $ex) {
                }
                throw new invalidargumentexception( $errMsg );
            }
            $xml=null;
            try {
                if( trim($data."") ) {
                    $xml = simplexml_load_string($data);
                }
            } catch(Exception $ex) {
                throw new Exception("Error: Cannot create simplexml object from data returned".PHP_EOL.$data);
            }
            return $xml;
        }
    }
?>
    
