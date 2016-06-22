<?php
class PBAAccountServiceManager {

    private static function _SoapCall($apiUrl,$soapRequest)
    {
        $header = array(
            "content-type: application/soap+xml",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: \"run\"",
            "Content-length: ".strlen($soapRequest));

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL,             $apiUrl);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT,  30);
        curl_setopt($handle, CURLOPT_TIMEOUT,         60);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER,  true );
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER,  false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST,  false);
        curl_setopt($handle, CURLOPT_POST,            true );
        curl_setopt($handle, CURLOPT_POSTFIELDS,      $soapRequest);
        curl_setopt($handle, CURLOPT_HTTPHEADER,      $header);
        curl_setopt($handle, CURLINFO_HEADER_OUT,     true);
        curl_setopt($handle, CURLOPT_VERBOSE,   1);

        $response 		= curl_exec($handle);

        $returnCode 	= curl_getinfo($handle, CURLINFO_HTTP_CODE);

        curl_close($handle);

        return array('code' => $returnCode, 'content' => $response);
    }

    public static function AccountDetailsGet($apiUrl, $accountID, $pbaApiUserName = '', $pbaApiPassword = '')
    {
        $return_data_params = array('AccountID','VendorAccountID','CompanyName','Address1','Address','City','State','Zip','CountryID','PostalAddress','accFName','accMName','accLName','accEmail','accPhCountryCode','accPhAreaCode','accPhNumber','accPhExtention','accFaxCountryCode','accFaxAreaCode','accFaxNumber','accFaxExtention','CreationDate','TaxStatus','AStatus','FullyRegistred');
        $return_data_params_result = array('AccountID','VendorAccountID','CompanyName','Address1','City','State','Zip','CountryID','accFName','accLName','accEmail','accPhCountryCode','accPhAreaCode','accPhNumber');

        try{
            $methodName = 'AccountDetailsGet_API';
            $securityMembers = '';
            if (!empty($pbaApiUserName) && !empty($pbaApiPassword)) {
                $securityMembers = '<member><name>Username</name><value>'.$pbaApiUserName.'</value></member><member><name>Password</name><value>'.$pbaApiPassword.'</value></member>';
            }

            $soapRequest = '<methodCall><methodName>Execute</methodName><params><param><value><struct><member>
            <name>Server</name><value>BM</value></member><member><name>Method</name><value>'.$methodName.'</value></member>'.$securityMembers.'
            <member><name>Params</name><value><array><data>
			<value><i4>'.$accountID.'</i4></value>
			</data></array></value></member></struct></value></param></params></methodCall>';

            $response = self::_SoapCall($apiUrl, $soapRequest);

            if ($response['code'] == 200) {

                $xmlResult = simplexml_load_string($response['content']);
                if ($xmlResult->fault->getName() != "fault") {
                    $nodeList = $xmlResult->xpath('//data/value/array/data/value');

                    $return_data = array();
                    foreach($return_data_params as $param_key => $return_data_param){
                        foreach($nodeList[$param_key] as $nodeValue)
                        {
                            $return_data[$return_data_param] = $nodeValue->__toString();
                        }
                    }
                    $result_data = array();
                    foreach($return_data as $return_data_key => $return_data_value)
                    {
                        if(in_array($return_data_key, $return_data_params_result)){
                            $result_data[$return_data_key] = $return_data_value;
                        }
                    }
                    return $result_data;
                }
            }
        }
        catch(Exception $ex){
        }
        return null;
    }
    

}
?>