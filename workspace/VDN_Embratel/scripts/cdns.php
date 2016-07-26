<?php

if (!defined('APS_DEVELOPMENT_MODE')) define ('APS_DEVELOPMENT_MODE', 'on');

require_once "aps/2/runtime.php";
require_once "elemental_api/deliveryService.php";
require_once "elemental_api/contentOrigin.php";
require_once "elemental_api/deliveryServiceGenSettings.php";
require_once "elemental_api/fileMgmt.php";
require_once "elemental_api/splunk.php";

/**
 * @type("http://embratel.com.br/app/VDN_Embratel/cdn/1.0")
 * @implements("http://aps-standard.org/types/core/resource/1.0")
 */
class cdn extends \APS\ResourceBase {
	
	// Relation with the management context
	
	/**
	 * @link("http://embratel.com.br/app/VDN_Embratel/context/1.0")
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

	/**
	 * @type(string)
	 * @title("Description")
	 * @description("CDN Description")
	 */
	public $description;

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

	/**
	 * @type(string)
	 * @title("Origin Path")
	 * @description("Content Path for video ingestion")
	 * @required
	 */
	public $origin_path;
	
	/**
	 * @type(boolean)
	 * @title("HTTPS")
	 * @description("Turn on HTTPS feature for live")
	 */
	public $https;
	
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

	public function provision() { 
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/cdns.log");
		\APS\LoggerRegistry::get()->info("Iniciando provisionamento do CDN... ".$this->aps->id);
		
		$custom_name   = $this->alias . "-" . $this->context->account->id;
		$custom_domain = $this->alias . "." . $this->context->account->id;
		$origin_domain = $custom_domain . "." . ConfigConsts::CDMS_DOMAIN;
		$ds_name       = "ds-".$custom_name;
		$this->httpTrafficActualUsage    = 0;
		$this->http_s_TrafficActualUsage = 0;
		
		\APS\LoggerRegistry::get()->info("--> ORIGIN DOMAIN: " . $origin_domain);

		$origin=null;
		$ds=null;
		$dsgs=null;
		$rule=null;
		
		// CREATE CONTENT ORIGIN
		$origin = new ContentOrigin("co-".$custom_name,$this->origin_server,$origin_domain,$this->description);
		if ( !$origin->create() ) {
			\APS\LoggerRegistry::get()->info("cdns:provisioning() Error creating Content Origin: " . $origin->getMessage());
			throw new \Exception("Can't create content origin:" . $origin->getMessage(), 501);
		}
		// CREATE DELIVERY SERVICE
		try {
			\APS\LoggerRegistry::get()->info("--> Creating DS");
			$ds = $this->createDeliveryService($origin,$ds_name);
			\APS\LoggerRegistry::get()->info("<-- End Creating DS");		
		} catch (Exception $fault) {
			\APS\LoggerRegistry::get()->info("Error creating DS");
			throw new Exception($fault->getMessage());
		}		

		// CREATE DELIVERY SERVICE GENERAL SETTINGS
		try {
			\APS\LoggerRegistry::get()->info("--> Creating General Settings DS");
			$dsgs = $this->createDeliveryServiceGenSettings($origin,$ds);
			\APS\LoggerRegistry::get()->info("<-- End General Settings DS");
		} catch (Exception $fault) {
			\APS\LoggerRegistry::get()->info("Error creating General Settings DS");
			throw new Exception($fault->getMessage());
		}
		
		// ASSIGN RULE TO DELIVERY SERVICE
		try {
			\APS\LoggerRegistry::get()->info("--> Assign Rule");
			$rule = $this->asssignRule($origin,$ds,$dsgs,$custom_name,$origin_domain);
			\APS\LoggerRegistry::get()->info("<-- End Rule");
		} catch (Exception $fault) {
			\APS\LoggerRegistry::get()->info("Error assign Rule");
			throw new Exception($fault->getMessage());
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
		
		\APS\LoggerRegistry::get()->info("<-- Fim Provisionando CDN.Delivery Service ID:".$this->delivery_service_id.".Content Origin ID:".$this->content_origin_id);
    }

    public function configure($new) {
    	$logger = \APS\LoggerRegistry::get();
    	$logger->setLogFile("logs/cdns.log");
    	\APS\LoggerRegistry::get()->info("Iniciando updating do CDN... ".$this->aps->id);    	 
 
    	$custom_name   = $new->alias . "-" . $this->context->account->id;
    	$custom_domain = $new->alias . "." . $this->context->account->id;
    	$origin_domain = $custom_domain . "." . ConfigConsts::CDMS_DOMAIN;    	
    	
    	\APS\LoggerRegistry::get()->info("New Domain:".$origin_domain);
    	
    	if ( !is_null($this->content_origin_id) ) {
	    	$origin = new ContentOrigin("co-".$custom_name,$new->origin_server,$origin_domain,$new->description);
	    	if ( !$origin->update($this->content_origin_id) ) {
	    		\APS\LoggerRegistry::get()->info("cdns:provisioning() Error updating Content Origin: " . $origin->getMessage());
	    		throw new \Exception("Can't update content origin:" . $origin->getMessage(), 501);    		
	    	}
    	}
    	
    	if ( !is_null($this->delivery_service_id) ) {
	    	$ds = new DeliveryService("ds-".$custom_name,$this->content_origin_id,$new->description);
	    	if ( !$ds->update($this->delivery_service_id) ) {
				\APS\LoggerRegistry::get()->info("cdns:provisioning() Error updating Delivery Service: " . $ds->getMessage());
				throw new \Exception("Can't update delivery service:" . $ds->getMessage(), 502);    		
	    	}
    	}
    	
    	if ( !is_null($this->rule_url_rwr_file_id) ) {
	    	$rule = new FileMgmt("20",$custom_name . "-url-rwr-rule","upload");
	    	if( !$rule->updateUrlRewriteRule($this->rule_url_rwr_file_id,$origin_domain,$new->origin_path) ) {
				\APS\LoggerRegistry::get()->info("cdns:provisioning() Error updating rule to Delivery Service: " . $rule->getMessage());
				throw new \Exception("Can't update rule to delivery service:" . $rule->getMessage(), 505);
	    	}
    	}
    	
    	\APS\LoggerRegistry::get()->info("Fim updating do CDN... ".$this->aps->id);
    }

	public function retrieve(){

	}

    public function upgrade(){

	}

    public function unprovision(){
    	$logger = \APS\LoggerRegistry::get();
    	$logger->setLogFile("logs/cdns.log");
    	\APS\LoggerRegistry::get()->info("Iniciando des-provisionamento do CDN... ".$this->aps->id);
    	 	
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
    	
    	\APS\LoggerRegistry::get()->info("<-- Fim DES-Provisionando CDN");    	    	
	}
	
	/*********************************************************************
	 * Custom Functions
	 * Functions to create delivery service, content origin and rule file
	 *********************************************************************
	 */
	function createDeliveryService($origin,$custom_name) {
		$ds = new DeliveryService($custom_name,$origin->getID(),$this->description);
		$ds->live = ($this->live ? "true":"false");
		if ( !$ds->create() ) {
			\APS\LoggerRegistry::get()->info("cdns:provisioning() Error creating Delivery Service: " . $ds->getMessage());
			// Rollback
			\APS\LoggerRegistry::get()->info("cdns:provisioning() Rollbacking: [".$origin->getID()."]");
			if ( !is_null($origin) ) $origin->delete( $origin->getID() );
			// 
			throw new \Exception("Can't create delivery service:" . $ds->getMessage(), 502);
		}

		// ASSIGN SEs
		if ( !$ds->assignSEs() ) {
			\APS\LoggerRegistry::get()->info("cdns:provisioning() Error assigning SEs to Delivery Service: " . $ds->getMessage());
			// Rollback
			\APS\LoggerRegistry::get()->info("cdns:provisioning() Rollbacking: [".$ds->getID()."].[".$origin->getID()."]");
			if ( !is_null($ds) ) $ds->delete( $ds->getID() );
			if ( !is_null($origin) ) $origin->delete( $origin->getID() );			
			//
			throw new \Exception("Can't assign service engines to delivery service:" . $ds->getMessage(), 504);
		}		
		
		return $ds;
	}
	
	function createDeliveryServiceGenSettings($origin,$ds) {
		// CUSTOMIZE PROTOCOL: HTTP or HTTPS
		
		if ( $this->live ) return;	// Precisa confirmar isso....
		
		$dsgs = new DeliveryServiceGenSettings($ds->getID(), ($this->https ? "https" : "http") );
		
		$dsgs->HttpExtAllow = "true";
		$dsgs->HttpExt      = urlencode("asf none nsc wma wmv nsclog");
		
		if ( !$dsgs->create() ) {
			\APS\LoggerRegistry::get()->info("cdns:provisioning() Error customizing delivery service: " . $dsgs->getMessage());
			// Rollback
			\APS\LoggerRegistry::get()->info("cdns:provisioning() Rollbacking: [".$ds->getID()."].[".$origin->getID()."]");
			if ( !is_null($ds) ) $ds->delete( $ds->getID() );
			if ( !is_null($origin) ) $origin->delete( $origin->getID() );
			//			
			throw new \Exception("Can't customize delivery service:" . $dsgs->getMessage(), 503);
		}			
		
		return $dsgs;
	}
	
	function asssignRule($origin,$ds,$dsgs,$custom_name,$origin_domain) {
		$rule = new FileMgmt("20",$custom_name . "-url-rwr-rule","upload");
		
		if( !$rule->createUrlRewriteRule($origin_domain,$this->origin_path) ) {
			\APS\LoggerRegistry::get()->info("cdns:provisioning() Error creating rule to Delivery Service: " . $rule->getMessage());
			// Rollback
			\APS\LoggerRegistry::get()->info("cdns:provisioning() Rollbacking: [".$dsgs->getID()."].[".$ds->getID()."].[".$origin->getID()."]");
			if ( !is_null($dsgs) ) $dsgs->delete( $dsgs->getID() );
			if ( !is_null($ds) ) $ds->delete( $ds->getID() );
			if ( !is_null($origin) ) $origin->delete( $origin->getID() );
			//
			throw new \Exception("Can't create rule to delivery service:" . $rule->getMessage(), 505);
		}		
		
		if ( !$ds->applyRuleFile( $rule->getID() ) ) {
			\APS\LoggerRegistry::get()->info("cdns:provisioning() Error applying rule to Delivery Service: " . $ds->getMessage());
			// Rollback
				\APS\LoggerRegistry::get()->info("cdns:provisioning() Rollbacking: [".$rule->getID()."][".$dsgs->getID()."].[".$ds->getID()."].[".$origin->getID()."]");
			if ( !is_null($dsgs) ) $dsgs->delete( $dsgs->getID() );
			if ( !is_null($ds) ) $ds->delete( $ds->getID() );
			if ( !is_null($origin) ) $origin->delete( $origin->getID() );
			if ( !is_null($rule) ) $rule->delete( $rule->getID() );			
			//
			throw new \Exception("Can't apply rule to delivery service:" . $ds->getMessage(), 506);
		}		
		
		return $rule;
	}
	
	/**
	 * Update traffic usage
	 * @verb(GET)
	 * @path("/updateResourceUsage")
	 */
	public function updateResourceUsage () {
		$logger = \APS\LoggerRegistry::get();
    	$logger->setLogFile("logs/cdns.log");
		$usage = array();
		$clientID = $this->context->account->id;
		$dsName = "ds-" . $this->alias . "-" . $clientID;
		$logger->info("Updating resource usage for delivery service: ".$dsName);
		$splunkStats = SplunkStats::getBilling($clientID, $dsName, $this->newestSplunkData);
		$this->newestSplunkData = $splunkStats->lastResultTime;
		$logger->info(var_dump($splunkStats));
		
		## Calculate the resource usage properties
		$this->httpTrafficActualUsage += $splunkStats->gigaTransfered;
		# Save counters to return to caller
		$usage['httpTrafficActualUsage'] = $splunkStats->gigaTransfered;
		
		if( $this->https ) {
			$this->http_s_TrafficActualUsage += $splunkStats->gigaTransfered;
			$usage['httpsTrafficActualUsage'] = $splunkStats->gigaTransfered;
		} else {
			$usage['httpsTrafficActualUsage'] = 0;
		}

		## Save resource usage in the APS controller
		$apsc = \APS\Request::getController();
		$apsc->updateResource($this);
		
		return $usage;
	}
}
?>
