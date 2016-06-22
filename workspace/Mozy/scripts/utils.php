<?php

function makeSoapCall($requiredparms, $parms) {
    $client = new ExtendSoapClient($requiredparms['wsdl'], array('exceptions' => 0, 'trace' => true));

    $method = $requiredparms['methodName'];

    $result = treatSoapFault($client->__soapCall($method, $parms));


    return $result;
}

function treatSoapFault($result) {

    if (is_object($result)) {
        if (get_class($result) == "SoapFault") {

            throw new Exception($result->faultstring);
        }
    }

    return $result;
}

require_once "framework.php";

class ExtendSoapClient extends SoapClient {

    public $sendRequest = true;
    public $printRequest = false;
    public $formatXML = false;

    public function __doRequest($request, $location, $action, $version, $one_way = 0) {
        
        if ($action = '/user/api/Create') {
            if (!$this->formatXML) {
                $out = $request;
            } else {
                $doc = new DOMDocument;
                $doc->preserveWhiteSpace = false;
                $doc->loadxml($request);
                $doc->formatOutput = true;
                $out = $doc->savexml();
            }
            // echo $out;
            $out = preg_replace('/<password xsi:type="xsd:string"><\/password>/', "", $out);
            $request = $out;
        }

        if ($this->sendRequest) {

            $logger = new SCITLogger\SCITLogger();
            $logger->info("Request: ".print_r($request,1));
            return parent::__doRequest($request, $location, $action, $version, $one_way);
        } else {
            return '';
        }
    }
}

?>