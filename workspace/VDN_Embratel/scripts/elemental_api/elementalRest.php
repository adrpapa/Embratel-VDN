<?php
/* 
** Classe base para todas as chamadas de API Elemental
*/
require_once "configConsts.php";
require_once "auth.php";

class ElementalRest {

    //Define authentication object. If null, no authentication is used
    public static $auth;

    public function __construct( $hostname, $apiEndpoint, $port=null, $protocol='http' ) {
        $credentials = "elemental:elemental";
        $this->uri = $protocol.'://'.$hostname;
        if( $port != null ){
            $this->uri .= ':'.$port;
        }
//             $this->uri = $baseURI.'/'.ConfigConsts::API_VERSION.'/api/'.'$apiEndpoint;
        $this->uri .= '/api/'.$apiEndpoint;
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        if( ConfigConsts::debug ) {
            curl_setopt($this->ch, CURLOPT_VERBOSE, true);
        }
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

    function restCDSM($credentials,$data=null) {
        if( $data ){
            $this->curl_custom_postfields($data);
        }			
        else {
            $this->headers = array(
                    "Content-type: text/xml;charset=\"utf-8\"",
                    "Accept: text/xml",
                    "Cache-Control: no-cache",
                    "Pragma: no-cache"
            );				
        }
        
        $this->headers[] = "Authorization: Basic " . $credentials;
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        
        return $this->restCall();
    }
    function curl_custom_postfields($data) {
        // invalid characters for "name" and "filename"
        static $disallow = array("\0", "\"", "\r", "\n");

        // build file parameters
        $k = "0";
        $v = ConfigConsts::TEMPLATE_PATH . "/rule-url-rwr.xml";

        $v = call_user_func("end", explode(DIRECTORY_SEPARATOR, $v));
        $k = str_replace($disallow, "_", $k);
        $v = str_replace($disallow, "_", $v);
        $body[] = implode("\r\n", array(
                "Content-Disposition: form-data; name=\"{$k}\"; filename=\"{$v}\"",
                "Content-Type: application/octet-stream",
                "",
                $data,
        ));
    
        // generate safe boundary
        do {
            $boundary = "---------------------" . md5(mt_rand() . microtime());
        } while (preg_grep("/{$boundary}/", $body));
    
        // add boundary for each parameters
        array_walk($body, function (&$part) use ($boundary) {
            $part = "--{$boundary}\r\n{$part}";
        });
    
        // add final boundary
        $body[] = "--{$boundary}--";
        $body[] = "";
        // set options
        
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, implode("\r\n", $body));
        $this->headers[] = "Expect: 100-continue";
        $this->headers[] = "Content-Type: multipart/form-data; boundary={$boundary}";
    }
    
    function restGet( $id=null, $params=null, $command=null){
        $this->headers = Array();
        return $this->restCall( $id, $command, $params );
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
        else {
            $this->headers = Array();
        }
        
        return $this->restCall($id, $command);
    }
    
    function putRecord($id, $command=null, $data=null){
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PUT");
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
        else {
            $this->headers = Array();
        }
        
        return $this->restCall($id, $command);
    }
    
    function restDelete( $id, $command=null ) {
        if( ! $id ) die ("DELETE operation must be called with ID\n");
        $this->headers = Array();
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        return $this->restCall( $id, $command );
    }

    function restCall( $id=null, $command=null, $params=null){
        echo "Calling restCall with id=$id, command=$command, params=$params";
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
        $this->headers[] = "Accept: application/xml";
        
        // Define Authentication
        if ( !is_null(ElementalRest::$auth) ) {
            if (! ElementalRest::$auth instanceof Auth ) { 
                throw new Exception('Error:restCall() Wrong auth type.Should be [Auth]'); 
            }
            ElementalRest::$auth->createAuthKey( chop(substr($urlFinal, strpos($urlFinal,'/api/')+4),'?'.$params) );
            $this->headers[] = "X-Auth-User: " . ElementalRest::$auth->getLogin();
            $this->headers[] = "X-Auth-Expires: " . ElementalRest::$auth->getExpires();
            $this->headers[] = "X-Auth-Key: " . ElementalRest::$auth->getAuthKey();
        }

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers );
        
        $data = curl_exec($this->ch);
        if( ConfigConsts::debug ) {
            echo "*********************\nExecutando CURL\n";
            echo "URL to call: $urlFinal\n";
            echo "\n********************\n";
        }
        $httpRC = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        if (curl_errno($this->ch)) {
            print("\nERRO na execução do CURL\n");
            die( "Error: " . curl_error($this->ch));
        }
        if ( ! in_array ($httpRC, array(100, 200, 201, 202)) ) {
            $errMsg = sprintf( "HTTP Error: %s\nData: %s", $httpRC, $data);
            try {
                $xml=simplexml_load_string($data);
                $errMsg = "Erro na chamada da API:\n";
                foreach( $xml->error as $err ){
                    $errMsg .= $err."\n";
                }
            } catch(Exception $ex) {
            }
            throw new invalidargumentexception( $errMsg );
        }
        $xml=null;
        try {
            if( trim($data."") ) {
                $xml = simplexml_load_string( utf8_encode($data) );
            }
        } catch(Exception $ex) {
            throw new Exception("Error: Cannot create simplexml object from data returned".PHP_EOL.$data);
        }
        return $xml;
    }
}
?>
    
