<?php
define('APS_DEVELOPMENT_MODE', true);
require_once "framework.php";
require_once "aps/2/runtime.php";
require_once "utils.php";
require_once "mozyProAccountGroup.php";
require_once "Utils/Wrapper.php";

/**
 * Class mozyProAccount
 * @type("http://www.mozy.com/mozyProAPS2/mozyProAccount/1.5")
 * @implements("http://aps-standard.org/types/core/resource/1.0")
 * @implements("http://aps-standard.org/types/core/suspendable/1.0")
 */
class mozyProAccount extends \APS\ResourceBase {

    /**
     * @link("http://www.mozy.com/mozyProAPS2/mozyPro/1.1")
     * @required
     */
    public $mozyPro;

    /**
     * @link("http://www.mozy.com/mozyProAPS2/mozyProConf/1.1[]")
     */
    public $mozyProConf;

    /**
     * @link("http://aps-standard.org/types/core/subscription/1.0")
     * @required
     */
    public $subscription;

    /**
     * @link("http://aps-standard.org/types/core/account/1.0")
     * @required
     */
    public $account;

    /**
     * @link("http://aps-standard.org/types/core/service-user/1.0[]")
     */
    public $serviceUsers;

    /**
     * @link("http://www.mozy.com/mozyProAPS2/mozyProAccountUser/1.1[]")
     */
    public $mozyProAccountUser;

    /**
     * @link("http://www.mozy.com/mozyProAPS2/mozyProAccountGroup/1.0[]")
     */
    public $mozyProAccountGroup;

    /**
     * @type(string)
     * @title("partnerId")
     * @readonly
     */
    public $partnerId;

    /**
     * @type(string)
     * @title("password")
     * @readonly
     * @encrypted
     */
    public $password;

    /**
     * @type(string)
     * @title("userName")
     */
    public $userName;

    /**
     * @type(string)
     * @title("userFullName")
     */
    public $userFullName;

    /**
     * @type(string)
     * @title("companyName")
     */
    public $companyName;

    /**
     * @type(string)
     * @title("user_group_id")
     * @readonly
     */
    public $user_group_id;

    /**
     * @type(boolean)
     * @title("groupview")
     */
    public $groupview;

    /**
     * @type(boolean)
     * @title("groupview")
     */
    public $groupviewOLD;

    /**
     * @type(string)
     * @title("accountLicenses")
     * @readonly
     */
    public $accountLicenses;

    /**
     * @type("http://aps-standard.org/types/core/resource/1.0#Counter")
     * @title("Server Storage Quota in GB")
     * @unit("unit")
     */
    public $serverQuota;

    /**
     * @type("http://aps-standard.org/types/core/resource/1.0#Counter")
     * @title("Server license number")
     * @unit("unit")
     */
    public $serverLicenseNum;

    /**
     * @type("http://aps-standard.org/types/core/resource/1.0#Counter")
     * @title("Sync or not")
     * @unit("unit")
     */
    public $syncAvailable;

    /**
     * @type("http://aps-standard.org/types/core/resource/1.0#Counter")
     * @title("Sync in GB")
     * @unit("unit")
     */
    public $syncDefaultQuota;

    /**
     * @type("http://aps-standard.org/types/core/resource/1.0#Counter")
     * @title("Desktop Storage Quota in GB")
     * @unit("unit")
     */
    public $desktopQuota;

    /**
     * @type(string)
     * @title("resources")
     * @description("resources")
     */
    public $resources;

    /**
     * @type(string)
     * @title("accountType")
     * @description("accountType")
     */
    public $accountType = 0;

    /**
     * @type("http://aps-standard.org/types/core/resource/1.0#Counter")
     * @title("Desktop license number")
     * @unit("unit")
     */
    public $desktopLicenseNum;

    /**
     * @type(string)
     * @title("partnerStatus")
     * @description("partnerStatus")
     */
    public $partnerStatus;

    /**
     * @type(integer)
     * @title("resellerSync")
     * @description("Stores if the reseller has sync enabled to be shown in UI button")
     */
    public $resellerSync;

    /**
     * @type(string)
     * @readonly(true)
     * @access(referrer,false)
     * @title("brandingId")
     * @description("Stores branding resource id")
     */
    public $brandingId;

    private $paramsConn;
    private $paramsData;
    private $vendorId;
    private $option;
    private $vendorData;
    private $cred;
    // Array of wsdl method names for configure. for fixing release and provision of resources at the same time.
    private $arrWsdlMethodName = array();
    protected $logger;
    #############################################################################################################################################
    ## Below function returns the "Defaults" of our settings, same as in APS 1.x using the $this->option "default-value"
    #############################################################################################################################################

    public function _getDefault() {

        return;
    }


    #############################################################################################################################################
    ## Definition of the functions that will respond to the different CRUD operations
    #############################################################################################################################################

    public function provision() {
        $this->logger = new SCITLogger\SCITLogger($this->account->id);
        $this->logger->info("mozyProAccount Provision Starting #");

        // Local vars
        $this->groupview = true;
        $this->groupviewOLD = true;
        $this->option = 1;
        $parentTenant = "";
        $partnerIdParent = "";
        $this->vendorData = new stdClass();
        $this->vendorData->VendorAccountID = -1;
        $this->vendorId = 1;

        // OrderType = false -> Direct Model ---- OrderType = true -> Reseller Model
        // Option = 1 Direct Model --- 2 = Reseller Model --- 3 = Reseller IG
        // AccountType = C -> Direct Customer ---- R -> Reseller ----- CR -> Customer reseller
        // Check the vendor property, if this vendor has vendor, it's a customer in PA, else, Reseller
        if ($this->mozyPro->orderType == false && $this->mozyPro->api_key == null) {
            throw new Exception("DIRECT MODEL MUST HAS GLOBALS CREDENTIALS AND BRANDING NOT");
        }
        if ($this->mozyPro->orderType == false && $this->mozyPro->api_key != null) {
            $this->option = 1;
            $this->accountType = "C";
        } else {
            $this->groupview = false;
            $this->groupviewOLD = $this->groupview;
            if ($this->mozyPro->orderType == true && $this->mozyPro->api_key != null) {
                $this->option = 2;
            } else {
                if ($this->mozyPro->orderType == true && $this->mozyPro->api_key == null) {
                    $this->option = 3;
                    $resources = $this->subscription->resources();
                    foreach ($resources as $item) {
                        if ($item->apsType == "http://www.mozy.com/mozyProAPS2/mozyProConf/1.1" && $item->limit == 1) {
                            $this->brandingId = $item->apsId;
                            break;
                        }
                    }
                } else {
                    throw new Exception("GENERAL ERROR IN CONFIGURATION");
                }
            }
        }
        $this->getMozyConfiguration();

        if ($this->option != 1) {
            // Using wrapper
            $customer = $this->account->id;
            if($this->mozyPro->APIPBA == NULL || $this->mozyPro->APIPBA == '')
            {
                throw new Exception("API PBA IN GLOBAL SETTINGS MUST BE FILLED");
            }
            $wrapper = new Wrapper($this->mozyPro->APIPBA);
            $this->vendorData = $wrapper->AccountDetailsGet_API($customer);
            $this->vendorId = $this->vendorData->val->me["struct"]["Result"]->me["array"][0]->me["array"][1]->me["i4"];
            $this->logger->info("VendorId #".print_r($this->vendorId,true));

            // The ID of the second level (vendor-vendor)
            $preVendorId = $wrapper->AccountDetailsGet_API($this->vendorId);
            $this->vendorData->VendorAccountID = $preVendorId->val->me["struct"]["Result"]->me["array"][0]->me["array"][1]->me["i4"];
            $this->logger->info("VendorId Second Level #".print_r($this->vendorData->VendorAccountID,true));
        }

        // Check if the customer is reseller
        $this->getAccountType();
        if ($this->accountType == "R" && $this->mozyPro->orderType == false) {
            throw new Exception("RESELLER CANNOT ORDER IN DIRECT MODEL");
        }
        if ($this->vendorData->VendorAccountID < 0) {
            if ($this->mozyPro->orderType == true && $this->accountType != "R") {
                throw new Exception("CUSTOMER CANNOT ORDER IN RESELLER MODE");
            }
        } else {
            if ($this->accountType != "R") {
                $parentTenantPrev = \APS\Request::getController()->getIo()->sendRequest("GET", "/aps/2/resources/?implementing(http://www.mozy.com/mozyProAPS2/mozyProAccount/1.5),eq(account.id," . $this->vendorId . ")");

                // The parentTenant will be used to create the user group
                $parentTenant = json_decode($parentTenantPrev);
                if (isset($parentTenant[0]->partnerId)) {
                    $partnerIdParent = $parentTenant[0]->partnerId;
                    $this->partnerStatus = $parentTenant[0]->partnerStatus;
                } else {
                    throw new Exception("CUSTOMER CANNOT ORDER MOZY BEFORE THE RESELLER");
                }
            }
        }
        // We need check if email address already exist before creating the partner, if exists we will provision the subscription and when the customer choses a new email, it will be provisioned in Mozy
        //$this->checkEmailAdress();
        if($this->checkEmailAdress() && $this->option == 1)
        {
            $this->logger->info("Email address already exists in Mozy");
            return;
        }
        
        // Fill data for create partner
        $this->fillDataForPartnerCreation();
        if ($this->option == 1 || ($this->accountType == "R")) {
            try {
                $resul = makeSoapCall($this->paramsConn, $this->paramsData);
                $this->logger->info("Partner ID result: " . print_r($resul,true));
                if (is_soap_fault($resul)) {
                    throw new Exception($resul->faultstring);
                } else {
                    $this->partnerId = $resul;
                    $this->partnerStatus = "ready";
                }
            } catch (Exception $fault) {
                $this->logger->info("Error while creating  partner " . "--" . $this->subscription->subscriptionId . "--" . $fault->getMessage());
                throw new Exception("Error while creating  partner " . $this->subscription->subscriptionId . "--" . $fault->getMessage());
            }
            $this->accountLicenses = $this->provisionResourcesForPartner();
            $this->logger->info("Provisioning resources:\n\t" . print_r($this->accountLicenses, true));

            foreach ($this->paramsData as $dataItem) {
                try {
                    $result = makeSoapCall($this->paramsConn, $dataItem);
                    $this->logger->info("ProvisionResourcesForPartner Response:\n\t" . print_r($result, true));
                } catch (Exception $fault) {
                    $this->logger->info("Unprovision for tenant has been called, :\n\t" . $fault->getMessage());
                    $this->unprovision();
                    throw new Exception("Error while creating subscription" . $this->subscription->subscriptionId);
                }
            }
        } else {
            $this->getPartnerStatus();
            $this->partnerId = $partnerIdParent;
            $this->companyName = $this->account->companyName;
            $this->password = \APS\generatePassword(12);
            \APS\Request::getController()->setResourceId($this->aps->id);
            $admins = $this->account->users;
            foreach ($admins as $admin) {
                if (strpos($admin->aps->type, 'admin-user')) {
                    //$this->logger->info("admin user for account," . print_r($admin, true));
                    $this->userFullName = $admin->fullName;
                    $this->userName = $admin->email;
                    break;
                }
            }

            $this->user_group_id = (int) $this->createGroupInMozy($this->syncAvailable->limit);
            $this->logger->info("UserGroupId:\n\t" . print_r($this->user_group_id, true));
            $this->accountLicenses = $this->provisionResourcesForPartner();
            $this->logger->info("Provisioning resources:\n\t" . print_r($this->accountLicenses, true));

            foreach ($this->paramsData as $dataItem) {
                try {
                    $result = makeSoapCall($this->paramsConn, $dataItem);
                    $this->logger->info("ProvisionResourcesForPartner Response:\n\t" . print_r($result, true));
                } catch (Exception $fault) {
                    $this->logger->info("Unprovision for tenant has been called, :\n\t" . $fault->getMessage());
                    $this->unprovision();
                    throw new Exception("Error while creating subscription" . $this->subscription->subscriptionId);
                }
            }
        }
        
        // Creating the default group in mozy
        $this->createMozyGroup();

        return array('partnerId' => $this->partnerId);
    }

    public function configure($new) {
        $this->logger = new SCITLogger\SCITLogger($this->account->id);
        $this->logger->info("mozyProAccount configure");

        $this->getPartnerStatus();
        parent::configure($new);
        $this->getMozyConfiguration();
        if ($this->groupviewOLD == $this->groupview) {
            $objWithLicData = $this->updateResourcesForCustomer();
            for ($i = 0; $i < count($this->paramsData); $i++) {
                $dataItem = $this->paramsData[$i];
                $this->paramsConn['methodName'] = $this->arrWsdlMethodName[$i];
                try {
                    $this->logger->info("Resources Provision or Release: " . print_r($dataItem, true));
                    $result = makeSoapCall($this->paramsConn, $dataItem);
                    $this->accountLicenses = json_encode($objWithLicData, 1);
                    $this->logger->info("Result mozy configure: " . print_r($result, true));
                    $this->logger->info("Licenses-- " . print_r($this->accountLicenses, true));
                } catch (Exception $fault) {
                    $this->logger->info("mozyAccount configure--ERR---" . $fault->getMessage());
                    throw new Exception("Error while configuring subscription" . $this->subscription->subscriptionId . "--" . $fault->getMessage());
                }
            }
        } else {
            $this->groupviewOLD = $this->groupview;
        }
    }

    public function retrieve() {

        //How the link between mozyProAccount and mozyProConf is wrong and nothing has been linked, we will inlcude a property in this
        // resource saving the Branding resource id
        if(!isset($this->brandingId)) {
            $resources = $this->subscription->resources();
            foreach ($resources as $item) {
                if ($item->apsType == "http://www.mozy.com/mozyProAPS2/mozyProConf/1.1" && $item->limit == 1) {
                    $this->brandingId = $item->apsId;
                    break;
                }
            }
        }


        $this->logger = new SCITLogger\SCITLogger($this->account->id);

        $this->logger->info("RETRIEVE");
        $this->getMozyConfiguration();
        $machineDetails = null;
        $listUsers = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->aps->id . '/mozyProAccountUser'), 0);
        foreach ($listUsers as $itemUser) {
            //    $listUsersLic = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $itemUser->aps->id . '/mozyProAccountUserLicense?eq(machineId,)'), 0);
            $listUsersLic = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $itemUser->aps->id . '/mozyProAccountUserLicense'), 0);
            foreach ($listUsersLic as $itemUsersLic) {
                $paramsLicConn = array();
                $paramsLicConn['wsdl'] = $this->mozyPro->ws_prefix . 'resource' . $this->mozyPro->ws_sufix;
                $paramsLicConn['methodName'] = "GetLicenses";
                $paramsLicData = array(
                    'api_key' => $this->cred->api_key,
                    'keystring' => $itemUsersLic->keyString,
                    'search' => null
                );
                $resultLic = makeSoapCall($paramsLicConn, $paramsLicData);
                $newItemLic = $resultLic->results[0];

                // Get the machine properties
                if ($resultLic->results[0]->machine_id != null) {
                    $resultMachine = $this->getMachineDetails($resultLic->results[0]->machine_id);
                    $itemUsersLic->alias = $resultMachine->results[0]->alias;
                    $itemUsersLic->quota_used_bytes = $resultMachine->results[0]->quota_used_bytes;
                    $itemUsersLic->quotaMachine = $resultMachine->results[0]->quota;
                    $itemUsersLic->last_backup_at = $resultMachine->results[0]->last_backup_at;
                }

                if (isset($newItemLic->machine_id)) {
                    $itemUsersLic->machineId = $newItemLic->machine_id;
                    try {
                        $newitemUsersLic = $this->objectToObject($itemUsersLic, "mozyProAccountUserLicense");
                        \APS\Request::getController()->updateResource($newitemUsersLic);
                    } catch (Exception $ex) {
                        $this->logger->info("Retrieve updated incorrect" . print_r($ex->getMessage(), true));
                    }
                }
            }
        }

        $this->getUsageForPartner();
        $this->getUsageForGroup();
    }

    public function unprovision() {
        $this->logger = new SCITLogger\SCITLogger($this->account->id);

        $this->logger->info("MozyAccount Unprovision starting ");

        $this->getMozyConfiguration();

        // Direct Model
        if ($this->mozyPro->orderType == false ) {
            $this->logger->info("mozyProAccount has been called to unprovision Direct Model");

            try {
                if (isset($this->partnerId) && $this->partnerId !='') {
                    $this->deletePartner();
                    $this->logger->info("Delete Partner data to send API: " . print_r($this->paramsData, true));
                    makeSoapCall($this->paramsConn, $this->paramsData);
                    $this->logger->info("Partner deleted in Mozy");
                }
                $this->partnerStatus = "disabled";
                $this->setPartnerStatus($this->partnerStatus);
                $this->logger->info("mozyProAccount has been called to unprovision correct-- unprovisioning partner");
            } catch (Exception $fault) {
                $this->logger->info("mozyProAccount Unprovision -- unprovisioning partner---ERROR : " . $fault->getMessage());
                throw new Exception("mozyProAccount Unprovision -- unprovisioning partner---ERROR subscription " . $fault->getMessage());
            }
        }
        //Reseller Model - Reseller subscription
        elseif (($this->mozyPro->orderType == true && $this->accountType == "R")) {
            $this->logger->info("mozyProAccount has been called to unprovision Partner");
            // If reseller has groups still active, it is necessary to disable them, if not, partner will be removed
            if (!$this->checkChildSuscriptions()) {
                $this->logger->info("Trying to destroy Reseller subscription having customers with active suscriptions ");
                throw new Exception("Trying to destroy Reseller subscription having customers with active suscriptions. Customers Subscriptions must be destroyed manually.");
            } else {
                //delete partner
                $this->deletePartner();
                try {
                    makeSoapCall($this->paramsConn, $this->paramsData);
                    $this->partnerStatus = "disabled";
                    $this->setPartnerStatus($this->partnerStatus);
                    $this->logger->info("mozyProAccount has been called to unprovision correct-- unprovisioning partner");
                } catch (Exception $fault) {
                    $this->logger->info("mozyProAccount Unprovision -- unprovisioning partner---ERR :" . $fault->getMessage());
                    throw new Exception("mozyProAccount Unprovision -- unprovisioning partner---ERR subscription" . $fault->getMessage());
                }
            }
        }
        // Reseller Model - Customer subscription
        else {
            $this->logger->info("mozyProAccount has been called to unprovision Group");
            // To delete a group, first is necessary to delete all his users and release all his resources
            // First: get all users and then send deleteUSer API call
            $listUsers = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->aps->id . '/mozyProAccountUser'), 0);
            foreach ($listUsers as $user) {
                $this->logger->info("user " . print_r($user, true));
                $this->deleteUser($user);
            }
            //Second: Release Group Resources
            $this->releaseResources();
            //Third: Delete Group

            $this->deleteGroup();
            try {
                $this->logger->info("Delete group in Mozy API call: ".print_r($this->paramsData,true));
                makeSoapCall($this->paramsConn, $this->paramsData);
                $this->logger->info("mozyProAccount has been called to unprovision correct-- unprovisioning groups");
            } catch (Exception $fault) {
                $this->logger->info("mozyProAccount Unprovision -- unprovisioning groups---ERR :" . $fault->getMessage());
                throw new Exception("mozyProAccount Unprovision -- unprovisioning groups---ERR subscription" . $fault->getMessage());
            }
        }
    }

    /**
     * We define the operation on post event
     * @verb(POST)
     * @path("/onCountersChange")
     * @param("http://aps-standard.org/types/core/resource/1.0#Notification",body)
     */
    public function onCountersChange($event) {
        $this->logger = new SCITLogger\SCITLogger($this->account->id);
        $this->logger->info("onUserChange event has been triggered, even variable has following data: " . print_r($event, true));

        return;
    }

    #############################################################################################################################################
    ##Additional method
    #############################################################################################################################################

    /**
     * We define operation for enable
     * @verb(PUT)
     * @path("/enable")
     * @param()
     */
    function enable() {
        $this->logger = new SCITLogger\SCITLogger($this->account->id);
        $this->logger->info("A Partner or Group is being enabled");
        $this->getMozyConfiguration();
        if ($this->partnerId != null && ($this->accountType == "R" || $this->accountType == "C")) {
            $this->enablePartner();
            try {
                $url = makeSoapCall($this->paramsConn, $this->paramsData);
                $this->partnerStatus = "ready";
                $this->setPartnerStatus($this->partnerStatus);
                $this->logger->info("A Customer is enable to access service" . print_r($url, true));
            } catch (Exception $fault) {
                $this->logger->info("ERROR----A Customer is enable, :\n\t" . $fault->getMessage());
                throw new Exception("Error while enable customer " . $fault->getMessage());
            }
        } else {
            $this->enableGroup();
            try {
                $this->logger->info("A Customer is enable to access service param Data" . print_r($this->paramsData, true));

                $url = makeSoapCall($this->paramsConn, $this->paramsData);
                $this->logger->info("A Group is being enabled to access service" . print_r($url, true));
            } catch (Exception $fault) {
                $this->logger->info("ERROR----A Group is enabled, :\n\t" . $fault->getMessage());
                throw new Exception("Error while activating group " . $fault->getMessage());
            }
        }
        return;
    }

    /**
     * We define operation for disable
     * @verb(PUT)
     * @path("/disable")
     * @param()
     */
    function disable() {
        $this->logger = new SCITLogger\SCITLogger($this->account->id);
        $this->logger->info("A Partner or Group is being disabled");
        $this->getMozyConfiguration();
        if ($this->partnerId != null && ($this->accountType == "R" || $this->accountType == "C")) {
            $this->disablePartner();
            try {
                $url = makeSoapCall($this->paramsConn, $this->paramsData);
                $this->partnerStatus = "disabled";
                $this->setPartnerStatus($this->partnerStatus);
                $this->logger->info("Partner has been disabled: " . print_r($url, true));
            } catch (Exception $fault) {
                $this->logger->info("ERROR----Partner disable has failed :\n\t" . $fault->getMessage());
                throw new Exception("Error while disable customer " . $fault->getMessage());
            }
        } else {
            $this->disableGroup();
            try {
                $this->logger->info("A Group is going to be disabled");
                makeSoapCall($this->paramsConn, $this->paramsData);
                $this->logger->info("Group has been disabled");
            } catch (Exception $fault) {
                throw new Exception("Error while disable group " . $fault->getMessage());
            }
        }
        return;
    }

    /**
     * We define operation for getting the clients, login url
     * @verb(GET)
     * @path("/getClientUrl")
     * @param(string,query)
     * @param(string,query)
     */
    public function getClientUrl($title, $title2) {
        $this->logger = new SCITLogger\SCITLogger($this->account->id);
        $this->getPartnerStatus();
        $this->logger->debug("A Customer is requesting clients and url to access service: " . $title . $title2);
        $this->getMozyConfiguration();

        $this->getClientUrlFill($title);
        try {
            $url = makeSoapCall($this->paramsConn, $this->paramsData);
            $this->logger->debug("A Customer is requesting clients and url to access service: " . print_r($url, true));
        } catch (Exception $fault) {
            $this->logger->info("getClientUrl for mozyPro when creating partner error, :\n\t" . $fault->getMessage());
            throw new Exception("Error while getClientUrl ");
        }

        return $url->results[0]->details[0]->value;
    }

    public function __construct() {
        $this->desktopLicenseNum = new \org\standard\aps\types\core\resource\Counter();
        $this->desktopQuota = new \org\standard\aps\types\core\resource\Counter();
        $this->serverLicenseNum = new \org\standard\aps\types\core\resource\Counter();
        $this->serverQuota = new \org\standard\aps\types\core\resource\Counter();
        $this->syncAvailable = new \org\standard\aps\types\core\resource\Counter();
        $this->syncDefaultQuota = new \org\standard\aps\types\core\resource\Counter();
    }

    #############################################################################################################################################
    ## Support functions for this class
    #############################################################################################################################################

    function getUsageForPartner() {
        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->mozyPro->ws_prefix . 'resource' . $this->mozyPro->ws_sufix;
        $this->paramsConn['methodName'] = "GetResources";

        if (($this->mozyPro->orderType == false && $this->mozyPro->api_key != null) || ($this->accountType == "R")) {
            $this->paramsData = array(
                'api_key' => $this->cred->api_key,
                'partner_id' => $this->partnerId,
                'user_group_id' => null
            );
        } else {
            $this->paramsData = array(
                'api_key' => $this->cred->api_key,
                'partner_id' => $this->partnerId,
                'user_group_id' => $this->user_group_id
            );
        }


        $this->logger->info(print_r($this->paramsConn, true));
        $result = array();
        try {
            $result = makeSoapCall($this->paramsConn, $this->paramsData);
        } catch (Exception $exsoap) {

            $this->logger->info("ERROR querying Mozy with res id :" . $this->partnerId);
            $this->logger->info(print_r($exsoap->getMessage(), true));
        }
        $this->logger->info("----MOZY RESULT OF THE ACCOUNT---------");
        $this->logger->info(print_r($result, true));

        $globalAtoInterfaz = array();

        foreach ($result as $item) {
            if (!property_exists($item, "license_type"))
                continue;

            if ((($item->license_type == "Desktop") || ($item->license_type == "Server")) && ($item->licenses > 0)) {

                $itemAtoInterfaz = array(
                    'license_type' => $item->license_type,
                    'licenses' => $item->licenses,
                    'licenses_reserved' => $item->licenses_reserved,
                    'licenses_used' => $item->licenses_used,
                    'quota' => $item->quota,
                    'quota_distributed' => $item->quota_distributed,
                    'quota_used_bytes' => $item->quota_used_bytes,
                    'license_type_order' => $item->license_type,
                    'quota_order' => $item->quota,
                    'number_order' => $item->licenses
                );
                array_push($globalAtoInterfaz, $itemAtoInterfaz);
            }
        }

        try {
            $this->accountLicenses = json_encode($globalAtoInterfaz, 1);
            \APS\Request::getController()->updateResource($this);
            $this->logger->info("----End Account---------");
        } catch (Exception $ex) {
            $this->logger->info(print_r($ex->getMessage(), true));
            $this->logger->info("----End Account ERROR---------");
        }
    }

    function getUsageForGroup() {
        $this->logger->info("----Init getUsageForGroup---------");
        $resGroupList = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->aps->id . '/mozyProAccountGroup'), 0);

        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->mozyPro->ws_prefix . 'resource' . $this->mozyPro->ws_sufix;
        $this->paramsConn['methodName'] = "GetResources";

        foreach ($resGroupList as $itemGroup) {
            $this->paramsData = array(
                'api_key' => $this->cred->api_key,
                'partner_id' => null,
                'user_group_id' => $itemGroup->groupId
            );
            $resultSoapGroup = "";
            try {
                $resultSoapGroup = makeSoapCall($this->paramsConn, $this->paramsData);
            } catch (Exception $exg) {
                $this->logger->info("ERROR querying Mozy with group id: " . $itemGroup->groupId);
                $this->logger->info(print_r($exg->getMessage(), true));
                continue;
            }

            foreach ($resultSoapGroup as $itemSoapGroup) {

                if (($itemSoapGroup->license_type == "Desktop")) {
                    $itemGroup->desktopQuotaOrdered = $itemSoapGroup->quota;
                    $itemGroup->desktopQuotaAssigned = $itemSoapGroup->quota_distributed;
                    $itemGroup->desktopKeysOrdered = $itemSoapGroup->licenses;
                    $itemGroup->desktopKeysAssigned = $itemSoapGroup->licenses_reserved + $itemSoapGroup->licenses_used;
                }

                if (($itemSoapGroup->license_type == "Server")) {
                    $itemGroup->serverQuotaOrdered = $itemSoapGroup->quota;
                    $itemGroup->serverQuotaAssigned = $itemSoapGroup->quota_distributed;
                    $itemGroup->serverKeysOrdered = $itemSoapGroup->licenses;
                    $itemGroup->serverKeysAssigned = $itemSoapGroup->licenses_reserved + $itemSoapGroup->licenses_used;
                }
            }
            try {
                $newObjGroup = $this->objectToObject($itemGroup, "mozyProAccountGroup");
                \APS\Request::getController()->updateResource($newObjGroup);
                $this->logger->info(print_r("updated", true));
            } catch (Exception $ex) {

                $this->logger->info(print_r($ex->getMessage(), true));
            }
        }
    }

    function setValuesConn($wsdl, $method) {
        $obj = array();
        $obj['wsdl'] = $wsdl;
        $obj['methodName'] = $method;
        return $obj;
    }

    function fillDataForPartnerCreation($staffGUID = NULL) {
        //part for creating the partner
        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->mozyPro->ws_prefix . 'partner' . $this->mozyPro->ws_sufix;
        $this->paramsConn['methodName'] = "Create";
        $syncQuota = null;

        $this->companyName = $this->account->companyName;
        $this->password = \APS\generatePassword(12);

        \APS\Request::getController()->setResourceId($this->aps->id);
        
        // If the var is not empty, we call this from second subscription
        if($staffGUID == NULL)
        {
            $admins = $this->account->users;
            foreach ($admins as $admin) {
                if (strpos($admin->aps->type, 'admin-user')) {
                    $this->userFullName = $admin->fullName;
                    $this->userName = $admin->email;
                    break;
                }
            }
        }
        else
        {
            $staffData = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $staffGUID), 0);
            $this->companyName = $staffData->givenName;
            $this->userName = $staffData->email;
            $this->userFullName = $staffData->fullName;
        }

        \APS\Request::getController()->setResourceId(null); // reset impersonalization

        if ($this->syncAvailable->limit == 1) {
            $syncQuota = $this->syncDefaultQuota->limit;
        }

        // The partner is created with sync disabled except if the customer is a reseller
        $syncBool = 0;
        if ($this->option != 1 && $this->accountType == "R") {
            $syncBool = 1;
            $this->resellerSync = 1;
        }

        $this->paramsData = array(
            'api_key' => $this->cred->api_key,
            'parent_partner_id' => $this->cred->root_partner_id,
            'name' => $this->companyName,
            'company_type' => "business",
            'subdomain' => null,
            'root_role' => $this->cred->root_role_id,
            'root_admin_username' => $this->userName,
            'root_admin_password' => $this->password,
            'root_admin_full_name' => $this->userFullName,
            'external_id' => $this->subscription->subscriptionId,
            'root_admin_external_id' => $this->subscription->subscriptionId,
            'details' => null,
            'root_admin_details' => null,
            'enable_sync' => $syncBool,
            'default_sync_quota' => $syncQuota
        );
    }

    function getUserGroup() {
        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->mozyPro->ws_prefix . 'user_group' . $this->mozyPro->ws_sufix;
        $this->paramsConn['methodName'] = "Get";
        $this->paramsData = array(
            'api_key' => $this->cred->api_key,
            'id' => null,
            'search' => array('partner_id' => $this->partnerId)
        );
    }

    function provisionResourcesForPartner() {

        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->mozyPro->ws_prefix . 'resource' . $this->mozyPro->ws_sufix;
        $this->paramsConn['methodName'] = "Provision";

        $globalA = array();
        $globalAtoInterfaz = array();

        $resListjson = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->subscription->aps->id . '/resources'), 0);

        $dektopNum = 0;
        $serverNum = 0;
        $dektopQuota = 0;
        $serverQuota = 0;
        $syncDefaultQuota = new stdClass();
        $syncDefaultQuota->limit = 0;

        foreach ($resListjson as $resource) {

            if (property_exists($resource, "property") && property_exists($resource, "limit")) {
                if ($resource->property == "desktopLicenseNum" && $resource->limit > 0) {
                    $dektopNum = $resource->limit;
                }
                if ($resource->property == "desktopQuota" && $resource->limit > 0) {
                    $dektopQuota = $resource->limit;
                }
                if ($resource->property == "serverLicenseNum" && $resource->limit > 0) {
                    $serverNum = $resource->limit;
                }
                if ($resource->property == "serverQuota" && $resource->limit > 0) {
                    $serverQuota = $resource->limit;
                }
            }
        }
        $itemA = array(
            'api_key' => $this->cred->api_key,
            'partner_id' => $this->partnerId,
            'user_group_id' => $this->user_group_id,
            'license_type' => 'Desktop',
            'quota' => $dektopNum,
            'licenses' => $dektopQuota
        );
        $itemAtoInterfaz = array(
            'license_type' => "Desktop",
            'licenses' => $dektopNum,
            'licenses_reserved' => "0",
            'licenses_used' => "0",
            'quota' => $dektopQuota,
            'quota_distributed' => "0",
            'quota_used_bytes' => "0",
            'license_type_order' => "Desktop",
            'quota_order' => $dektopQuota,
            'number_order' => $dektopNum
        );

        array_push($globalA, $itemA);
        array_push($globalAtoInterfaz, $itemAtoInterfaz);

        $itemA = array(
            'api_key' => $this->cred->api_key,
            'partner_id' => $this->partnerId,
            'user_group_id' => $this->user_group_id,
            'license_type' => "Server",
            'quota' => $serverNum,
            'licenses' => $serverQuota
        );
        $itemAtoInterfaz = array(
            'license_type' => "Server",
            'licenses' => $serverNum,
            'licenses_reserved' => "0",
            'licenses_used' => "0",
            'quota' => $serverQuota,
            'quota_distributed' => "0",
            'quota_used_bytes' => "0",
            'license_type_order' => "Server",
            'quota_order' => $serverQuota,
            'number_order' => $serverNum
        );
        array_push($globalA, $itemA);
        array_push($globalAtoInterfaz, $itemAtoInterfaz);

        \APS\Request::getController()->setResourceId(null); // impersonate
        $this->paramsData = $globalA;
        return json_encode($globalAtoInterfaz, 1);
    }

    function findGroupsByPartner() {
        // Local vars
        $res = null;

        $paramsConn = array();
        $paramsConn['wsdl'] = $this->mozyPro->ws_prefix . 'user_group' . $this->mozyPro->ws_sufix;
        $paramsConn['methodName'] = "Get";

        $paramsData = array(
            'api_key' => $this->cred->api_key,
            'id' => null,
        );
        try {
            $res = makeSoapCall($paramsConn, $paramsData);
        } catch (Exception $ex) {
            $this->logger->info("ERROR - Partner has not got groups ");
            throw new Exception("ERROR - Partner has not got groups " . $ex->getMessage());
        }
        return $res;
    }

    function deletePartner() {
        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->mozyPro->ws_prefix . 'partner' . $this->mozyPro->ws_sufix;
        $this->paramsConn['methodName'] = "Delete";

        //recorrer las licencias y crear
        $this->paramsData = array(
            'api_key' => $this->cred->api_key,
            'id' => $this->partnerId
        );
    }

    function deleteUser($user) {

        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->mozyPro->ws_prefix . 'user' . $this->mozyPro->ws_sufix;
        $this->paramsConn['methodName'] = "Delete";

        $this->paramsData = array(
            'api_key' => $this->cred->api_key,
            'id' => $user->mozyUserId
        );
        try {

            $res = makeSoapCall($this->paramsConn, $this->paramsData);
            //$this->logger->info("Delete user call " . print_R($res,true));
        } catch (Exception $ex) {
            $this->logger->info("ERROR - Delete user call failed ");
            throw new Exception("Error creating group in mozy " . $ex->getMessage());
        }
    }

    function releaseResources() {
        $this->logger->info("ReleaseResources in Mozy ");

        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->mozyPro->ws_prefix . 'resource' . $this->mozyPro->ws_sufix;
        $this->paramsConn['methodName'] = "Release";

        $allResources = array();

        if ($this->desktopQuota->limit > 0 && $this->desktopLicenseNum->limit > 0) {
            $desktop = array(
                'api_key' => $this->cred->api_key,
                'partner_id' => $this->partnerId,
                'user_group_id' => $this->user_group_id,
                'license_type' => 'Desktop',
                'licenses' => $this->desktopLicenseNum->limit,
                'quota' => $this->desktopQuota->limit
            );
            array_push($allResources, $desktop);
        }
        if ($this->serverLicenseNum->limit > 0 && $this->serverQuota->limit > 0) {
            $server = array(
                'api_key' => $this->cred->api_key,
                'partner_id' => $this->partnerId,
                'user_group_id' => $this->user_group_id,
                'license_type' => 'Server',
                'licenses' => $this->serverLicenseNum->limit,
                'quota' => $this->serverQuota->limit
            );
            array_push($allResources, $server);
        }
        $this->paramsData = $allResources;

        foreach ($this->paramsData as $license) {
            try {
                $this->logger->info("Resources to release in Mozy: ".print_r($license,true));
                $res = makeSoapCall($this->paramsConn, $license);
                $this->logger->info("Resources released in Mozy: ".print_r($res,true));
            } catch (Exception $ex) {
                $this->logger->info("ERROR - Release Resources call failed ");
                throw new Exception("ERROR in ReleaseResources API call " . $ex->getMessage());
            }
        }
    }

    function getClientUrlFill($title) {
        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->mozyPro->ws_prefix . 'admin' . $this->mozyPro->ws_sufix;
        $this->paramsConn['methodName'] = "Get";

        $this->paramsData = array(
            'api_key' => $this->cred->api_key,
            'id' => null,
            'search' => array('username' => strtolower($this->userName)),
            'details' => array('item' => 'login_href')
        );
    }

    function enablePartner() {
        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->mozyPro->ws_prefix . 'partner' . $this->mozyPro->ws_sufix;
        $this->paramsConn['methodName'] = "Update";

        $this->paramsData = array(
            'api_key' => $this->cred->api_key,
            'id' => $this->partnerId,
            'search' => null,
            'name' => null,
            'company_type' => null,
            'subdomain' => null,
            'root_role' => null,
            'external_id' => null,
            'details' => array('item' => array('key' => "status", 'value' => "active"))
        );
    }

    function disablePartner() {
        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->mozyPro->ws_prefix . 'partner' . $this->mozyPro->ws_sufix;
        $this->paramsConn['methodName'] = "Update";

        $this->paramsData = array(
            'api_key' => $this->cred->api_key,
            'id' => $this->partnerId,
            'search' => null,
            'name' => null,
            'company_type' => null,
            'subdomain' => null,
            'root_role' => null,
            'external_id' => null,
            'details' => array('item' => array('key' => "status", 'value' => "suspended"))
        );
    }

    function disableGroup($group = null) {
        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->mozyPro->ws_prefix . 'user_group' . $this->mozyPro->ws_sufix;
        $this->paramsConn['methodName'] = "Update";

        if ($group == null) {
            $group = $this->user_group_id;
        }
        $this->paramsData = array(
            'api_key' => $this->cred->api_key,
            'id' => $group,
            'search' => null,
            'name' => null,
            'default_quotas' => null,
            'default_for_partner' => null,
            'external_id' => null,
            'details' => array('item' => array('key' => "status", 'value' => "suspended"))
        );
    }

    function enableGroup() {
        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->mozyPro->ws_prefix . 'user_group' . $this->mozyPro->ws_sufix;
        $this->paramsConn['methodName'] = "Update";

        $this->paramsData = array(
            'api_key' => $this->cred->api_key,
            'id' => $this->user_group_id,
            'search' => null,
            'name' => null,
            'default_quotas' => null,
            'default_for_partner' => null,
            'external_id' => null,
            'details' => array('item' => array('key' => "status", 'value' => "active"))
        );
    }

    function createUserGroupResource() {
        $apsc = \APS\Request::getController();
        $resItem = $apsc->getResource($this->aps->id);
        $jsonData = $apsc->getType("http://www.mozy.com/mozyProAPS2/mozyProAccount/1.5");
    }

    function retrievePartnerData() {
        $this->logger = new SCITLogger\SCITLogger($this->account->id);

        $paramsConn = array();
        $paramsConn['wsdl'] = $this->mozyPro->ws_prefix . 'resource' . $this->mozyPro->ws_sufix;
        $paramsConn['methodName'] = "GetResources";

        if ($this->mozyPro->orderType == false || ($this->mozyPro->orderType == true && $this->accountType == "R")) {
            $paramsData = array(
                'api_key' => $this->cred->api_key,
                'partner_id' => $this->partnerId,
                'user_group_id' => null
            );
        } else {
            $paramsData = array(
                'api_key' => $this->cred->api_key,
                'partner_id' => $this->partnerId,
                'user_group_id' => $this->user_group_id
            );
        }

        $result = makeSoapCall($paramsConn, $paramsData);

        $globalAtoInterfaz = array();

        foreach ($result as $item) {
            if ((($item->license_type == "Desktop") || ($item->license_type == "Server")) && ($item->licenses > 0)) {
                $itemAtoInterfaz = array(
                    'license_type' => $item->license_type,
                    'licenses' => $item->licenses,
                    'licenses_reserved' => $item->licenses_reserved,
                    'licenses_used' => $item->licenses_used,
                    'quota' => $item->quota,
                    'quota_distributed' => $item->quota_distributed,
                    'quota_used_bytes' => $item->quota_used_bytes,
                    'license_type_order' => $item->license_type,
                    'quota_order' => $item->quota,
                    'number_order' => $item->licenses
                );
                array_push($globalAtoInterfaz, $itemAtoInterfaz);
            }
        }
        $this->accountLicenses = json_encode($globalAtoInterfaz, 1);
        $this->logger->info("Get previous account licenses/resources: " . print_R($this->accountLicenses,true));

    }

    /**
     * Request Mozy Provision or Release resources depending on the licenses counters
     * @return array listOfLic
     */
    function updateResourcesForCustomer() {
        $this->logger = new SCITLogger\SCITLogger($this->account->id);

        $this->retrievePartnerData();

        $globalA = array();

        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->mozyPro->ws_prefix . 'resource' . $this->mozyPro->ws_sufix;

        $listOfLic = json_decode($this->accountLicenses, 1);
        if (count($listOfLic) == 1 || count($listOfLic) == 0) {
            if(count($listOfLic) == 0){
                $listOfLic[] = array("license_type" => "Server", "licenses" => 0, "licenses_reserved" => 0, "licenses_used" => 0, "quota" => 0, "quota_distributed" => 0, "quota_used_bytes" => 0, "license_type_order" => "Server", "quota_order" => 0, "number_order" => 0);
                $listOfLic[] = array("license_type" => "Desktop", "licenses" => 0, "licenses_reserved" => 0, "licenses_used" => 0, "quota" => 0, "quota_distributed" => 0, "quota_used_bytes" => 0, "license_type_order" => "Desktop", "quota_order" => 0, "number_order" => 0);
            }
            elseif ($listOfLic[0]["license_type"] == "Desktop") {
                $listOfLic[] = array("license_type" => "Server", "licenses" => 0, "licenses_reserved" => 0, "licenses_used" => 0, "quota" => 0, "quota_distributed" => 0, "quota_used_bytes" => 0, "license_type_order" => "Server", "quota_order" => 0, "number_order" => 0);
            }
            elseif ($listOfLic[0]["license_type"] == "Server") {
                $listOfLic[] = array("license_type" => "Desktop", "licenses" => 0, "licenses_reserved" => 0, "licenses_used" => 0, "quota" => 0, "quota_distributed" => 0, "quota_used_bytes" => 0, "license_type_order" => "Desktop", "quota_order" => 0, "number_order" => 0);
            }
        }

        foreach ($listOfLic as $key => $resource) {

            if (($resource["license_type"] == "Desktop") && (($this->desktopLicenseNum->limit != $resource["licenses"]) || ($this->desktopQuota->limit != $resource["quota"]))) {
                $itemA = array(
                    'api_key' => $this->cred->api_key,
                    'partner_id' => $this->partnerId,
                    'user_group_id' => $this->user_group_id,
                    'license_type' => 'Desktop',
                    'quota' => ($this->desktopLicenseNum->limit >= $resource["licenses"]) ? $this->desktopLicenseNum->limit - $resource["licenses"] : $resource["licenses"] - $this->desktopLicenseNum->limit,
                    'licenses' => ($this->desktopQuota->limit >= $resource["quota"]) ? $this->desktopQuota->limit - $resource["quota"] : $resource["quota"] - $this->desktopQuota->limit
                );
                $listOfLic[$key]["licenses"] = $this->desktopLicenseNum->limit;
                $listOfLic[$key]["quota"] = $this->desktopQuota->limit;
                array_push($globalA, $itemA);
                $method = ($this->desktopLicenseNum->limit < $resource["licenses"] || $this->desktopQuota->limit < $resource["quota"]) ? "Release" : "Provision";
                array_push($this->arrWsdlMethodName, $method);
            }
            if (($resource["license_type"] == "Server") && (($this->serverLicenseNum->limit != $resource["licenses"]) || ($this->serverQuota->limit != $resource["quota"]))) {
                $itemA = array(
                    'api_key' => $this->cred->api_key,
                    'partner_id' => $this->partnerId,
                    'user_group_id' => $this->user_group_id,
                    'license_type' => "Server",
                    'quota' => ($this->serverLicenseNum->limit >= $resource["licenses"]) ? $this->serverLicenseNum->limit - $resource["licenses"] : $resource["licenses"] - $this->serverLicenseNum->limit,
                    'licenses' => ($this->serverQuota->limit >= $resource["quota"]) ? $this->serverQuota->limit - $resource["quota"] : $resource["quota"] - $this->serverQuota->limit
                );
                $listOfLic[$key]["licenses"] = $this->serverLicenseNum->limit;
                $listOfLic[$key]["quota"] = $this->serverQuota->limit;
                array_push($globalA, $itemA);
                $method = ($this->serverLicenseNum->limit < $resource["licenses"] || $this->serverQuota->limit < $resource["quota"]) ? "Release" : "Provision";
                array_push($this->arrWsdlMethodName, $method);
            }
        }
        $this->paramsData = $globalA;
        return $listOfLic;
    }

    private function getMachineDetails($machineID) {
        // Local vars
        $paramsMachine = array();
        $paramsMachine['wsdl'] = $this->mozyPro->ws_prefix . 'machine' . $this->mozyPro->ws_sufix;
        $paramsMachine['methodName'] = "Get";

        $paramsMachineData = array(
            'api_key' => $this->cred->api_key,
            'id' => $machineID
        );
        try {
            $this->logger->info("Get machine API call :-----" . print_r($paramsMachineData,true));
            $resultMachine = makeSoapCall($paramsMachine, $paramsMachineData);
        } catch (Exception $ex) {
            $this->logger->info("ERR :-----" . $ex->getMessage());
        }
        return $resultMachine;
    }

    // Catch the credentials from branding
    function getMozyConf() {
        $apsc = \APS\Request::getController();
        $apsId = $this->brandingId;
        $configuration = $apsId != null ? $apsc->getResource($apsId) : $this->mozyPro;
        $this->cred = new stdClass();
        $this->cred->api_key = $configuration->api_key;
        $this->cred->root_partner_id = $configuration->root_partner_id;
        $this->cred->root_role_id = $configuration->root_role_id;
    }

    /**
     * We define the operation on post event
     * @verb(POST)
     * @path("/setEnableDisableSync")
     * @param(string, body)
     */
    public function setEnableDisableSync($id) {
        $this->logger = new SCITLogger\SCITLogger($this->account->id);
        $this->logger->info("MozyAccount - SetEnableDisableSync starting ");

        // Exception if the partner is disabled (Reseller mode)
        $this->getPartnerStatus();

        // Get credentials in $this->cred
        $this->getMozyConfiguration();

        // Check for new value of sync (enabled if it currently is disabled and viceversa)
        $newSync = null;

        if ($this->accountType != "R") {
            if ($this->syncAvailable->usage == 1) {
                $newSync = False;
                $this->syncAvailable->usage = 0;
            } else {
                $newSync = True;
                $this->syncAvailable->usage = 1;
            }
        } else {
            if ($this->option != 1) {
                if ($this->resellerSync == 1) {
                    $newSync = False;
                    $this->resellerSync = 0;
                } else {
                    $newSync = True;
                    $this->resellerSync = 1;
                }
            }
        }

        // newSync has the new sync value
        $this->logger->info("Sync has been changed to: " . (($newSync) ? "true" : "false"));

        // Change Request 2015-11-18 // Every user with sync activated must disable sync before disable partner sync.
        if ($newSync == false) {
            // If the subscription belongs to a Customer (either from Service Provider or Reseller)
            if ($this->accountType != "R") {
                $this->deleteSyncUsers();
            } else
            // If the subscription belongs to a Reseller
            if ($this->accountType == "R") {
                $this->disableCustomerSync();
            }
        }

        // Prepare Array for SOAP params
        $paramsSync = array();

        // If Tenant is a Partner (Reseller or Customer in Direct model)
        if (($this->mozyPro->orderType == false && $this->mozyPro->api_key != null) || ($this->accountType == "R")) {
            $paramsSync['wsdl'] = $this->mozyPro->ws_prefix . 'partner' . $this->mozyPro->ws_sufix;
            $paramsSync['methodName'] = "Update";

            $paramsSyncData = array(
                'api_key' => $this->cred->api_key,
                'id' => $this->partnerId,
                'search' => null,
                'name' => null,
                'company_name' => null,
                'subdomain' => null,
                'root_role' => null,
                'external_id' => null,
                'details' => null,
                'enable_sync' => $newSync
            );
            // Fix: in case of Reseller subscription (it has no limit in syncDefaultQuota)
            // Do not send 'default_sync_quota', soapCall sends as 0 instead of false
            if ($this->accountType != "R") {
                $paramsSyncData['default_sync_quota'] = $this->syncDefaultQuota->limit;
            }
            // If Tenant is a User Group (Reseller's Customer)
        } else {
            $paramsSync['wsdl'] = $this->mozyPro->ws_prefix . 'user_group' . $this->mozyPro->ws_sufix;
            $paramsSync['methodName'] = "Update";
            $paramsSyncData = array(
                'api_key' => $this->cred->api_key,
                'id' => $this->user_group_id,
                'search' => null,
                'name' => null,
                'default_quotas' => null,
                'default_for_partner' => null,
                'external_id' => null,
                'details' => null,
                'enable_sync' => $newSync
            );
        }

        try {
            makeSoapCall($paramsSync, $paramsSyncData);
        } catch (Exception $ex) {
            $this->logger->info("ERROR :----- No sync updated" . $ex->getMessage());
        }

        // In direct model, if we active sync, active in each group
        if ($this->accountType == "C" && $newSync == True) {
            $paramsSync['wsdl'] = $this->mozyPro->ws_prefix . 'user_group' . $this->mozyPro->ws_sufix;
            $paramsSync['methodName'] = "Update";
            $groups = null;
            
            // Get the groups for this partner
            $this->getUserGroup();
            try {
                $groups = makeSoapCall($this->paramsConn, $this->paramsData);
            } catch (Exception $ex) {
                $this->logger->info("ERROR :----- Partner has not got groups" . $ex->getMessage());
            }
            
            // For each group, activate sync
            for ($i = 0; $i < count($groups->results); $i++) {
                $paramsSyncData = array(
                    'api_key' => $this->cred->api_key,
                    'id' => $groups->results[$i]->id,
                    'search' => null,
                    'name' => null,
                    'default_quotas' => null,
                    'default_for_partner' => null,
                    'external_id' => null,
                    'details' => null,
                    'enable_sync' => $newSync
                );
                try {
                    makeSoapCall($paramsSync, $paramsSyncData);
                } catch (Exception $ex) {
                    $this->logger->info("ERROR :----- No sync updated" . $ex->getMessage());
                }
            }
        }

        // _
        // Update the resource
        $apsc = \APS\Request::getController();
        $res = $apsc->updateResource($this);

        return;
    }

    /**
     * @verb(GET)
     * @path("/manualRetrieve")
     */
    public function manualRetrieve() {
        $this->logger = new SCITLogger\SCITLogger($this->account->id);
        $this->retrieve();
    }

    // Create group in mozy in not partner creation case
    private function createGroupInMozy($enableSyncMozy) {
        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->mozyPro->ws_prefix . 'user_group' . $this->mozyPro->ws_sufix;
        $this->paramsConn['methodName'] = "Create";
        $default_quotas = null;
        if ($enableSyncMozy == True) {
            $default_quotas[] = new stdClass();
            $default_quotas[0]->type = 'Sync';
            $default_quotas[0]->quota = $this->syncDefaultQuota->limit;
        }

        // Even the service plan has sync available, the user groups must be created with sync disabled
        $defaultSyncStatus = 0;
        $this->paramsData = array(
            'api_key' => $this->cred->api_key,
            'partner_id' => $this->partnerId,
            'name' => $this->account->companyName,
            'default_quotas' => $default_quotas,
            'default_for_partner' => false,
            'external_id' => $this->subscription->subscriptionId,
            'enable_sync' => $defaultSyncStatus
        );
        try {
            $res = makeSoapCall($this->paramsConn, $this->paramsData);
        } catch (Exception $ex) {
            throw new Exception("Error creating group in mozy " . $ex->getMessage());
        }

        return $res;
    }

    // Check if the credentials must be catched in mozypro or branding
    private function getMozyConfiguration() {
        if ($this->mozyPro->api_key == null) {
            $this->getMozyConf();
        } else {
            $this->cred = new stdClass();
            $this->cred->model = 1;
            $this->cred->api_key = $this->mozyPro->api_key;
            $this->cred->root_partner_id = $this->mozyPro->root_partner_id;
            $this->cred->root_role_id = $this->mozyPro->root_role_id;
        }
    }

    // C -> customer, R -> reseller, CR -> customer buys to reseller
    private function getAccountType() {
        if ($this->option == 2) {
            if ($this->vendorData->VendorAccountID < 0) {
                $this->accountType = "R";
            } else {
                $this->accountType = "CR";
            }
        } else {
            if ($this->option == 3) {
                if ($this->vendorData->VendorAccountID > 1) {
                    $this->accountType = "CR";
                } else {
                    $this->accountType = "R";
                }
            }
        }
    }

    private function deleteGroup() {
        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->mozyPro->ws_prefix . 'user_group' . $this->mozyPro->ws_sufix;
        $this->paramsConn['methodName'] = "Delete";

        $this->paramsData = array(
            'api_key' => $this->cred->api_key,
            'id' => $this->user_group_id,
            'search' => null
        );
    }

    // Disable sync for the Customer's users
    private function deleteSyncUsers() {
        $this->logger->info("Delete Sync Users ");

        // Retrieve accountUsers of the subscription with enableSync true
        $listUsers = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->aps->id . '/mozyProAccountUser?eq(enableSync,1)'), 0);
        foreach ($listUsers as $itemUser) {
            // Change values for reset sync
            $itemUser->enableSync = 0;
            $itemUser->syncQuota = $this->syncDefaultQuota->limit;

            try {
                // Request to accountUser.configure() with new syncEnable parameter
                \APS\Request::getController()->getIo()->sendRequest('PUT', '/aps/2/resources/' . $itemUser->aps->id, json_encode($itemUser));
                $this->logger->info("// User: " . $itemUser->aps->id . " / Disabled Sync");
            } catch (Exception $ex) {
                $this->logger->info("Error: " . print_R($ex->getMessage(), 1));
            }
        }
    }

    /**
     * Disable sync for the Reseller's Customers
     */
    private function disableCustomerSync() {
        $this->logger->info("/// Disable Reseller users ///");
        // Retrieve all Reseller's Customers with sync enabled
        $customersWithSync = \APS\Request::getController()->getIo()->sendRequest("GET", "/aps/2/resources/?implementing(http://www.mozy.com/mozyProAPS2/mozyProAccount/1.5),eq(partnerId," . $this->partnerId . "),eq(accountType,CR),eq(syncAvailable.limit,1),eq(syncAvailable.usage,1)");
        // Request to each customer to disable its sync
        foreach (json_decode($customersWithSync) as $customer) {
            $this->logger->info("// Disable Customer " . $customer->aps->id . " Users Sync");
            \APS\Request::getController()->getIo()->sendRequest("POST", "/aps/2/resources/" . $customer->aps->id . "/setEnableDisableSync", array("id" => "R"));
        }
    }

    // If the reseller has mozy active customers, throw exception in unprovision
    private function checkChildSuscriptions() {
        // Local vars
        $sw = true;

        $listUsers = \APS\Request::getController()->getIo()->sendRequest("GET", "/aps/2/resources/?implementing(http://www.mozy.com/mozyProAPS2/mozyProAccount/1.5),eq(partnerId," . $this->partnerId . ")");
        $listUsersFormated = json_decode($listUsers);
        foreach ($listUsersFormated as $itemUser) {
            if ($itemUser->aps->status == "aps:ready" && $itemUser->aps->id != $this->aps->id) {
                $this->logger->info("Manual destroy required in suscription: " . print_R($itemUser->aps->id, true));
                $this->logger->info("Disabling user group: " . print_R($itemUser->user_group_id, true));
                $this->disableGroup($itemUser->user_group_id);
                try {
                    $this->logger->info("A Customer is disable to access service param Data" . print_r($this->paramsData, true));
                    makeSoapCall($this->paramsConn, $this->paramsData);
                    $this->logger->info("Group disabled");
                } catch (Exception $fault) {
                    throw new Exception("Error while disable group " . $fault->getMessage());
                }
                $sw = false;
            }
        }
        return $sw;
    }

    private function getPartnerStatus() {
        if ($this->partnerStatus == "disabled") {
            $this->logger->info("PARTNER IS DISABLED");
            throw new Exception("PARTNER IS DISABLED");
        }
    }

    private function setPartnerStatus($status) {
        $listCustomers = \APS\Request::getController()->getIo()->sendRequest("GET", "/aps/2/resources/?implementing(http://www.mozy.com/mozyProAPS2/mozyProAccount/1.5),eq(partnerId," . $this->partnerId . ")");
        $listCustomersFormated = json_decode($listCustomers);
        foreach ($listCustomersFormated as $itemCustomer) {
            $itemCustomer->partnerStatus = $status;

            $newresAccount = $this->objectToObject($itemCustomer, "mozyProAccount");
            \APS\Request::getController()->updateResource($newresAccount);
        }
    }
    
    // Check if email exists in mozy, false if it exists
    private function checkEmailAdress($userName = NULL)
    {
        // Get the user Email to search in Mozy
        \APS\Request::getController()->setResourceId($this->aps->id);
        $admins = $this->account->users;
        foreach ($admins as $admin) {
            if (strpos($admin->aps->type, 'admin-user')) {
                if($userName == NULL)
                {
                    $userName = $admin->email;
                }
                break;
            }
        }

        // Test credentials

        $paramsConn = array();
        $paramsConn['wsdl'] = $this->mozyPro->ws_prefix . 'partner' . $this->mozyPro->ws_sufix;
        $paramsConn['methodName'] = "Get";
        $paramsData = array(
            'api_key' => $this->cred->api_key,
            'id' => $this->partnerId,
            'search' => array('root_admin_username' => $userName)
        );
        // Execute the query, if it does not find anything, returns false
        try
        {
            makeSoapCall($paramsConn, $paramsData);
        } catch (Exception $ex) {
            return false;
        }
        return true;
    }
    
    /**
	* checkAvailabilityEmail
	* @verb(POST)
	* @path("/checkAvailabilityEmail")
	* @param(string, query)
	* @return(string, text/plain)
	*/
	public function checkAvailabilityEmail($json)
        {
            $this->logger = new SCITLogger\SCITLogger($this->account->id);
            // Custom method for return the available emails in mozy, called from UserList UI

            $aux = null;
            try{
                $emails = json_decode($json);
                $this->logger->info("Emails to validate: ".print_r($emails,1));

                // Check each passed mail for the availability
                foreach($emails as $email)
                {
                    $data = new \stdClass();
                    $data->email       = strtolower($email);
                    
                    // If the credencials do not exist, is because we are tying create a partner from CCP
                    if(!isset($this->cred->api_key))
                    {
                        $this->getMozyConfiguration();
                    }
                    
                    if(!$this->checkEmailAdress($data->email))
                    {
                        // If the email does not exist, we can use it
                        $aux[] = $email;
                    }
                }

                if ($aux)
                {
                    $this->logger->info("Valid emails: ".print_r($aux,1));
                    $aux = json_encode($aux);
                }
                else
                {
                    //There isn't any valid email.
                    $aux = "513";
                }	
                return $aux;

            }
            catch(Exception $e){
                $this->logger->info("Validate email error: " . print_r($e->getMessage(),1));
                return "500";
            }
    }
    
    /**
    * createAdmin
    * @verb(POST)
    * @path("/createPartner")
    * @param(string, query)
    * @return(string,text/plain)
    */
    public function createPartner($staffGUID)
    {
        $this->logger = new SCITLogger\SCITLogger($this->account->id);
        // Custom method for return the available emails in mozy, called from UserList UI
        $this->logger->info("createPartner");
        $this->logger->info("staffGUID: " . print_R($staffGUID, true));
        
        // If the credencials do not exist, is because we are tying create a partner from CCP
        if(!isset($this->cred->api_key))
        {
            $this->getMozyConfiguration();
        }
        // Create the new partner
        $this->fillDataForPartnerCreation($staffGUID);
        $resul = makeSoapCall($this->paramsConn, $this->paramsData);
        if (is_soap_fault($resul))
        {
            throw new Exception($resul->faultstring);
        }
        else
        {
            $this->partnerId = $resul;
            $this->partnerStatus = "ready";
        }
        
        // Provisioning the licenses
        $this->accountLicenses = $this->provisionResourcesForPartner();
        $this->logger->info("Provision for mozyProAccount has been called, and this is the user group:\n\t" . print_r($this->accountLicenses, true));

        foreach ($this->paramsData as $dataItem) {
            try {
                $result = makeSoapCall($this->paramsConn, $dataItem);
            } catch (Exception $fault) {
                $this->logger->info("Unprovision for tenant has been called, :\n\t" . $fault->getMessage());
                $this->unprovision();
                throw new Exception("Error while creating subscription" . $this->subscription->subscriptionId);
            }
            $this->logger->info("Provision for account has been called, it received as input account information:\n\t" . print_r($result, true));
        }
        // Create the default group in Mozy
        $this->createMozyGroup();
        // Save data in PA
        \APS\Request::getController()->updateResource($this);
        return;
    }
    
    // Method for create the default group in Mozy
    private function createMozyGroup()
    {
        $this->getUserGroup();

        try {
            $result = makeSoapCall($this->paramsConn, $this->paramsData);
            if (($this->option == 1 || ($this->accountType == "R")) || $this->option == NULL) {
                $this->user_group_id = $result->results[0]->id;
            }
            $apsc = \APS\Request::getController();

            $apsc->setResourceId($this->aps->id);
            \APS\TypeLibrary::registerClass('mozyProAccountGroup', false);

            $resourceGroup = new mozyProAccountGroup();
            $resourceGroup->aps = new \APS\ResourceMeta("http://www.mozy.com/mozyProAPS2/mozyProAccountGroup/1.0");
            if (($this->option == 2 && $this->accountType != "R") || ($this->option == 3 && $this->accountType != "R")) {
                $resourceGroup->name = $this->account->companyName;
            } else {
                $resourceGroup->name = "Default";
            }
            if ($this->syncAvailable->limit == True || $this->syncAvailable->limit == 1) {
                $resourceGroup->enableSync = 1;
            } else {
                $resourceGroup->enableSync = 0;
            }

            $resourceGroup->mozyProAccount = $this;
            $resourceGroup->groupId = $this->user_group_id;
            $resourceGroup->serverKeysAssigned = 0;
            $resourceGroup->serverKeysOrdered = $this->serverLicenseNum->limit;
            $resourceGroup->desktopKeysAssigned = 0;
            $resourceGroup->desktopKeysOrdered = $this->desktopLicenseNum->limit;
            $resourceGroup->serverQuotaAssigned = 0;
            $resourceGroup->serverQuotaOrdered = $this->serverQuota->limit;
            $resourceGroup->desktopQuotaAssigned = 0;
            $resourceGroup->desktopQuotaOrdered = $this->desktopQuota->limit;
            $resourceGroup->syncQuota = $this->syncDefaultQuota->limit;

            $output = $apsc->registerResource($resourceGroup);
            $this->logger->info("MozyProAccountGroup resource provisioned in OSA");
        } catch (Exception $fault) {
            $this->logger->info("Unprovision for tenant has been called, :\n\t" . $fault->getMessage());
            $this->unprovision();
            throw new Exception("Error while creating subscription " . $this->subscription->subscriptionId . "---" . $fault->getMessage());
        }
    }

    function objectToObject($instance, $className) {
        return unserialize(sprintf(
                        'O:%d:"%s"%s', strlen($className), $className, strstr(strstr(serialize($instance), '"'), ':')
        ));
    }

}

?>
