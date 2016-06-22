<?php
require_once "framework.php";
require_once "aps/2/runtime.php";
require_once "utils.php";
require_once "mozyProAccount.php";
require_once "mozyProAccountUserLicense.php";

/**
 * Class mozyProAccountUser
 * @type("http://www.mozy.com/mozyProAPS2/mozyProAccountUser/1.1")
 * @implements("http://aps-standard.org/types/core/resource/1.0")
 */
class mozyProAccountUser extends \APS\ResourceBase {

	/**
	 * @link("http://www.mozy.com/mozyProAPS2/mozyProAccount/1.5")
	 * @required
	 */
	public $mozyProAccount;

	/**
	 * @link("http://www.mozy.com/mozyProAPS2/mozyProAccountUserLicense/1.0[]")
	 * @access(referrer, true)
	 */
	public $mozyProAccountUserLicense;

	/**
	 * @link("http://aps-standard.org/types/core/service-user/1.0")
	 * @required
	 */
	public $user;

	/**
	 * @type(string)
	 * @title("user_group_id")
	 */
	public $user_group_id;

	/**
	 * @type(string)
	 * @title("user_group_id")
	 */
	public $user_group_id_OLD;

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
	public $displayName;

	/**
	 * @type(string)
	 * @title("email")
	 */
	public $login;

	/**
	 * @type(string)
	 * @title("userId")
	 */
	public $userId;

	/**
	 * @type(string)
	 * @title("userstatus")
	 */
	public $userstatus;

	/**
	 * @type(string)
	 * @title("desktopQuotaSum")
	 */
	public $desktopQuotaSum;

	/**
	 * @type(integer)
	 * @title("desktopLicSum")
	 */
	public $desktopLicSum;

	/**
	 * @type(string)
	 * @title("serverQuotaSum")
	 */
	public $serverQuotaSum;

	/**
	 * @type(integer)
	 * @title("serverLicSum")
	 */
	public $serverLicSum;

	/**
	 * @type(integer)
	 * @title("enableSync")
	 */
	public $enableSync;

	/**
	 * @type(integer)
	 * @title("syncQuota")
	 */
	public $syncQuota;

	/**
	 * @type(integer)
	 * @title("syncQuotaUsed")
	 */
	public $syncQuotaUsed;

	/**
	 * @type(string)
	 * @title("mozyUserId")
	 */
	public $mozyUserId;

	/**
	 * @type(integer)
	 * @title("syncQuotaUsedMozy")
	 */
	public $syncQuotaUsedMozy;

	/**
	 * @type(integer)
	 * @title("syncLastBackUp")
	 */
	public $syncLastBackUp;

	/**
	 * @type(integer)
	 * @title("swTypeLicense")
	 */
	public $swTypeLicense;

	private $paramsConn;
	private $paramsData;
	private $cred;
	private $keystring;
    protected $logger;


	#############################################################################################################################################
	## Definition of the functions that will respond to the different CRUD operations
	#############################################################################################################################################

	public function provision() {
        $this->logger = new SCITLogger\SCITLogger($this->mozyProAccount->account->id);
		$this->logger->info("MozyProAccountuser provision starting :\n\t");
		$this->getMozyConfiguration();
		$this->fillDataForUserCreation();
		$this->enableSync = 0;
		$this->syncQuotaUsedMozy = 0;

		$this->logger->info("Data send to API to create a user:\n\t" . print_r($this->paramsData, true));
		try {
			$this->mozyUserId = makeSoapCall($this->paramsConn, $this->paramsData);
			$this->logger->info("User created in Mozy - UserId:\n\t" . $this->mozyUserId);
		} catch (Exception $fault) {
			$this->logger->info("Error while creating Mozy User, :\n\t" . $fault->getMessage());
			throw new Exception($fault->getMessage());
		}

		///Start create resource License If you come of Service User Wizard
        if ($this->swTypeLicense == 1 || $this->swTypeLicense == 2) {
			if (!is_numeric($this->userId)) {
				$this->userId = $this->user->userId;
			}
            $this->logger->info("MozyProAccountuser provision Async");

            throw new \Rest\Accepted($this, "Creating Mozy service ...", 30);
		}
		///Finish create resource License If you come of Service User Wizard

		$sub = new \APS\EventSubscription(\APS\EventSubscription::Changed, "onUserChange");

		$sub->source->type = "http://aps-standard.org/types/core/service-user/1.0";
		$apsc = \APS\Request::getController();
		$subscriptionnotifications = $apsc->subscribe($this, $sub);

		return array('user_group_id' => $this->user_group_id, 'password' => "", 'displayName' => $this->displayName, 'login' => $this->login, 'mozyUserId' => $this->mozyUserId);
	}

    public function provisionAsync() {

        $this->logger = new SCITLogger\SCITLogger($this->mozyProAccount->account->id);

        $this->logger->info("MozyProAccountuser provision Async starting");

        if ($this->swTypeLicense == 1 || $this->swTypeLicense == 2) {

            $apsc = \APS\Request::getController();
            $apsc2 = $apsc->impersonate($this->aps->id);

            $linkMozyProAccountUser = new \APS\Link();
            $linkMozyProAccountUser->id = $this->aps->id;
            $linkMozyProAccountUser->href = "/aps/2/resources/" . $this->aps->id;
            $linkMozyProAccountUser->link = "strong";
            $linkMozyProAccountUser->name = "mozyProAccountUser";

            $newLicense = \APS\TypeLibrary::newResourceByTypeId("http://www.mozy.com/mozyProAPS2/mozyProAccountUserLicense/1.0");
            $newLicense->mozyProAccountUser = new APS\ResourceProxy($linkMozyProAccountUser);
            $newLicense->aps->links[] = $linkMozyProAccountUser;

            $newLicense->licenseNum = "1";
            $newLicense->user_group_id = $this->user_group_id;

            if ($this->swTypeLicense == 1) {
                $newLicense->quota = $this->desktopQuotaSum;
                $newLicense->licenseType = "Desktop";
            }

            if ($this->swTypeLicense == 2) {
                $newLicense->quota = $this->serverQuotaSum;
                $newLicense->licenseType = "Server";
            }

            $prov = $apsc2->registerResource($newLicense);
            $this->mozyProAccountUserLicense = $prov->aps->id;
            // Create licenses in Mozy
            $this->updateQuota();

            $newItemLic = $apsc2->getResource($prov->aps->id);
            $newItemLic->keyString = $this->keystring;
            $apsc2->updateResource($newItemLic);

        }
    }

	public function configure($new) {

		//update user with or without change of user group
        $this->logger = new SCITLogger\SCITLogger($this->mozyProAccount->account->id);
		$this->logger->info("MozyProAccountUser configure starting -------------------- :\n\t");
		$this->getMozyConfiguration();
		$this->fillDataForUserUpdate($new);

		// Fix buy sync after the order without sync
		if ($this->syncQuota == 0) {
			$this->syncQuota = $this->mozyProAccount->syncDefaultQuota->limit;
			$new->syncQuota = $this->mozyProAccount->syncDefaultQuota->limit;
		}

		if ($this->syncQuota != $new->syncQuota ) {
			$this->syncQuota = $new->syncQuota;
            if($new->enableSync !=0)
			    $res = $this->checkMachineMozy($new->mozyUserId, 1);
		}

		if ($new->user_group_id != $this->user_group_id) {
			$apsc = \APS\Request::getController();
			$apsc2 = $apsc->impersonate($new);
			$resLicList = json_decode($apsc2->getIo()->sendRequest('GET', 'aps/2/resources/' . $new->aps->id . '/mozyProAccountUserLicense'), 0);
			//unassign
			$listRes = array();
			$desktopQuotaSummary = 0;
			$serverQuotaSummary = 0;

			foreach ($resLicList as $itemLic) {
				if ($itemLic->machineId != "" && $itemLic->machineId != "") {
					continue;
				}

				if ($itemLic->licenseType == "Desktop") {
					$desktopQuotaSummary += $itemLic->quota;
				} else {
					$serverQuotaSummary += $itemLic->quota;
				}
			}
			if ($desktopQuotaSummary > 0) {
				$this->transferKey("Desktop", $desktopQuotaSummary, $this->user_group_id, $new->user_group_id);
				try {
					$this->logger->info("TRANSFER DESKTOP LICENSES BEGIN --------------------, :\n\t" . $desktopQuotaSummary . "--" . $this->user_group_id . "--" . $new->user_group_id);
					$result = makeSoapCall($this->paramsConn, $this->paramsData);
					$this->logger->info("TRANSFER DESKTOP LICENSES END --------------------, :\n\t");
				} catch (Exception $fault) {
					$this->fillDataForUserUpdate($this);
					throw new Exception("Error configure function for user" . $fault->getMessage());
				}
			}

			if ($serverQuotaSummary > 0) {
				$this->transferKey("Server", $serverQuotaSummary, $this->user_group_id, $new->user_group_id);
				try {
					$this->logger->info("TRANSFER SERVER LICENSES BEGIN --------------------, :\n\t" . $serverQuotaSummary . "" . $this->user_group_id . "" . $new->user_group_id);
					$result = makeSoapCall($this->paramsConn, $this->paramsData);
					$this->logger->info("TRANSFER SERVER LICENSES END --------------------, :\n\t" . print_R($result, true));
				} catch (Exception $fault) {
					$this->fillDataForUserUpdate($this);
					if ($desktopQuotaSummary > 0) {
						$this->transferKey("Desktop", $desktopQuotaSummary, $new->user_group_id, $this->user_group_id);
					}
					throw new Exception("Error configure function for user" . $fault->getMessage());
				}
			}

			//assign
			foreach ($resLicList as $itemLic) {
				$newItemLic = $apsc2->getResource($itemLic->aps->id);
				$newItemLic->user_group_id = $new->user_group_id;
				try {
					$apsc2->updateResource($newItemLic);
					$this->logger->info("OK UPDATE LIC--- " . print_r($newItemLic, true));
				} catch (Exception $ex) {
					$this->logger->info("ERROR update lic---" . print_r($ex->getMessage(), true));
					throw new Exception("ERROR update lic " . $ex->getMessage());
				}
			}
			$new->user_group_id_OLD = $new->user_group_id;

			try {
				$apsc2->updateResource($new);
				$this->logger->info("OK UPDATE resource in POA--- " . print_r($new, true));
			} catch (Exception $ex) {
				$this->logger->info("ERROR UPDATE resource in POA---" . print_r($ex->getMessage(), true));
				throw new Exception("ERROR UPDATE resource in POA " . $ex->getMessage());
			}
		}
	}

	public function unprovision() {
        $this->logger = new SCITLogger\SCITLogger( $this->mozyProAccount->account->id);
        $this->logger->info("Unprovision starting for user: " . $this->mozyUserId);
		$this->getMozyConfiguration();
		if ($this->mozyUserId != null) {
			try {
				$this->fillDataForDeleteUser();
			} catch (Exception $ex) {
				return;
			}
			try {
				$result = makeSoapCall($this->paramsConn, $this->paramsData);
				$this->syncQuotaUsedMozy = 0;
				$this->syncLastBackUp = "";
				$this->logger->info("Unprovision of Mozy user correct");
			} catch (Exception $fault) {
				$this->logger->info("Error Unprovision of Mozy user, :\n\t" . $fault->getMessage());
				throw new Exception($fault->getMessage());
			}

			$resAccount = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->mozyProAccount->aps->id), 0);

			$resAccount->desktopQuota->usage -= $this->desktopQuotaSum;
			$resAccount->desktopLicenseNum->usage -= $this->desktopLicSum;
			$resAccount->serverQuota->usage -= $this->serverQuotaSum;
			$resAccount->serverLicenseNum->usage -= $this->serverLicSum;

			$newresAccount = $this->objectToObject($resAccount, "mozyProAccount");

			\APS\Request::getController()->updateResource($newresAccount);

			$this->logger->info("Unprovision MozyProAccountUser finished succesfully");
		}
	}

	/**
	 * We define the operation on post event
	 * @verb(POST)
	 * @path("/onUsrChange")
	 * @param("http://aps-standard.org/types/core/resource/1.0#Notification",body)
	 */
	public function onUserChange($event) {
        $this->logger = new SCITLogger\SCITLogger($this->mozyProAccount->account->id);
        try {
			$apsc = \APS\Request::getController();
			$serviceuser = $apsc->getResource($event->source->id);
			if (($serviceuser->userId == $this->userId) && (($serviceuser->displayName != $this->displayName) || ($serviceuser->login != $this->login))) {
				$this->login = $serviceuser->login;
				$this->displayName = $serviceuser->displayName;
				$this->configure($this);
				$apsc->updateResource($this);
			}
		} catch (Exception $ex) {
			$this->logger->info("ERR :----- onUserEvent" . $ex->getMessage());
		}
		return;
	}

	/**
	 * @verb(GET)
	 * @path("/getLicenses")
	 * @return(string,text/json)
	 * @access(referrer, true)  // Authorize a service user (referrer) as the ‘owner’
	 */
	public function getLicenses() {
        $this->logger = new SCITLogger\SCITLogger($this->mozyProAccount->account->id);
        $this->logger->info(__METHOD__ . " " . $this->aps->id);

		return \APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->aps->id . '/mozyProAccountUserLicense');
	}

	#############################################################################################################################################
	## A tenant may be suspended, we may define de operations related with core type suspendable
	## in that case will be enable and disable
	#############################################################################################################################################

	/**
	 * We define operation for enable
	 * @verb(POST)
	 * @path("/enableuser")
	 * @param()
	 */
	function enable() {
        $this->logger = new SCITLogger\SCITLogger($this->mozyProAccount->account->id);
        $this->logger->info("Enable user: " . $this->userId);
		$this->getMozyConfiguration();
		$this->fillDataForEnableUser();
		try {
			$result = makeSoapCall($this->paramsConn, $this->paramsData);
			$this->logger->info("User correctly enabled in Mozy: " . $result);
		} catch (Exception $fault) {
			$this->logger->info("Error Enable user, :\n\t" . $fault->getMessage());
			throw new Exception("Error while Enable  user" . $this->userId);
		}
		$this->userstatus = "active";
		\APS\Request::getController()->updateResource($this);

		$this->logger->info("Resource enabled and well updated");
	}

	/**
	 * We define operation for disable
	 * @verb(POST)
	 * @path("/disableuser")
	 * @param()
	 */
	function suspend() {
        $this->logger = new SCITLogger\SCITLogger($this->mozyProAccount->account->id);
        $this->logger->info("Suspend function for user: " . $this->userId);
		$this->getMozyConfiguration();
		$this->fillDataForSuspendUser();
		try {
			$result = makeSoapCall($this->paramsConn, $this->paramsData);
			$this->logger->info("User has been suspended in Mozy: " . $result);
		} catch (Exception $fault) {
			$this->logger->info("Error suspend user, :\n\t" . $fault->getMessage());
			throw new Exception("Error while suspend  user" . $this->userId);
		}
		$this->userstatus = "suspended";
		\APS\Request::getController()->updateResource($this);

		$this->logger->info("Resource suspended and well updated");
	}

	/**
	 * We define operation for getting the clients, login url
	 * @verb(GET)
	 * @path("/getUserUrl")
	 * @param(string,query)
	 * @param(string,query)
	 */
	public function getUserUrl($title, $title2) {
        $this->logger = new SCITLogger\SCITLogger($this->mozyProAccount->account->id);
		$this->logger->info("A User is requesting url to access service" . $title . $title2);
		$this->getMozyConfiguration();
		$this->getUserUrlFill($title);
		try {
			$url = makeSoapCall($this->paramsConn, $this->paramsData);
			$this->logger->info("A User is requesting url to access service" . print_r($url, true));
		} catch (Exception $fault) {
			$this->logger->info("getUserUrl for mozyPro when adquiring access url error, :\n\t" . $fault->getMessage());
			throw new Exception("Error while getUserUrl  ");
		}

		return $url->results[0]->details[0]->value;
	}

	/**
	 * We define operation for getting the clients, login url
	 * @verb(GET)
	 * @path("/getInstructions")
	 */
	public function getInstructions() {
        $this->logger = new SCITLogger\SCITLogger($this->mozyProAccount->account->id);
		$this->logger->info("User requesting info");

		$apsc = \APS\Request::getController();
		$apsc2 = $apsc->impersonate($this->mozyProAccount);
		$tenant = $apsc2->getResource($this->mozyProAccount->link->id);

		$resources = $tenant->subscription->resources();

		$result = new stdClass();
		$this->logger->info("Instruction resources" . print_R($resources, true));
		foreach ($resources as $res) {

			if ("http://www.mozy.com/mozyProAPS2/mozyProConf/1.1" === $res->apsType AND isset($res->limit) AND $res->limit > 0) {
				$brand = $apsc->getResource($res->apsId);
				$result = '{"backup":"' . $brand->backupHelp . '","sync":"' . $brand->syncHelp . '"}';
				break;
			}
		}
		return $result;
	}

	#############################################################################################################################################
	## Support functions for this class
	#############################################################################################################################################

	function fillDataForUserCreation() {
		//part for creating the partner
		\APS\Request::getController()->setResourceId($this->mozyProAccount->link->id); // impersonate
		$resListjson = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->mozyProAccount->link->id), 0);

		// If the user is created by customer that buy to reseller, we introduce the user into the customer group
		$this->syncQuota = $this->mozyProAccount->syncDefaultQuota->limit;
		if ($this->mozyProAccount->accountType == "CR") {
			$this->getParentGroup();
			try {
				$mozyGroup = makeSoapCall($this->paramsConn, $this->paramsData);

				// We must avoid the reseller group id, and choose the otther
				$groupParent = $this->getGroup($mozyGroup, $resListjson->user_group_id);
				$this->logger->info("User added to group :\n\t" . print_R($groupParent, true));
				$this->user_group_id = $groupParent;
			} catch (Exception $fault) {
				$this->logger->info("User cannot be added to group" . $fault->getMessage());
				throw new Exception($fault->getMessage());
			}
		}
		\APS\Request::getController()->setResourceId(null); // end impersonate

		$this->password = \APS\generatePassword(12);

		$this->paramsConn = array();
		$this->paramsConn['wsdl'] = $this->mozyProAccount->mozyPro->ws_prefix . 'user' . $this->mozyProAccount->mozyPro->ws_sufix;
		$this->paramsConn['methodName'] = "Create";

		$this->paramsData = array(
			'api_key' => $this->cred->api_key,
			'user_group_id' => $this->user_group_id,
			'username' => $this->login,
			'password' => "",
			'full_name' => $this->displayName,
			'details' => null,
			'external_id' => $this->mozyProAccount->subscription->subscriptionId
		);
	}

	function fillDataForUserUpdate($serviceuser) {
		//part for creating the partner
		\APS\Request::getController()->setResourceId($this->mozyProAccount->link->id);
		$resListjson = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->mozyProAccount->link->id), 0);
		\APS\Request::getController()->setResourceId(null);

		$this->paramsConn = array();
		$this->paramsConn['wsdl'] = $this->mozyProAccount->mozyPro->ws_prefix . 'user' . $this->mozyProAccount->mozyPro->ws_sufix;
		$this->paramsConn['methodName'] = "Update";

		$currentSyncState = $this->enableSync;
		$sync = null;
        //If the sync status for the user has changed
		if ($serviceuser->enableSync != $this->enableSync) {
            //A user can enable sync if the partner and the group has sync enabled, has an active machine and enough desktop quota
			if ($this->checkIfCanSync($this->mozyProAccount->syncDefaultQuota->limit, $serviceuser->mozyUserId)) {
				$sync = True;
				$this->syncInGroup();
				$this->enableSync = 1;

				if ($serviceuser->enableSync == null || $serviceuser->enableSync == "" || $serviceuser->enableSync == false || $serviceuser->enableSync == False) {
					$sync = False;
					$this->enableSync = 0;
				}
			}
		} else {
			if (!$this->checkIfCanSync($this->mozyProAccount->syncDefaultQuota->limit, $serviceuser->mozyUserId)) {
				$sync = False;
				$this->enableSync = 0;
			}
		}

		$this->paramsData = array(
			'api_key' => $this->cred->api_key,
			'id' => $serviceuser->mozyUserId,
			'search' => null,
			'user_group_id' => $serviceuser->user_group_id,
			'username' => $serviceuser->login,
			'password' => "",
			'full_name' => $serviceuser->displayName,
			'details' => null,
			'external_id' => null,
			'enable_sync' => $sync
		);

		try {
			$result = makeSoapCall($this->paramsConn, $this->paramsData);
			$this->logger->info("configure function for user: " . print_r($result, true));
		} catch (Exception $fault) {
			$this->logger->info("Error configure function for user: \n\t" . $fault->getMessage());
			throw new Exception($fault->getMessage());
		}

		// Change Request: Sends an sync activation email on sync enable
		if ($currentSyncState != $serviceuser->enableSync && $serviceuser->enableSync == 1) {
			$this->sendSyncEmail($serviceuser);
		}

	}

	function fillDataForDeleteUser() {
		//part for creating the partner
		\APS\Request::getController()->setResourceId($this->mozyProAccount->link->id); // impersonate
		$resListjson = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->mozyProAccount->link->id), 0);
		\APS\Request::getController()->setResourceId(null); // end impersonate

		$this->paramsConn = array();
		$this->paramsConn['wsdl'] = $this->mozyProAccount->mozyPro->ws_prefix . 'user' . $this->mozyProAccount->mozyPro->ws_sufix;
		$this->paramsConn['methodName'] = "Delete";

		$this->paramsData = array(
			'api_key' => $this->cred->api_key,
			'id' => $this->mozyUserId
		);
	}

	function fillDataForEnableUser() {
		//part for creating the partner

		\APS\Request::getController()->setResourceId($this->mozyProAccount->link->id);
		$resListjson = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->mozyProAccount->link->id), 0);
		\APS\Request::getController()->setResourceId(null);

		$this->paramsConn = array();
		$this->paramsConn['wsdl'] = $this->mozyProAccount->mozyPro->ws_prefix . 'user' . $this->mozyProAccount->mozyPro->ws_sufix;
		$this->paramsConn['methodName'] = "Update";

		$this->paramsData = array(
			'api_key' => $this->cred->api_key,
			'id' => $this->mozyUserId,
			'search' => null,
			'user_group_id' => $this->user_group_id,
			'username' => $this->login,
			'password' => "",
			'full_name' => $this->displayName,
			'details' => array('UserDetailStruct' => array('key' => "status", 'value' => "active"))
		);
	}

	function fillDataForSuspendUser() {
		//part for creating the partner

		\APS\Request::getController()->setResourceId($this->mozyProAccount->link->id);
		$resListjson = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->mozyProAccount->link->id), 0);
		\APS\Request::getController()->setResourceId(null);

		$this->paramsConn = array();
		$this->paramsConn['wsdl'] = $this->mozyProAccount->mozyPro->ws_prefix . 'user' . $this->mozyProAccount->mozyPro->ws_sufix;
		$this->paramsConn['methodName'] = "Update";

		$this->paramsData = array(
			'api_key' => $this->cred->api_key,
			'id' => $this->mozyUserId,
			'search' => null,
			'user_group_id' => $this->user_group_id,
			'username' => $this->login,
			'password' => "",
			'full_name' => $this->displayName,
			'details' => array('UserDetailStruct' => array('key' => "status", 'value' => "suspended"))
		);
	}

	function transferKey($licenseType, $quota, $origGroup, $destGroup) {

		$this->paramsConn = array();
		$this->paramsConn['wsdl'] = $this->mozyProAccount->mozyPro->ws_prefix . 'resource' . $this->mozyProAccount->mozyPro->ws_sufix;
		$this->paramsConn['methodName'] = "Transfer";

		$this->paramsData = array(
			'api_key' => $this->cred->api_key,
			'source_partner_id' => null,
			'source_user_group_id' => $origGroup,
			'target_partner_id' => null,
			'target_user_group_id' => $destGroup,
			'license_type' => $licenseType,
			'licenses' => null,
			'quota' => $quota
		);
	}

	private function getParentGroup() {
		$this->paramsConn = array();
		$this->paramsConn['wsdl'] = $this->mozyProAccount->mozyPro->ws_prefix . 'user_group' . $this->mozyProAccount->mozyPro->ws_sufix;
		$this->paramsConn['methodName'] = "Get";

		$this->paramsData = array(
			'api_key' => $this->cred->api_key,
			'id' => null,
			'search' => array('partner_id' => $this->mozyProAccount->partnerId)
		);
	}

	private function getGroup($total, $avoidGroup) {
		// local vars
		$resp = null;
		for ($i = 0; $i < count($total->results); $i++) {
			if ($total->results[$i]->id == $avoidGroup) {
				$resp = $total->results[$i]->id;
				break;
			}
		}
		return $resp;
	}

	private function getMozyConfiguration() {
        $this->cred = new stdClass();
        $this->cred->ws_prefix = $this->mozyProAccount->mozyPro->ws_prefix;
		$this->cred->ws_sufix = $this->mozyProAccount->mozyPro->ws_sufix;
		if ($this->mozyProAccount->mozyPro->api_key == null) {
			$this->getMozyConf();
		} else {
            $this->cred->api_key = $this->mozyProAccount->mozyPro->api_key;
			$this->cred->root_partner_id = $this->mozyProAccount->mozyPro->root_partner_id;
			$this->cred->root_role_id = $this->mozyProAccount->mozyPro->root_role_id;
		}
	}

	private function getMozyConf() {
		$apsc = \APS\Request::getController();
        $apsId = $this->mozyProAccount->brandingId;
		$configuration = $apsId != null ? $apsc->getResource($apsId) : $this->mozyProAccount->mozyPro;
		$this->cred->api_key = $configuration->api_key;
		$this->cred->root_partner_id = $configuration->root_partner_id;
		$this->cred->root_role_id = $configuration->root_role_id;
	}

	private function getAllLicensesMozy() {
		$paramsConn['wsdl'] = $this->mozyProAccount->mozyPro->ws_prefix . 'resource' . $this->mozyProAccount->mozyPro->ws_sufix;

		$paramsConn['methodName'] = "GetLicenses";
		$paramsData = array(
			'api_key' => $this->cred->api_key,
			'keystring' => null,
			'search' => array(
				'user_group_id' => $this->user_group_id, 'assigned_email_address' => $this->login,
			));

		$res = null;
		try {
			$res = makeSoapCall($paramsConn, $paramsData);
		} catch (Exception $ex) {
			$this->logger->info("User has not got licenses ");
		}
		return $res;
	}

	private function syncInGroup() {

        // Array struct for sync quota

        if ($this->syncQuota == 0) {
            $syncQuota = $this->mozyProAccount->syncDefaultQuota->limit;
        }
        else{
            $syncQuota = $this->syncQuota;
        }
        $default_quotas[] = new stdClass();
        $default_quotas[0]->type = 'Sync';
        $default_quotas[0]->quota = $syncQuota;

        $paramsConn['wsdl'] = $this->mozyProAccount->mozyPro->ws_prefix . 'user_group' . $this->mozyProAccount->mozyPro->ws_sufix;
        $paramsConn['methodName'] = "Update";
		$paramsData = array(
			'api_key' => $this->cred->api_key,
			'id' => $this->user_group_id,
			'search' => null,
			'name' => null,
			'default_quotas' => $default_quotas,
			'default_for_partner' => null,
			'external_id' => null,
			'details' => null,
			'enable_sync' => true
		);

		$res = true;
		try {
            $this->logger->info("Update user_group, params: ".print_r($paramsData,true));
            makeSoapCall($paramsConn, $paramsData);
		} catch (Exception $ex) {
			$this->logger->info("User group cannot enable sync ");
			$res = false;
		}
		return $res;
	}

	/**
	 * On Sync Enable, sends a mail to the user
	 * @param object $serviceuser
	 * @throws Exception Error sending the mail
	 */
	function sendSyncEmail($serviceuser) {
        $this->logger = new SCITLogger\SCITLogger($this->mozyProAccount->account->id);
        // Prepares the SOAP call with parameters
		$this->paramsConn = array();
		$this->paramsConn['wsdl'] = $this->mozyProAccount->mozyPro->ws_prefix . 'email' . $this->mozyProAccount->mozyPro->ws_sufix;
		$this->paramsConn['methodName'] = "Deliver";
		//
		$this->paramsData = array(
			'api_key' => $this->cred->api_key,
			'template' => 'user_enable_stash',
			'user_id' => $serviceuser->mozyUserId,
			'admin_id' => null,
			'email' => $serviceuser->login,
			'language' => null
		);

		try {
			makeSoapCall($this->paramsConn, $this->paramsData);
		} catch (Exception $ex) {
			$this->logger->info("Error sending the sync activation email: " . $ex->getMessage());
			throw new Exception($ex->getMessage());
		}
	}

	function objectToObject($instance, $className) {
		return unserialize(sprintf(
			'O:%d:"%s"%s', strlen($className), $className, strstr(strstr(serialize($instance), '"'), ':')
		));
	}

	function getUserUrlFill($title) {
		$this->logger->info("getUserURl for mozyProAccount has been called," . print_r($this, true));
		$this->paramsConn = array();
		$this->paramsConn['wsdl'] = $this->mozyProAccount->mozyPro->ws_prefix . 'user' . $this->mozyProAccount->mozyPro->ws_sufix;
		$this->paramsConn['methodName'] = "Get";

		$this->paramsData = array(
			'api_key' => $this->cred->api_key,
			'id' => $this->mozyUserId,
			'search' => array('user_group_id' => $this->user_group_id),
			'details' => array('item' => 'login_href')
		);
	}

	private function checkIfCanSync($syncQuota, $mozyId) {
		// Get Available desktop quota from subscription
		$availableDesktopQuota = 0;
		if (isset($this->mozyProAccount->desktopQuota->limit)) {
			$usedDesktopQuota = isset($this->mozyProAccount->desktopQuota->usage) ? $this->mozyProAccount->desktopQuota->usage : 0;
			$availableDesktopQuota = $this->mozyProAccount->desktopQuota->limit - $usedDesktopQuota;
		}
		// Check if there is enough quota for enable sync and the user has at least one license active (machine)
		if ($availableDesktopQuota >= $syncQuota) {
			$res = $this->checkMachineMozy($mozyId, 0);
			if ((int) $res > 0) {
				return true;
			}
			return false;
		} else {
			throw new \Exception("Sync quota cannot get over the total desktop quota ", 411);
		}
	}

	private function checkMachineMozy($mozyId, $type) {
		$paramsConn['wsdl'] = $this->mozyProAccount->mozyPro->ws_prefix . 'machine' . $this->mozyProAccount->mozyPro->ws_sufix;

		$paramsConn['methodName'] = "Get";
		if ($type == 0) {
			$paramsData = array(
				'api_key' => $this->cred->api_key,
				'id' => null,
				'search' => array('user_id' => $mozyId)
			);
		} else {
			$paramsData = array(
				'api_key' => $this->cred->api_key,
				'id' => null,
				'search' => array('user_id' => $mozyId, 'alias' => "Sync")
			);
		}

		$res = 0;
		try {
			$machine = makeSoapCall($paramsConn, $paramsData);
			if ($type == 1) {
				if ($machine->results[0]->quota_used_bytes == null || $machine->results[0]->quota_used_bytes == "") {
					$this->syncQuotaUsedMozy = 0;
				} else {
					$this->syncQuotaUsedMozy = $machine->results[0]->quota_used_bytes;
				}
				$this->syncLastBackUp = $machine->results[0]->last_backup_at;
				$this->updateMachineSync($machine->results[0]->user_id, $machine->results[0]->alias);
			}
			$res = 1;
		} catch (Exception $ex) {
			$this->logger->info("Error: " . print_R($ex->getMessage(), true));
			throw new \Exception("Machine does not exist ", 410);
		}

		return $res;
	}

	private function updateMachineSync($user_id, $alias) {
		$paramsConn['wsdl'] = $this->mozyProAccount->mozyPro->ws_prefix . 'machine' . $this->mozyProAccount->mozyPro->ws_sufix;

		$paramsConn['methodName'] = "Update";
		$paramsData = array(
			'api_key' => $this->cred->api_key,
			'id' => null,
			'search' => array('user_id' => $user_id, 'alias' => $alias),
			'quota' => $this->syncQuota,
		);

		$res = 0;
		try {
			$machine = makeSoapCall($paramsConn, $paramsData);
		} catch (Exception $ex) {
			throw new \Exception("Cannot update machine in Mozy ", 412);
		}
	}

	private function fillDataForGetFreeLicenses() {

		if (!isset($this->cred) || $this->cred == NULL) {
			$this->getMozyConfiguration();
		}

		if ($this->swTypeLicense == 1) {
			$typeLic = "Desktop";
		} else {
			$typeLic = "Server";
		}
		$paramsConn = array();
		$paramsConn['wsdl'] = $this->cred->ws_prefix . 'resource' . $this->cred->ws_sufix;
		$paramsConn['methodName'] = "GetLicenses";
		$paramsData = array(
			'api_key' => $this->cred->api_key,
			'keystring' => null,
			'search' => array(
				'status' => 'free',
				'license_type' => $typeLic,
				'user_group_id' => $this->user_group_id,
				'no_sub_partner' => null
			),
		);
        try {
            $this->logger->info("Data Send to GetLicenses API call: " . print_r($paramsData, true));
            $this->keystring = makeSoapCall($paramsConn, $paramsData);
        }catch (Exception $ex){
            $this->logger->error("Error: " . print_r($ex->getMessage(), true));
        }
	}

	private function fillDataForUserResourcesCreation($keystring, $assignlogin) {

		$paramsConn = array();
		$paramsConn['wsdl'] = $this->cred->ws_prefix . 'resource' . $this->cred->ws_sufix;
		$paramsConn['methodName'] = "UpdateLicenses";

		$paramsData = array(
			'api_key' => $this->cred->api_key,
			'keystring' => $keystring,
			'search' => null,
			'assigned_email_address' => $assignlogin,
			'quota_desired' => (($this->swTypeLicense == 1) ? $this->desktopQuotaSum : $this->serverQuotaSum),
			'external_id' => $this->mozyProAccount->subscription->subscriptionId,
			'license_type' => (($this->swTypeLicense == 1) ? "Desktop" : "Server"),
			'deliver_emails' => true,
			'expires_at' => null,
			'clear_expires_at' => null,
			'user_group_id' => null
		);
        $this->logger->info("Update Licenses data send to API call:\n\t" . print_r($paramsData,true));
        makeSoapCall($paramsConn, $paramsData);
	}

	private function provisionWithoutResUpdate($resUser) {
		$this->fillDataForGetFreeLicenses();
		$this->keystring = $this->keystring->results[0]->keystring;
		$this->fillDataForUserResourcesCreation($this->keystring, $resUser->login);
	}

	private function updateQuota() {
		$resAccount = json_decode(\APS\Request::getController()->getIo()->sendRequest('GET', '/aps/2/resources/' . $this->mozyProAccount->aps->id), 0);

		$this->provisionWithoutResUpdate($this);
		//update resources

		try {
			if ($this->swTypeLicense == 1) {
				$resAccount->desktopQuota->usage += $this->desktopQuotaSum;
				$resAccount->desktopLicenseNum->usage += 1;
			} else {

				$resAccount->serverQuota->usage += $this->serverQuotaSum;
				$resAccount->serverLicenseNum->usage += 1;
			}
			$newresAccount = $this->objectToObject($resAccount, "mozyProAccount");
			\APS\Request::getController()->updateResource($newresAccount);
		} catch (Exception $e) {}
	}

}

?>
