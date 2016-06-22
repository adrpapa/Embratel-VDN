<?php

error_reporting(E_ALL);

require_once './mozyProAccount.php';
require_once 'aps/2/aps.php';
require_once "Utils/ApsUtilsDebug.php";

$handle = opendir('./config');

while (false !== ($file = readdir($handle))){

    $extension = strtolower(substr(strstr($file, '.'), 1));

    if($extension == 'pem'){
        $filename = basename($file,'.'.$extension);

        $apsc = \APS\Request::getController($filename);
        $resListPartial = json_decode( \APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources?implementing(http://www.mozy.com/mozyProAPS2/mozyProAccount/1.5)'),0);

        foreach($resListPartial as $resource){
            ApsUtilsDebug::Debug("--------------------------------Sincro ini-------------------------------------------------------");

            $res = json_decode( \APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/'.$resource->aps->id),0);

            if($res->aps->status!='aps:ready')continue;
            try{
                if($res->partnerId=="") continue;
            }
            catch(Exception $excp){continue;}

            ApsUtilsDebug::Debug("----Init Account---------");
            ApsUtilsDebug::Debug(print_r($res,true));
            $inifile=parse_ini_file('./'.$res->mozyPro->aps->id.'.ini',true);

            $paramsConn = array();
            $paramsConn['wsdl'] = $inifile['GLOBAL']['ws_prefix'].'resource'.$inifile['GLOBAL']['ws_sufix'];
            $paramsConn['methodName'] = "GetResources";

            $paramsData = array (
                'api_key'=>$inifile['GLOBAL']['api_key'],
                'partner_id'=>$res->partnerId,
                'user_group_id'=>null
            );

            $result ="";
            try{
            	$result= makeSoapCall($paramsConn , $paramsData);
            }
            catch(Exception $exsoap){
            	ApsUtilsDebug::Debug("ERROR querying Mozy with res id :". $res->partnerId);
            	ApsUtilsDebug::Debug(print_r($exsoap->getMessage(),true));
            	continue;
            }
            ApsUtilsDebug::Debug("----MOZY RESULT OF THE ACCOUNT---------");
            ApsUtilsDebug::Debug(print_r($result,true));

            $globalAtoInterfaz=array();

            foreach($result as $item){

                // print_r($item);
                if(!property_exists($item,"license_type"))continue;

                if((($item->license_type=="Desktop")|| ($item->license_type=="Server"))&&($item->licenses>0)){

                    $itemAtoInterfaz =  array (
                        'license_type'=>$item->license_type,
                        'licenses'=>$item->licenses,
                        'licenses_reserved'=>$item->licenses_reserved,
                        'licenses_used'=>$item->licenses_used,
                        'quota'=>$item->quota,
                        'quota_distributed'=>$item->quota_distributed,
                        'quota_used_bytes'=>$item->quota_used_bytes,
                        'license_type_order'=>$item->license_type,
                        'quota_order'=>$item->quota,
                        'number_order'=>$item->licenses
                    );
                    array_push($globalAtoInterfaz,$itemAtoInterfaz);
                }
            }

            try{

                $res->accountLicenses=json_encode($globalAtoInterfaz,1);
                $newObj = objectToObject($res,"mozyProAccount");
                \APS\Request::getController()->updateResource($newObj);

                ApsUtilsDebug::Debug("----Fin Account---------");

            }
            catch(Exception $ex){

                ApsUtilsDebug::Error(print_r($ex->getMessage(),true));
                ApsUtilsDebug::Error("----Fin Account ERROR---------");
            }
            //end of mozyProAccount update
            //
            ApsUtilsDebug::Debug("----Init Groupos---------");
            ApsUtilsDebug::Debug(print_r($res,true));

            $resGroupList = json_decode( \APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/'.$res->aps->id.'/mozyProAccountGroup'),0);

            foreach($resGroupList as $itemGroup){
                $paramsData = array (
                    'api_key'=>$inifile['GLOBAL']['api_key'],
                    'partner_id'=>null,
                    'user_group_id'=>$itemGroup->groupId
                );
                $resultSoapGroup="";
                try{
                	$resultSoapGroup= makeSoapCall($paramsConn , $paramsData);
                	
                	ApsUtilsDebug::Debug("----mozy Groupos---------");
                	ApsUtilsDebug::Debug(print_r($resultSoapGroup,true));
                }
                catch(Exception $exg){
                	ApsUtilsDebug::Debug("ERROR querying Mozy with res id :". $res->partnerId . "and group id: ". $itemGroup->groupId );
                	ApsUtilsDebug::Debug(print_r($exg->getMessage(),true));
                	continue;
                }
                
                foreach($resultSoapGroup as $itemSoapGroup){

                    if(($itemSoapGroup->license_type=="Desktop")){
                        $itemGroup->desktopQuotaOrdered= $itemSoapGroup->quota;
                        $itemGroup->desktopQuotaAssigned= $itemSoapGroup->quota_distributed;
                        $itemGroup->desktopKeysOrdered= $itemSoapGroup->licenses;
                        $itemGroup->desktopKeysAssigned= $itemSoapGroup->licenses_reserved +$itemSoapGroup->licenses_used;
                    }

                    if( ($itemSoapGroup->license_type=="Server")){
                        $itemGroup->serverQuotaOrdered= $itemSoapGroup->quota;
                        $itemGroup->serverQuotaAssigned= $itemSoapGroup->quota_distributed;
                        $itemGroup->serverKeysOrdered= $itemSoapGroup->licenses;
                        $itemGroup->serverKeysAssigned= $itemSoapGroup->licenses_reserved +$itemSoapGroup->licenses_used;
                    }
                }

                try{

                    $newObjGroup = objectToObject($itemGroup,"mozyProAccountGroup");
                    \APS\Request::getController()->updateResource($newObjGroup);


                }
                catch(Exception $ex){

                    ApsUtilsDebug::Error( print_r($ex->getMessage(),true));
                }

            }


        }
    }
}

ApsUtilsDebug::Debug("--------------------------------Sincro end-------------------------------------------------------");

function objectToObject($instance, $className) {
    return unserialize(sprintf(
        'O:%d:"%s"%s',
        strlen($className),
        $className,
        strstr(strstr(serialize($instance), '"'), ':')
    ));
}



?>
