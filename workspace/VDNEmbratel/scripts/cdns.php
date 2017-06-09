<?php

require_once "loader.php";

require_once "elemental_api/deliveryService.php";
require_once "elemental_api/contentOrigin.php";
require_once "elemental_api/deliveryServiceGenSettings.php";
require_once "elemental_api/fileMgmt.php";
require_once "utils/splunk.php";

require_once "embratel/common/parallels/Parallels.php";

/**
* Class context
* @type("http://embratel.com.br/app/VDNEmbratel/cdn/1.1")
* @implements("http://aps-standard.org/types/core/resource/1.0")
* @access(referrer,true)
*/
class cdn extends \APS\ResourceBase {
	/**
	 * @link("http://embratel.com.br/app/VDNEmbratel/context/2.1")
	 * @required
	 */
	public $context;

	/**
	 * @type(string)
	 * @title("Name")
	 * @description("CDN Name")
	 * @required
	 */
	public $name;
	/*
	   Cliente_vod_http
	 */
	/**
	 * @type(string)
	 * @title("Description")
	 * @description("CDN Description")
	 */
	public $description;
	/*
	   Cliente_vod_http
	 */

	/**
	 * @type(string)
	 * @title("Alias")
	 * @description("CDN Alias-only characters and numbers are allowed")
	 * @required
	 */
	public $alias;

	/**
	 * @type(string)
	 * @title("Content Origin Server(FQDN or IP)")
	 * @description("Content Origin Server(FQDN or IP) for video ingestion")
	 * @required
	 */
	public $origin_server;
	/*
	   fqdn apontando para o Delta
	   Cliente_xxx.201.31.12.36
	 */
	/**
	 * @type(string)
	 * @title("Origin Path")
	 * @description("Content Path for video ingestion")
	 * @required
	 */
	public $origin_path;
	/*
	   Restante do caminho para o delta sem barra inicial
	   out/u/
	 */

	/**
	 * @type(boolean)
	 * @title("HTTPS")
	 * @description("Turn on HTTPS feature for output")
	 */
	public $https;
	/*
	   true ou false
	 */

	/**
	 * @type(boolean)
	 * @title("HTTPS")
	 * @description("Turn on HTTPS feature for input")
	 */
	public $https_in;
	/*
	   true ou false
	 */

	/**
	 * @type(boolean)
	 * @title("Live")
	 * @description("Turn on Live Delivery Service")
	 */
	public $live;


	/*******************************************
	 * READ ONLY
	 *******************************************/

	/**
	 * @type(string)
	 * @title("Delivery Service ID")
	 * @description("CDMS Delivery Service")
	 * @readonly
	 */
	public $delivery_service_id;

	/**
	 * @type(string)
	 * @title("Origin ID")
	 * @description("CDMS Content Origin")
	 * @readonly
	 */
	public $content_origin_id;

	/**
	 * @type(string)
	 * @title("Delivery Service General Settings")
	 * @description("CDMS Delivery Service General Settings")
	 * @readonly
	 */
	public $delivery_service_gen_settings_id;

	/**
	 * @type(string)
	 * @title("URL Rewrite Rule File")
	 * @description("CDMS URL Rewrite Rule File")
	 * @readonly
	 */
	public $rule_url_rwr_file_id;

	/**
	 * @type(string)
	 * @title("Origin Domain")
	 * @description("Content Origin Domain")
	 * @readonly
	 */
	public $origin_domain;

	/**
	 * @type(string)
	 * @title("Delivery Service Name")
	 * @description("Delivery Service Name")
	 * @readonly
	 */
	public $delivery_service_name;	

	/*****************************************************
	 **************** METRIC PROPERTIES ******************
	 *****************************************************/

	/**
	 * @type("number")
	 * @title("Traffic HTTP actual usage")
	 * @description("Traffic HTTP actual usage")
	 */
	public $httpTrafficActualUsage;

	/**
	 * @type("number")
	 * @title("Traffic HTTPS actual usage")
	 * @description("Traffic HTTPS actual usage")
	 */
	public $http_s_TrafficActualUsage;	

	/**
	 * @type("string")
	 * @title("Timestamp of last result from splunk")
	 * @description("Timestamp of last result from splunk")
	 */
	public $newestSplunkData;

#############################################################################################################################################
## Definition of the functions that will respond to the different CRUD operations
#############################################################################################################################################

	public function tostring() {
		$retval  = "context:$this->context->aps->id";
		$retval  = " name:$this->name";
		$retval .= " description:$this->description";
		$retval .= " alias:$this->alias";
		$retval .= " origin_server:$this->origin_server";
		$retval .= " origin_path:$this->origin_path";
		$retval .= " https:$this->https";
		$retval .= " https_in:$this->https_in";
		$retval .= " live:$this->live";
		$retval .= " delivery_service_id:$this->delivery_service_id";
		$retval .= " content_origin_id:$this->content_origin_id";
		$retval .= " delivery_service_gen_settings_id:$this->delivery_service_gen_settings_id";
		$retval .= " rule_url_rwr_file_id:$this->rule_url_rwr_file_id";
		$retval .= " origin_domain:$this->origin_domain";
		$retval .= " delivery_service_name:$this->delivery_service_name";	
		$retval .= " httpTrafficActualUsage:$this->httpTrafficActualUsage";
		$retval .= " http_s_TrafficActualUsage:$this->http_s_TrafficActualUsage";	
		$retval .= " newestSplunkData:$this->newestSplunkData";
		return $retval;
	}
	
	public function provision() { 
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/" . __CLASS__ . "_" . date("Ymd") . ".log");
		$logger->debug("[".__METHOD__. '] >>');
//		echo "************ CDN Object *********";print_r($this);
		ConfigConsts::loadConstants($this->context);
		$logger->info("Iniciando provisionamento do CDN... ".$this->aps->id);

		$custom_name   = $this->alias . "-" . getClientID($this->context);
		$custom_domain = $this->alias . "." . getClientID($this->context);
		$origin_domain = $custom_domain . "." . ConfigConsts::$CDMS_DOMAIN;
		$ds_name       = "ds-".$custom_name;
		$this->httpTrafficActualUsage    = 0;
		$this->http_s_TrafficActualUsage = 0;
		$logger->info("--> ORIGIN DOMAIN: " . $origin_domain);

		$origin=null;
		$ds=null;
		$dsgs=null;
		$rule=null;
		$userError = "Ocorreu um erro no provisionamento do CDN";

		// CREATE CONTENT ORIGIN
		$origin = new ContentOrigin("co-".$custom_name,$this->origin_server,$origin_domain,$this->description);
		if ( !$origin->create() ) {
			$logger->info("cdns:provisioning() Error creating Content Origin: " . $origin->getMessage());
			throw new \Rest\RestException( 500, $userError." - Content Origin", $origin->getMessage());
		}
		// CREATE DELIVERY SERVICE
		try {
			$logger->info("--> Creating DS");
			$ds = $this->createDeliveryService($origin, $ds_name);
			$logger->info("<-- End Creating DS");		
		} catch (Exception $fault) {
			$logger->info("Error creating DS " . $fault->getMessage());
			$this->unprovision();
			throw new \Rest\RestException( 500, $userError." - Delivery Service", $fault->getMessage());
		}

		// CREATE DELIVERY SERVICE GENERAL SETTINGS
		try {
			$logger->info("--> Creating General Settings DS");
			$dsgs = $this->createDeliveryServiceGenSettings($origin,$ds);
			$logger->info("<-- End General Settings DS");
		} catch (Exception $fault) {
			$logger->info("Error creating General Settings DS " . $fault->getMessage());
			throw new \Rest\RestException( 500, $userError." - Delivery Service Settings", $fault->getMessage());
		}

		// ASSIGN RULE TO DELIVERY SERVICE
		try {
			$logger->info("--> Assign Rule");
			$rule = $this->asssignRule($origin,$ds,$dsgs,$custom_name,$origin_domain);
			$logger->info("<-- End Rule");
		} catch (Exception $fault) {
			$logger->info("Error assign Rule " . $fault->getMessage());
			$this->unprovision();
			throw new \Rest\RestException( 500, $userError." - Assign Rule", $fault->getMessage());
		}

		// SUCCESS: UPDATE APS RESOURCES
		//*****************************************
		if ( !is_null($origin) ) $this->content_origin_id = $origin->getID();
		if ( !is_null($ds) ) {
			$this->delivery_service_id   = $ds->getID();
			$this->delivery_service_name = $ds_name;
		}
		if ( !is_null($dsgs) )   $this->delivery_service_gen_settings_id = $dsgs->getID();
		if ( !is_null($rule) )   $this->rule_url_rwr_file_id = $rule->getID();
		$this->origin_domain = $origin_domain;
		//*****************************************

		$this->logActivity(__METHOD__, true, "Recurso Incluído");
		$return = $this->notifyDeliveryServiceCreation();
		$logger->info("<-- Fim Provisionando CDN.Delivery Service ID:".$this->delivery_service_id.".Content Origin ID:".$this->content_origin_id);
		$logger->debug("[".__METHOD__. '] <<');
	}

	public function configure($new) {
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/" . __CLASS__ . "_" . date("Ymd") . ".log");
		$logger->debug("[".__METHOD__. '] >>');
		ConfigConsts::loadConstants($this->context);
		$logger->info("Iniciando updating do CDN... ".$this->aps->id);    	 
		$resource_before = \APS\TypeLibrary::newResourceByTypeId(
				"http://embratel.com.br/app/VDNEmbratel/cdn/1.1");
		$resource_before->_copy($this);
		$this->_copy($new);

		$custom_name   = $new->alias . "-" . getClientID($this->context);
		$custom_domain = $new->alias . "." . getClientID($this->context);
		$origin_domain = $custom_domain . "." . ConfigConsts::$CDMS_DOMAIN;    	
		$ds_name       = "ds-".$custom_name;

		$logger->info("New Domain:".$origin_domain);
		$this->origin_domain = $origin_domain;

		if ( !is_null($this->content_origin_id) ) {
			$origin = new ContentOrigin("co-".$custom_name,$new->origin_server,$origin_domain,$new->description);
			if ( !$origin->update($this->content_origin_id) ) {
				$logger->info("cdns:provisioning() Error updating Content Origin: " . $origin->getMessage());
				throw new \Exception("Can't update content origin:" . $origin->getMessage(), 501);    		
			}
		}

		if ( !is_null($this->delivery_service_id) ) {
			$ds = new DeliveryService($ds_name,$this->content_origin_id,$new->description);
			if ( !$ds->update($this->delivery_service_id) ) {
				$logger->info("cdns:provisioning() Error updating Delivery Service: " . $ds->getMessage());
				throw new \Exception("Can't update delivery service:" . $ds->getMessage(), 502);    		
			}
			$this->delivery_service_name = $ds_name;
			$dsgs = new DeliveryServiceGenSettings();
			$dsgs->Bitrate = ConfigConsts::$CDMS_MAX_BITRATE_PER_SESSION;
			$dsgs->OsProtocol=( $this->https ? "1":"0");
			$dsgs->StreamingProtocol=($this->https ? "1":"0");
			$dsgs->update($this->delivery_service_id);
		}

		if ( !is_null($this->rule_url_rwr_file_id) ) {
			$rule = new FileMgmt("20",$custom_name . "-url-rwr-rule","upload");
			if( !$rule->updateUrlRewriteRule($this->rule_url_rwr_file_id,$origin_domain,$new->origin_path) ) {
				$logger->info("cdns:provisioning() Error updating rule to Delivery Service: " . $rule->getMessage());
				throw new \Exception("Can't update rule to delivery service:" . $rule->getMessage(), 505);
			}
		}
		

		$this->logActivity(__METHOD__, true, "Recurso Alterado", $resource_before);
		$logger->info("Fim updating do CDN... ".$this->aps->id);
		$logger->debug("[".__METHOD__. '] <<');
	}

	public function logActivity($method, $ok, $notes, $resource_before=null) {
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/" . __CLASS__ . "_" . date("Ymd") . ".log");
		$logger->debug("[".__METHOD__. '] >>');
		ConfigConsts::loadConstants($this->context);
		$activity = \APS\TypeLibrary::newResourceByTypeId(
				"http://embratel.com.br/app/VDNEmbratel/activity/1.0");
		if( $ok == false ){
			$activity->result = "ERROR";
		} else {
			$activity->result = "OK";
		}
		$activity->resource_name = $this->name;
		$activity->usuer_login = "faltapreencher";
		$activity->user_name = "Falta preencher";
		$activity->operation_timestamp;
		$activity->operation_type = $method;
		$activity->resource_type = __CLASS__;
		$activity->resource_after = $this->tostring();
		if( isset($resource_before) ) {
			$activity->resource_before = $resource_before->tostring();
		} else {
			$activity->resource_before = "N/A";
		}
		$activity->notes = $notes;

		$apsc = \APS\Request::getController();
		$apsc2 = $apsc->impersonate($this);
		$context = $apsc2->getResource($this->context->aps->id);
		$apsc2->linkResource($context, 'activities', $activity);
		$logger->debug("[".__METHOD__. '] <<');
	}

	public function retrieve(){
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/" . __CLASS__ . "_" . date("Ymd") . ".log");
		$logger->debug("[".__METHOD__. '] >>');
		$logger->debug("[".__METHOD__. '] <<');
	}

	public function upgrade(){
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/" . __CLASS__ . "_" . date("Ymd") . ".log");
		$logger->debug("[".__METHOD__. '] >>');
		$this->logActivity(__METHOD__, true, "Upgrade de Recurso");
		$logger->debug("[".__METHOD__. '] <<');
	}

	public function unprovision(){
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/" . __CLASS__ . "_" . date("Ymd") . ".log");
		$logger->debug("[".__METHOD__. '] >>');
		$logger->info("Iniciando des-provisionamento do CDN... ".$this->aps->id);
		ConfigConsts::loadConstants($this->context);

		if ( !is_null($this->delivery_service_gen_settings_id) ) {
			$dsgs = new DeliveryServiceGenSettings();
			$dsgs->delete( $this->delivery_service_gen_settings_id );
		}

		if ( !is_null($this->delivery_service_id) ) {
			$ds = new DeliveryService();
			$ds->delete( $this->delivery_service_id );
		}

		if ( !is_null($this->content_origin_id) ) {
			$origin = new ContentOrigin();
			$origin->delete( $this->content_origin_id );
		}

		if ( !is_null($this->rule_url_rwr_file_id) ) {
			$rule = new FileMgmt();
			$rule->delete( $this->rule_url_rwr_file_id );    	
		}

		$this->logActivity(__METHOD__, true, "Desprovisionamento de Recurso");
		$logger->info("<-- Fim DES-Provisionando CDN");    	    	
		$logger->debug("[".__METHOD__. '] <<');
	}

	/*********************************************************************
	 * Custom Functions
	 * Functions to create delivery service, content origin and rule file
	 *********************************************************************
	 */
	function createDeliveryService($origin,$custom_name) {
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/" . __CLASS__ . "_" . date("Ymd") . ".log");
		$logger->debug("[".__METHOD__. '] >>');
		ConfigConsts::loadConstants($this->context);
		$ds = new DeliveryService($custom_name,$origin->getID(),$this->description);
		/* INICIO Alteração sugeridas pelo Daniel Pinkas da Cisco */
		$ds->live = "false";	// ($this->live ? "true":"false");
		/* FIM Alteração sugeridas pelo Daniel Pinkas da Cisco */
		if ( !$ds->create() ) {
			$logger->info("cdns:provisioning() Error creating Delivery Service: " . $ds->getMessage());
			// Rollback
			$logger->info("cdns:provisioning() Rollbacking: [".$origin->getID()."]");
			if ( !is_null($origin) ) $origin->delete( $origin->getID() );
			// 
			throw new \Exception("Can't create delivery service:" . $ds->getMessage(), 502);
		}

		// ASSIGN SEs
		if ( !$ds->assignSEs($ds->getID()) ) {
			$logger->info("cdns:provisioning() Error assigning SEs to Delivery Service: " . $ds->getMessage());
			// Rollback
			$logger->info("cdns:provisioning() Rollbacking: [".$ds->getID()."].[".$origin->getID()."]");
			if ( !is_null($ds) ) $ds->delete( $ds->getID() );
			if ( !is_null($origin) ) $origin->delete( $origin->getID() );			
			//
			throw new \Exception("Can't assign service engines to delivery service:" . $ds->getMessage(), 504);
		}		

		$this->logActivity(__METHOD__, true, "Criado Delivery Service");
		$logger->debug("[".__METHOD__. '] <<');
		return $ds;
	}

	function createDeliveryServiceGenSettings($origin,$ds) {
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/" . __CLASS__ . "_" . date("Ymd") . ".log");
		$logger->debug("[".__METHOD__. '] >>');
//		echo "************ CDN Object *********";print_r($this);
		ConfigConsts::loadConstants($this->context);
		// CUSTOMIZE PROTOCOL: HTTP or HTTPS

		// if ( $this->live ) return;	// Precisa confirmar isso....

		$dsgs = new DeliveryServiceGenSettings($ds->getID(), ($this->https ? "https" : "http"),
			ConfigConsts::$CDMS_MAX_BITRATE_PER_SESSION );

		/* INICIO Alterações sugeridas pelo Daniel Pinkas da Cisco */
		// $dsgs->HttpExtAllow = "true";
		// $dsgs->HttpExt      = urlencode("asf none nsc wma wmv nsclog");
		$dsgs->Bitrate = ConfigConsts::$CDMS_MAX_BITRATE_PER_SESSION;
		/* FIM Alterações sugeridas pelo Daniel Pinkas da Cisco */

		if ( !$dsgs->create() ) {
			$logger->info("cdns:provisioning() Error customizing delivery service: " . $dsgs->getMessage());
			// Rollback
			$logger->info("cdns:provisioning() Rollbacking: [".$ds->getID()."].[".$origin->getID()."]");
			if ( !is_null($ds) ) $ds->delete( $ds->getID() );
			if ( !is_null($origin) ) $origin->delete( $origin->getID() );
			//			
			throw new \Exception("Can't customize delivery service:" . $dsgs->getMessage(), 503);
		}			

		$this->logActivity(__METHOD__, true, "Criado Delivery Service General Settings");
		$logger->debug("[".__METHOD__. '] <<');
		return $dsgs;
	}

	function asssignRule($origin,$ds,$dsgs,$custom_name,$origin_domain) {
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/" . __CLASS__ . "_" . date("Ymd") . ".log");
		$logger->debug("[".__METHOD__. '] >>');
//		echo "************ CDN Object *********";print_r($this);
		ConfigConsts::loadConstants($this->context);
		$rule = new FileMgmt("20",$custom_name . "-url-rwr-rule","upload");

		if( !$rule->createUrlRewriteRule($origin_domain,$this->origin_path) ) {
			$logger->info("cdns:provisioning() Error creating rule to Delivery Service: " . $rule->getMessage());
			// Rollback
			$logger->info("cdns:provisioning() Rollbacking: [".$dsgs->getID()."].[".$ds->getID()."].[".$origin->getID()."]");
			if ( !is_null($dsgs) ) $dsgs->delete( $dsgs->getID() );
			if ( !is_null($ds) ) $ds->delete( $ds->getID() );
			if ( !is_null($origin) ) $origin->delete( $origin->getID() );
			//
			throw new \Exception("Can't create rule to delivery service:" . $rule->getMessage(), 505);
		}		

		if ( !$ds->applyRuleFile( $rule->getID() ) ) {
			$logger->info("cdns:provisioning() Error applying rule to Delivery Service: " . $ds->getMessage());
			// Rollback
			$logger->info("cdns:provisioning() Rollbacking: [".$rule->getID()."][".$dsgs->getID()."].[".$ds->getID()."].[".$origin->getID()."]");
			if ( !is_null($dsgs) ) $dsgs->delete( $dsgs->getID() );
			if ( !is_null($ds) ) $ds->delete( $ds->getID() );
			if ( !is_null($origin) ) $origin->delete( $origin->getID() );
			if ( !is_null($rule) ) $rule->delete( $rule->getID() );			
			//
			throw new \Exception("Can't apply rule to delivery service:" . $ds->getMessage(), 506);
		}		

		$this->logActivity(__METHOD__, true, "Criado Rule File e associado ao Delivery Service");
		$logger->debug("[".__METHOD__. '] <<');
		return $rule;
	}

	/**
	 * Update traffic usage
	 * @verb(GET)
	 * @path("/updateResourceUsage")
	 */
	public function updateResourceUsage () {
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/" . __CLASS__ . "_" . date("Ymd") . ".log");
		$logger->debug("[".__METHOD__. '] >>');
//		echo "************ CDN Object *********";print_r($this);
		ConfigConsts::loadConstants($this->context);
		$usage = array();
		$clientID = getClientID($this->context);
		$dsName = "ds-" . $this->alias . "-" . $clientID;
		$logger->info("Updating resource usage for delivery service: ".$dsName);
		$splunkStats = SplunkStats::getBilling($this->context, $dsName, $this->newestSplunkData);
		$this->newestSplunkData = $splunkStats->lastResultTime;
		//$logger->info(var_dump($splunkStats));

## Calculate the resource usage properties and save counters to return to caller
		$usage['lastResultTime'] = $splunkStats->lastResultTime;
		if( $this->https ) {
			$this->http_s_TrafficActualUsage += $splunkStats->gigaTransfered;
			$usage['httpTrafficActualUsage'] = 0;
			$usage['httpsTrafficActualUsage'] = $splunkStats->gigaTransfered;
		} else {
			$this->httpTrafficActualUsage += $splunkStats->gigaTransfered;
			$usage['httpTrafficActualUsage'] = $splunkStats->gigaTransfered;
			$usage['httpsTrafficActualUsage'] = 0;
		}

## Save resource usage in the APS controller
		$apsc = \APS\Request::getController();
		$apsc->updateResource($this);
		$logger->debug("[".__METHOD__. '] <<');
		return $usage;
	}


	public function notifyDeliveryServiceCreation(){
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/" . __CLASS__ . "_" . date("Ymd") . ".log");
		$logger->debug("[".__METHOD__. '] >>');
		ConfigConsts::loadConstants($this->context);
		if( ConfigConsts::$PBA_API == "" ||
			ConfigConsts::$EMAIL_TEMPLATE_NAME == '' ) {
				$logger->debug("Notification Skipped configure PBA Api Gate and Email template");
				return;
			}
		$accountID=getClientID($this->context);
		$apsc = \APS\Request::getController();
		$apsc2 = $apsc->impersonate($this);
		$users = $apsc2->getResources('implementing(http://aps-standard.org/types/core/user/1.0)');
		$userID = $users[0]->memberId;

		$placeholders = array();
		$placeholders['Service_Delivery_Name'] = $this->delivery_service_name;
		$placeholders['usuario'] = $users[0]->login;
		$placeholders['PORTAL_ANALYTICS_URL'] = ConfigConsts::$PORTAL_ANALYTICS_URL;
		$parallels = new Parallels($logger);

		$return = $parallels->sendNotification(ConfigConsts::$EMAIL_TEMPLATE_NAME,
			 	$accountID, $userID, $placeholders);
		if($return == false) {
			echo $return;
		}
		$this->logActivity(__METHOD__, $return, $parallels->message);
		$logger->debug("[".__METHOD__. '] <<');
		return $return;
	}


	/**
	 * Enable Service engines for CDN
	 * @verb(GET)
	 * @path("/assignServiceEngines")
	 */
	public function assignServiceEngines(){
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/" . __CLASS__ . "_" . date("Ymd") . ".log");
		$logger->debug("[".__METHOD__. '] >>');
//		echo "************ CDN Object *********";print_r($this);
		ConfigConsts::loadConstants($this->context);
		if ( !is_null($this->delivery_service_id) ) {
			$ds = new DeliveryService();
			if( ! $ds->assignSEs( $this->delivery_service_id ) ) {
				throw new \Exception("Can't assign service engines to delivery service:" . $ds->getMessage(), 504);
			}
		}
		return;  
	}

	/**
	 * Enable Service engines for CDN
	 * @verb(GET)
	 * @path("/unassignServiceEngines")
	 */
	public function unassignServiceEngines(){
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/" . __CLASS__ . "_" . date("Ymd") . ".log");
		$logger->debug("[".__METHOD__. '] >>');
//		echo "************ CDN Object *********";print_r($this);
		ConfigConsts::loadConstants($this);
		if ( !is_null($this->delivery_service_id) ) {
			$ds = new DeliveryService();
			if( ! $ds->unassignSEs( $this->delivery_service_id ) ) {
				throw new \Exception("Can't unassign service engines from delivery service:" . $ds->getMessage(), 504);
			}
		}
		$logger->debug("[".__METHOD__. '] <<');
		return;
	}
}
?>
