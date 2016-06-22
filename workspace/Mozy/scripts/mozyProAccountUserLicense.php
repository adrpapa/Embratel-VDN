<?php
require_once "framework.php";
require_once "aps/2/runtime.php";
require_once "mozyProAccount.php";
require_once "mozyProAccountUser.php";

/**
 * Class mozyProAccountUserLicense
 * @type("http://www.mozy.com/mozyProAPS2/mozyProAccountUserLicense/1.0")
 * @implements("http://aps-standard.org/types/core/resource/1.0")
 */
class mozyProAccountUserLicense extends \APS\ResourceBase {

    /**
     * @link("http://www.mozy.com/mozyProAPS2/mozyPro/1.1")
     * @required
     */
    public $mozyPro;

    /**
     * @link("http://www.mozy.com/mozyProAPS2/mozyProAccountUser/1.1")
     *  @required
     */
    public $mozyProAccountUser;

    /**
     * @type(string)
     * @title("Storage Quota in GB")
     *
     */
    public $quota;

    /**
     * @type(string)
     * @title("License type")
     */
    public $licenseType;

    /**
     * @type(string)
     * @title("licenseNum")
     *
     */
    public $licenseNum;

    /**
     * @type(string)
     * @title("user_group_id")
     */
    public $user_group_id;

    /**
     * @type(string)
     * @title("KeyString")
     */
    public $keyString;

    /**
     * @type(string)
     * @title("MachineId")
     */
    public $machineId;

    /**
     * @type(string)
     * @title("alias")
     */
    public $alias;

    /**
     * @type(string)
     * @title("quota_used_bytes")
     */
    public $quota_used_bytes;

    /**
     * @type(string)
     * @title("quotaMachine")
     */
    public $quotaMachine;

    /**
     * @type(string)
     * @title("last_backup_at")
     */
    public $last_backup_at;
    private $paramsConn;
    private $paramsData;
    private $cred;


    public function provision() {
        $this->logger = new SCITLogger\SCITLogger($this->mozyProAccountUser->mozyProAccount->account->id);
        $this->logger->info("MozyProAccountUserLicense provision starting");
        $this->getMozyConfiguration();
        $resUser = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->mozyProAccountUser->link->id), 0);

        $resAccount = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $resUser->mozyProAccount->aps->id), 0);

        $this->provisionWithoutResUpdate($resUser, $resAccount);
        //update resources

        try {
            if ($this->licenseType == "Desktop") {
                $resAccount->desktopQuota->usage+=$this->quota;
                $resAccount->desktopLicenseNum->usage+=1;
                $resUser->desktopQuotaSum +=$this->quota;
                $resUser->desktopLicSum+=1;
            } else {

                $resAccount->serverQuota->usage+=$this->quota;
                $resAccount->serverLicenseNum->usage+=1;
                $resUser->serverQuotaSum+=$this->quota;
                $resUser->serverLicSum+=1;
            }

            $newresAccount = $this->objectToObject($resAccount, "mozyProAccount");
            $newresUser = $this->objectToObject($resUser, "mozyProAccountUser");
            \APS\Request::getController()->updateResource($newresAccount);
            \APS\Request::getController()->updateResource($newresUser);
            $this->logger->info("Resources mozyproaccount and mozyproaccountuser well updated in poa after license provisioning");
        } catch (Exception $exc) {
            $this->logger->info("ERROR while updating resources after creating licenses, :\n\t" . $exc->getMessage());
        }
    }

    public function configure($new) {
        $this->logger = new SCITLogger\SCITLogger($this->mozyProAccountUser->mozyProAccount->account->id);
        $this->logger->info("MozyProAccountUserLicense Configure starting");

        if ($new->quota == $this->quota) {
            return;
        }
        $this->getMozyConfiguration();
        $resUser = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->mozyProAccountUser->link->id), 0);

        $resAccount = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $resUser->mozyProAccount->aps->id), 0);

        if ($this->machineId != "") {
            $this->paramsConn = array();
            $this->paramsConn['wsdl'] = $this->cred->ws_prefix . 'machine' . $this->cred->ws_sufix;
            $this->paramsConn['methodName'] = "Update";

            $this->paramsData = array(
                'api_key' => $this->cred->api_key,
                'id' => $new->machineId,
                'search' => null,
                'quota' => $new->quota,
                'status' => null,
                'source_machine_id' => null,
                'external_id' => null,
                'details' => null
            );
            $result = makeSoapCall($this->paramsConn, $this->paramsData);
            $this->logger->info("update quota of machines, :\n\t" . print_r($result, true));
        } else {
            if ($this->machineId == "") {
                $wsdlUrl = $this->cred->ws_prefix . 'machine' . $this->cred->ws_sufix;
                $wsdlUrlRes = $this->cred->ws_prefix . 'resource' . $this->cred->ws_sufix;
                $urlKey = $this->cred->api_key;

                $this->checkIfMachineIdAlreadyAssigned($wsdlUrl, $wsdlUrlRes, $urlKey, $this->keyString, $new->quota);

                $this->paramsConn = array();
                $this->paramsConn['wsdl'] = $this->cred->ws_prefix . 'resource' . $this->cred->ws_sufix;
                $this->paramsConn['methodName'] = "UpdateLicenses";

                $this->paramsData = array(
                    'api_key' => $this->cred->api_key,
                    'keystring' => $this->keyString,
                    'search' => null,
                    'assigned_email_address' => $resUser->login,
                    'quota_desired' => $new->quota,
                    'external_id' => null,
                    'license_type' => $new->licenseType,
                    'deliver_emails' => false,
                    'expires_at' => null,
                    'clear_expires_at' => null,
                    'user_group_id' => $new->user_group_id
                );
                $result = makeSoapCall($this->paramsConn, $this->paramsData);
                $this->logger->info("Updated quota of licenses in Mozy, result:\n\t" . print_r($result, true));
            }
        }

        try {

            if ($new->quota > $this->quota) {
                $quotaRealValue = $new->quota - $this->quota;
                $this->logger->debug("New quota - old quota = " . print_r($quotaRealValue, true));
                if ($new->licenseType == "Desktop") {
                    $resAccount->desktopQuota->usage+=$quotaRealValue;
                    $resUser->desktopQuotaSum +=$quotaRealValue;
                } else {
                    $resAccount->serverQuota->usage+=$quotaRealValue;
                    $resUser->serverQuotaSum+=$quotaRealValue;
                }
            } else {
                $quotaRealValue = $this->quota - $new->quota;
                if ($new->licenseType == "Desktop") {
                    $resAccount->desktopQuota->usage-=$quotaRealValue;
                    $resUser->desktopQuotaSum -=$quotaRealValue;
                } else {
                    $resAccount->serverQuota->usage-=$quotaRealValue;
                    $resUser->serverQuotaSum-=$quotaRealValue;
                }
            }
            $new->machineId = $this->machineId;
            $this->logger->debug("quotaValue in use: " . print_r($resAccount->serverQuota->usage, true));
            $newresAccount = $this->objectToObject($resAccount, "mozyProAccount");
            $newresUser = $this->objectToObject($resUser, "mozyProAccountUser");
            \APS\Request::getController()->updateResource($newresAccount);
            \APS\Request::getController()->updateResource($newresUser);
            $this->quota = $new->quota;

            $this->logger->info("resource in poa after lic provisioning well updated");
        } catch (Exception $exc) {
            $this->logger->info("ERROR while updating resources after creating licenses, :\n\t" . $exc->getMessage());
        }
    }

    function checkIfMachineIdAlreadyAssigned($wsdlUrl, $wsdlUrlRes, $apiKey, $keystring, $quota) {
        $this->paramsLicConn = array();
        $this->paramsLicConn['wsdl'] = $wsdlUrlRes;
        $this->paramsLicConn['methodName'] = "GetLicenses";

        $this->paramsLicData = array(
            'api_key' => $apiKey,
            'keystring' => $keystring,
            'search' => null
        );

        $resultLic = makeSoapCall($this->paramsLicConn, $this->paramsLicData);
        $this->logger->info("GetLicenses, result:\n\t" . print_r($resultLic, true));
        $newItemLic = $resultLic->results[0];
        if (isset($newItemLic->machine_id)) {
            $this->machineId = $newItemLic->machine_id;

            $this->paramsConn = array();
            $this->paramsConn['wsdl'] = $wsdlUrl;
            $this->paramsConn['methodName'] = "Update";

            $this->paramsData = array(
                'api_key' => $apiKey,
                'id' => $this->machineId,
                'search' => null,
                'quota' => $quota,
                'status' => null,
                'source_machine_id' => null,
                'external_id' => null,
                'details' => null
            );
            $result = makeSoapCall($this->paramsConn, $this->paramsData);
            $this->logger->info("Update quota of machines,result:\n\t" . print_r($result, true));
        }
    }

    public function unprovision() {


        $this->logger = new SCITLogger\SCITLogger($this->mozyProAccountUser->mozyProAccount->account->id);

        $this->logger->info("Unprovision AccountUserLicense starting ");

        $this->getMozyConfiguration();
        $resUser = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->mozyProAccountUser->link->id), 0);
        $resAccount = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $resUser->mozyProAccount->aps->id), 0);

        $this->unprovisionWithoutResUpdate($resUser, $resAccount);

        try {
            if ($this->licenseType == "Desktop") {
                $resAccount->desktopQuota->usage-=$this->quota;
                $resAccount->desktopLicenseNum->usage-=1;
                $resUser->desktopQuotaSum -=$this->quota;
                $resUser->desktopLicSum-=1;
            } else {

                $resAccount->serverQuota->usage-=$this->quota;
                $resAccount->serverLicenseNum->usage-=1;
                $resUser->serverQuotaSum-=$this->quota;
                $resUser->serverLicSum-=1;
            }
            $newresAccount = $this->objectToObject($resAccount, "mozyProAccount");
            $newresUser = $this->objectToObject($resUser, "mozyProAccountUser");
            \APS\Request::getController()->updateResource($newresAccount);
            \APS\Request::getController()->updateResource($newresUser);
            $this->logger->info("Unprovision AccountUserLicense finished");

        } catch (Exception $exc) {
            $this->logger->info("resource in poa after lic provisioning error, :\n\t" . $exc->getMessage());
        }
    }

    function provisionWithoutResUpdate($resUser, $resAccount) {
        $this->logger->info("Provision function has been called to create license");

        if ($this->user_group_id == null) {
            $this->user_group_id = $this->mozyProAccountUser->user_group_id;
        }
        $this->fillDataForGetFreeLicenses($this->user_group_id);

        $keystring = "";
        try {
            $keystring = makeSoapCall($this->paramsConn, $this->paramsData);
            $this->logger->info("Getlicense response:" . print_r($keystring, true));
        } catch (Exception $fault) {
            $this->logger->info('GetLicense exception: ' . print_r($fault, true));
            throw new Exception($fault->getMessage());
        }

        $this->logger->info("License retrieved with following Keystring:\n\t" . $keystring->results[0]->keystring);
        $this->keyString = $keystring->results[0]->keystring;
        $this->fillDataForUserResourcesCreation($keystring->results[0]->keystring, $resUser->login);
        try {
            $result = makeSoapCall($this->paramsConn, $this->paramsData);
            $this->logger->info("Provision license to the proper user in Mozy:" . print_r($result, true));
        } catch (Exception $fault) {
            $this->logger->info("Provision for mozyProAccountuser when creating partner error, :\n\t" . $fault->getMessage());
            throw new Exception($fault->getMessage());
        }
    }

    function unprovisionWithoutResUpdate($resUser, $resAccount) {

        $this->machineId = $this->getIfMachine();
        if ($this->machineId != null && $this->machineId != "") {
            $this->removeMachine();
        } else {
            $this->fillDataForRemoveLicenses();
            try {
                $result = makeSoapCall($this->paramsConn, $this->paramsData);
                $this->logger->info("Licenses unprovisioned in Mozy: " . print_r($this->paramsData, true));
            } catch (Exception $fault) {
                $this->logger->info("error unProvision function  license :\n\t" . $fault->getMessage());
            }
        }
    }

    function fillDataForRemoveLicenses() {
        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->cred->ws_prefix . 'resource' . $this->cred->ws_sufix;
        $this->paramsConn['methodName'] = "UpdateLicenses";

        $this->paramsData = array(
            'api_key' => $this->cred->api_key,
            'keystring' => $this->keyString,
            'search' => null,
            'assigned_email_address' => '',
            'quota_desired' => null,
            'external_id' => null,
            'license_type' => null,
            'deliver_emails' => true,
            'expires_at' => null,
            'clear_expires_at' => null,
            'user_group_id' => null
        );
    }

    function fillDataForGetFreeLicenses($groupId) {
        //$this->logger->info(" function group" . print_r($this, true));
        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->cred->ws_prefix . 'resource' . $this->cred->ws_sufix;
        $this->paramsConn['methodName'] = "GetLicenses";
        $this->paramsData = array(
            'api_key' => $this->cred->api_key,
            'keystring' => null,
            'search' => array(
                'status' => 'free',
                'license_type' => $this->licenseType,
                'user_group_id' => $groupId,
                'no_sub_partner' => null
            )
        );
    }

    function fillDataForUserResourcesCreation($keystring, $assignlogin) {

        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->cred->ws_prefix . 'resource' . $this->cred->ws_sufix;
        $this->paramsConn['methodName'] = "UpdateLicenses";

        $this->paramsData = array(
            'api_key' => $this->cred->api_key,
            'keystring' => $keystring,
            'search' => null,
            'assigned_email_address' => $assignlogin,
            'quota_desired' => $this->quota,
            'external_id' => $this->mozyProAccountUser->mozyProAccount->subscription->subscriptionId,
            'license_type' => null,
            'deliver_emails' => true,
            'expires_at' => null,
            'clear_expires_at' => null,
            'user_group_id' => null
        );
    }

    private function getMozyConfiguration() {
        $this->cred = new stdClass();
        $this->cred->ws_prefix = $this->mozyProAccountUser->mozyProAccount->mozyPro->ws_prefix;
        $this->cred->ws_sufix = $this->mozyProAccountUser->mozyProAccount->mozyPro->ws_sufix;

        if ($this->mozyProAccountUser->mozyProAccount->mozyPro->api_key == null) {
            $this->getMozyConf();
        } else {
            $this->cred->api_key = $this->mozyProAccountUser->mozyProAccount->mozyPro->api_key;
            $this->cred->root_partner_id = $this->mozyProAccountUser->mozyProAccount->mozyPro->root_partner_id;
            $this->cred->root_role_id = $this->mozyProAccountUser->mozyProAccount->mozyPro->root_role_id;
        }
    }

    private function getMozyConf() {
        $apsc = \APS\Request::getController();
        $apsId = $this->mozyProAccountUser->mozyProAccount->brandingId;
        $configuration = $apsId != null ? $apsc->getResource($apsId) : $this->mozyProAccountUser->mozyProAccount->mozyPro;
        $this->cred->api_key = $configuration->api_key;
        $this->cred->root_partner_id = $configuration->root_partner_id;
        $this->cred->root_role_id = $configuration->root_role_id;
    }

    private function getIfMachine() {
        $paramsConn['wsdl'] = $this->cred->ws_prefix . 'resource' . $this->cred->ws_sufix;

        $paramsConn['methodName'] = "GetLicenses";

        $paramsData = array(
            'api_key' => $this->cred->api_key,
            'keystring' => $this->keyString,
            'search' => null
        );
        try {
            $res = makeSoapCall($paramsConn, $paramsData);
        } catch (Exception $ex) {
            $this->logger->info("Error searching the license ");
            throw new Exception("Error searching the license");
        }
        return $res->results[0]->machine_id;
    }

    private function removeMachine() {
        $paramsConn['wsdl'] = $this->cred->ws_prefix . 'machine' . $this->cred->ws_sufix;

        $paramsConn['methodName'] = "Delete";

        $paramsData = array(
            'api_key' => $this->cred->api_key,
            'id' => $this->machineId
        );
        try {
            $res = makeSoapCall($paramsConn, $paramsData);
        } catch (Exception $ex) {
            $this->logger->info("Error deleting the machine ");
            throw new Exception("Error deleting the machine");
        }
    }

    function objectToObject($instance, $className) {
        return unserialize(sprintf(
            'O:%d:"%s"%s', strlen($className), $className, strstr(strstr(serialize($instance), '"'), ':')
        ));
    }

}

?>
