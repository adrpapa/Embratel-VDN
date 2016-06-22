<?php

require_once "xmlrpc-3.0.0/lib/xmlrpc.inc";

class Wrapper {

    private $pba_xmlrpc;
    private $locale = "en";
    private $ok = 1;

    function __construct($pba_xmlrpc, $poa_xmlrpc = NULL) {
        //
        $tot = "";
        $ip = "";
        $port = "5224";

        if ($pba_xmlrpc) {
            $tot = explode(":", $pba_xmlrpc);
            switch (count($tot)) {
                case 0:
                    $ip = $pba_xmlrpc;
                    break;
                case 1:
                    $ip = $tot[0];
                    break;
                case 2:
                    $ip = $tot[0];
                    $port = $tot[1];
                    break;
                case 3:
                    $ip = $tot[1];
                    $port = $tot[2];
                    break;
                default:
                    break;
            }
            $this->pba_xmlrpc = new xmlrpc_client("/RPC2", $ip, $port);
            $this->pba_xmlrpc->request_charset_encoding = "UTF-8";
            
            // Test if the PBA IP is OK
            $res = $this->AccountDetailsGet_API(-1);
            if($res->errno > 0)
            {
                $logger = new SCITLogger\SCITLogger();

                $logger->info("BAD CONNECTION WITH PBA");
                $this->ok = 0; 
            }
        }
    }

    // This methods is for PBA help
    
    // Returns the account information, vendorid
    public function AccountDetailsGet_API($AccountID)
    {
        $params = array(new xmlrpcval($AccountID, 'i4'));
        $req = $this->createRequest("BM", "AccountDetailsGet_API", $params);
        $res = $this->pba_xmlrpc->send($req);
        return $res;
    }
    
    // Execute the request for each method
    private function createRequest($container, $method, $params = array())
    {
        $request = new xmlrpcmsg("Execute");
        $params = array("Object" => new xmlrpcval($container . "_Object", "string"),
            "Container" => new xmlrpcval($container . "_Container", "string"),
            "Lang" => new xmlrpcval($this->locale, "string"),
            "Method" => new xmlrpcval($method, "string"),
            "Params" => new xmlrpcval($params, "array"));
        $request->addParam(new xmlrpcval($params, "struct"));
        return $request;
    }


    // This method returns if PBA is OK
    public function getOk()
    {
        return $this->ok;
    }

}

?>