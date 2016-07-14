<?php

if (!defined('APS_DEVELOPMENT_MODE')) define ('APS_DEVELOPMENT_MODE', 'on');

require_once "aps/2/runtime.php";
require_once "elemental_api/deliveryService.php";
require_once "elemental_api/contentOrigin.php";
require_once "elemental_api/deliveryServiceGenSettings.php";
require_once "elemental_api/fileMgmt.php";

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
	 * @pattern("^[a-zA-Z0-9_]*$")
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
	
#############################################################################################################################################
## Definition of the functions that will respond to the different CRUD operations
#############################################################################################################################################

	public function provision() { 
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/cdns.log");
		\APS\LoggerRegistry::get()->info("Iniciando provisionamento do CDN... ".$this->aps->id);
		
		$custom_name   = $this->alias . "-" . $this->context->subscription->id;
		$custom_domain = $this->alias . "." . $this->context->subscription->id;
		$origin_domain = $custom_domain . "." . ConfigConsts::CDMS_DOMAIN;
		
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
			$this->logger->info("--> Creating DS");
			$ds = $this->createDeliveryService($origin,$custom_name);
			$this->logger->info("<-- End Creating DS");		
		} catch (Exception $fault) {
			$this->logger->info("Error creating DS");
			throw new Exception($fault->getMessage());
		}		

		// CREATE DELIVERY SERVICE GENERAL SETTINGS
		try {
			$this->logger->info("--> Creating General Settings DS");
			$dsgs = $this->createDeliveryServiceGenSettings($origin,$ds);
			$this->logger->info("<-- End General Settings DS");
		} catch (Exception $fault) {
			$this->logger->info("Error creating General Settings DS");
			throw new Exception($fault->getMessage());
		}
		
		// ASSIGN RULE TO DELIVERY SERVICE
		try {
			$this->logger->info("--> Assign Rule");
			$rule = $this->asssignRule($origin,$ds,$dsgs,$custom_name,$origin_domain);
			$this->logger->info("<-- End Rule");
		} catch (Exception $fault) {
			$this->logger->info("Error assign Rule");
			throw new Exception($fault->getMessage());
		}		
		
		// SUCCESS: UPDATE APS RESOURCES
		//*****************************************
		$this->content_origin_id = $origin->getID();
		$this->delivery_service_id = $ds->getID();
		$this->delivery_service_gen_settings_id = $dsgs->getID();
		$this->rule_url_rwr_file_id = $rule->getID();
		$this->origin_domain = $origin_domain;
		//*****************************************
		
		\APS\LoggerRegistry::get()->info("<-- Fim Provisionando CDN.Delivery Service ID:".$this->delivery_service_id.".Content Origin ID:".$this->content_origin_id);
    }

    public function configure($new) {

    }

	public function retrieve(){

	}

    public function upgrade(){

	}

    public function unprovision(){
    	$logger = \APS\LoggerRegistry::get();
    	$logger->setLogFile("logs/cdns.log");
    	\APS\LoggerRegistry::get()->info("Iniciando des-provisionamento do CDN... ".$this->aps->id);
    	
    	$rule = new FileMgmt();
    	$rule->delete( $this->rule_url_rwr_file_id );
    	
    	$dsgs = new DeliveryServiceGenSettings();
    	$dsgs->delete( $this->delivery_service_gen_settings_id );
    	
    	$ds = new DeliveryService();
    	$ds->delete( $this->delivery_service_id );
    	
    	$origin = new ContentOrigin();
    	$origin->delete( $this->content_origin_id );
    	
    	\APS\LoggerRegistry::get()->info("<-- Fim DES-Provisionando CDN");    	    	
	}
	
	/*********************************************************************
	 * Custom Functions
	 * Functions to create delivery service, content origin and rule file
	 *********************************************************************
	 */
	function createDeliveryService($origin,$custom_name) {
		$ds = new DeliveryService("ds-".$custom_name,$origin->getID(),$this->description);
		$ds->live = ($this->live ? "true":"false");
		if ( !$ds->create() ) {
			\APS\LoggerRegistry::get()->info("cdns:provisioning() Error creating Delivery Service: " . $ds->getMessage());
			// Rollback
			if ( !is_null($origin) ) $origin->delete( $origin->getID() );
			// 
			throw new \Exception("Can't create delivery service:" . $ds->getMessage(), 502);
		}

		// ASSIGN SEs
		if ( !$ds->assignSEs() ) {
			\APS\LoggerRegistry::get()->info("cdns:provisioning() Error assigning SEs to Delivery Service: " . $ds->getMessage());
			// Rollback
			if ( !is_null($ds) ) $ds->delete( $ds->getID() );
			if ( !is_null($origin) ) $origin->delete( $origin->getID() );			
			//
			throw new \Exception("Can't assign service engines to delivery service:" . $ds->getMessage(), 504);
		}		
		
		return $ds;
	}
	
	function createDeliveryServiceGenSettings($origin,$ds) {
		// CUSTOMIZE PROTOCOL: HTTP or HTTPS
		$dsgs = new DeliveryServiceGenSettings($ds->getID(), ($this->https ? "https" : "http") );
		if ( !$dsgs->create() ) {
			\APS\LoggerRegistry::get()->info("cdns:provisioning() Error customizing delivery service: " . $dsgs->getMessage());
			// Rollback
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
			if ( !is_null($dsgs) ) $dsgs->delete( $dsgs->getID() );
			if ( !is_null($ds) ) $ds->delete( $ds->getID() );
			if ( !is_null($origin) ) $origin->delete( $origin->getID() );
			//
			throw new \Exception("Can't create rule to delivery service:" . $rule->getMessage(), 505);
		}		
		
		if ( !$ds->applyRuleFile( $rule->getID() ) ) {
			\APS\LoggerRegistry::get()->info("cdns:provisioning() Error applying rule to Delivery Service: " . $ds->getMessage());
			// Rollback
			if ( !is_null($rule) ) $rule->delete( $rule->getID() );
			if ( !is_null($dsgs) ) $dsgs->delete( $dsgs->getID() );
			if ( !is_null($ds) ) $ds->delete( $ds->getID() );
			if ( !is_null($origin) ) $origin->delete( $origin->getID() );
			//
			throw new \Exception("Can't apply rule to delivery service:" . $ds->getMessage(), 506);
		}		
		
		return $rule;
	}
}
?>
