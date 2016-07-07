<?php

if (!defined('APS_DEVELOPMENT_MODE')) define ('APS_DEVELOPMENT_MODE', 'on');

require_once "aps/2/runtime.php";

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
	 * @title("Content Origin Server(FQDN or IP)")
	 * @description("Content Origin Server(FQDN or IP) for video ingestion")
	 * @required
	 */
	public $origin_server;	

	/**
	 * @type(string)
	 * @title("Origin Domain")
	 * @description("Content Origin Domain")
	 * @required
	 */
	public $origin_domain;	
	
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
	
#############################################################################################################################################
## Definition of the functions that will respond to the different CRUD operations
#############################################################################################################################################

	public function provision() { 
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/cdns.log");
		\APS\LoggerRegistry::get()->info("Iniciando provisionamento do CDN... ".$this->aps->id);
		\APS\LoggerRegistry::get()->info("<-- Fim Provisionando CDN");
    }

    public function configure($new) {

    }

	public function retrieve(){

	}

    public function upgrade(){

	}

    public function unprovision(){

	}
}
?>
