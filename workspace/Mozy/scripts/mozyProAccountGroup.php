<?php

require_once "framework.php";
require_once "aps/2/runtime.php";
require_once "utils.php";
require_once "mozyProAccountUser.php";

/**
 * Class mozyProAccountGroup
 * @type("http://www.mozy.com/mozyProAPS2/mozyProAccountGroup/1.0")
 * @implements("http://aps-standard.org/types/core/resource/1.0")
 */
class mozyProAccountGroup extends \APS\ResourceBase {

    /**
     * @link("http://www.mozy.com/mozyProAPS2/mozyProAccount/1.5")
     * @required
     */
    public $mozyProAccount;

    /**
     * @type(integer)
     * @title("Group id")
     */
    public $groupId;

    /**
     * @type(string)
     * @title("$name")
     */
    public $name;

    /**
     * @type(integer)
     * @title("serverKeysAssigned")
     */
    public $serverKeysAssigned;

    /**
     * @type(integer)
     * @title("serverKeysOrdered")
     */
    public $serverKeysOrdered;

    /**
     * @type(integer)
     * @title("desktopKeysAssigned")
     */
    public $desktopKeysAssigned;

    /**
     * @type(integer)
     * @title("desktopKeysOrdered")
     */
    public $desktopKeysOrdered;

    /**
     * @type(integer)
     * @title("serverQuotaAssigned")
     */
    public $serverQuotaAssigned;

    /**
     * @type(integer)
     * @title("serverQuotaOrdered")
     */
    public $serverQuotaOrdered;

    /**
     * @type(integer)
     * @title("desktopQuotaAssigned")
     */
    public $desktopQuotaAssigned;

    /**
     * @type(integer)
     * @title("desktopQuotaOrdered")
     */
    public $desktopQuotaOrdered;

    /**
     * @type(integer)
     * @title("enable_sync")
     */
    public $enableSync;

    /**
     * @type(integer)
     * @title("syncQuota")
     */
    public $syncQuota;
    private $paramsConn;
    private $paramsData;
    private $cred;
    protected $logger;


    public function provision() {

        $this->logger = new SCITLogger\SCITLogger($this->mozyProAccount->account->id);

        $this->logger->info("Provision Account group");
        $this->getMozyConfiguration();
        $this->fillDataForUserGroup();
        if ($this->groupId != "")
            return;

        $this->groupId = makeSoapCall($this->paramsConn, $this->paramsData);
        $this->logger->info("Provision function group id" . print_r($this->groupId, true));

        return array('name' => $this->name, 'groupId' => $this->groupId, 'serverKeysAssigned' => '0', 'serverKeysOrdered' => '0', 'desktopKeysAssigned' => '0', 'desktopKeysOrdered' => '0', 'serverQuotaAssigned' => '0', 'serverQuotaOrdered' => '0', 'desktopQuotaAssigned' => '0', 'desktopQuotaOrdered' => '0');
    }

    public function configure($new) {
        parent::configure($new);

        $this->logger = new SCITLogger\SCITLogger($this->mozyProAccount->account->id);
        $this->logger->info("Configure function");
        $this->getMozyConfiguration();
        $this->fillDataForUpdateUserGroup();
        $result = makeSoapCall($this->paramsConn, $this->paramsData);
        $this->logger->info("configuring function group id" . print_r($result,true));
    }

    public function unprovision() {
        $this->logger = new SCITLogger\SCITLogger( $this->mozyProAccount->account->id);

        $this->logger->info("Unprovision function for user group - " . $this->groupId);
        if ($this->groupId != null && $this->mozyProAccount->accountType != 'CR') {
            $this->getMozyConfiguration();
            $this->fillDataForDeleteUserGroup();
            try {
                $this->logger->info("Unprovision group data send to API: " . print_r($this->paramsData,true));
                $result = makeSoapCall($this->paramsConn, $this->paramsData);
                $this->logger->info("Unprovision group - " . print_r($result,true));
            } catch (Exception $fault) {
                $this->logger->info("Error deleting userGroups\n\t");
                // throw new Exception("Error while creating  group".$fault->getMessage());
            } catch (SoapFault $fault) {
                
            }
            try {
                \APS\Request::getController()->setResourceId($this->mozyProAccount->link->id); // impersonate
                $resAccount = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->mozyProAccount->link->id), 0);
                $listUsers = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->mozyProAccount->link->id . '/mozyProAccountUser?eq(user_group_id,' . $this->groupId . ')'), 0);

                foreach ($listUsers as $userRes) {
                    $res = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $userRes->aps->id), 0);
                    $res->user_group_id = $resAccount->user_group_id;
                    $newObj = $this->objectToObject($res, "mozyProAccountUser");
                    \APS\Request::getController()->updateResource($newObj);
                }
                $this->logger->info("Unprovision for group: correct");
            } catch (Exception $excpt) {
                
            }
        }
    }

    function fillDataForUserGroup() {
        //part for creating the partner
        \APS\Request::getController()->setResourceId($this->mozyProAccount->link->id);
        $resListjson = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->mozyProAccount->link->id), 0);
        \APS\Request::getController()->setResourceId(null);

        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->cred->ws_prefix . 'user_group' . $this->cred->ws_sufix;
        $this->paramsConn['methodName'] = "Create";

        $this->paramsData = array(
            'api_key' => $this->cred->api_key,
            'partner_id' => $this->mozyProAccount->partnerId,
            'name' => $this->name,
            'default_quotas' => null,
            'default_for_partner' => false,
            'external_id' => $this->mozyProAccount->subscription->subscriptionId
        );
    }

    function fillDataForDeleteUserGroup() {
        //part for creating the partner

        $resListjson = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->mozyProAccount->link->id), 0);

        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->cred->ws_prefix . 'user_group' . $this->cred->ws_sufix;
        $this->paramsConn['methodName'] = "Delete";

        $this->paramsData = array(
            'api_key' => $this->cred->api_key,
            'id' => $this->groupId,
            'search' => null
        );
    }

    function fillDataForUpdateUserGroup() {
        //part for creating the partner
        \APS\Request::getController()->setResourceId($this->mozyProAccount->link->id);
        $resListjson = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->mozyProAccount->link->id), 0);
        \APS\Request::getController()->setResourceId(null);

        $this->paramsConn = array();
        $this->paramsConn['wsdl'] = $this->cred->ws_prefix . 'user_group' . $this->cred->ws_sufix;
        $this->paramsConn['methodName'] = "Update";

        $this->paramsData = array(
            'api_key' => $this->cred->api_key,
            'id' => $this->groupId,
            'search' => null,
            'name ' => $this->name
        );
    }

    private function getMozyConfiguration() {
        if ($this->mozyProAccount->mozyPro->api_key == null) {
            $this->getMozyConf();
        } else {
            $this->cred = new stdClass();
            $this->cred->api_key = $this->mozyProAccount->mozyPro->api_key;
            $this->cred->root_partner_id = $this->mozyProAccount->mozyPro->root_partner_id;
            $this->cred->root_role_id = $this->mozyProAccount->mozyPro->root_role_id;
        }
        $this->cred->ws_prefix = $this->mozyProAccount->mozyPro->ws_prefix;
        $this->cred->ws_sufix = $this->mozyProAccount->mozyPro->ws_sufix;
    }

    private function getMozyConf() {
        $apsc = \APS\Request::getController();
        $apsId = $this->mozyProAccount->brandingId;
        $configuration = $apsId != null ? $apsc->getResource($apsId) : $this->mozyProAccount->mozyPro;
        $this->cred = new stdClass();
        $this->cred->api_key = $configuration->api_key;
        $this->cred->root_partner_id = $configuration->root_partner_id;
        $this->cred->root_role_id = $configuration->root_role_id;
    }

    function objectToObject($instance, $className) {
        return unserialize(sprintf(
            'O:%d:"%s"%s', strlen($className), $className, strstr(strstr(serialize($instance), '"'), ':')
        ));
    }

}

?>
