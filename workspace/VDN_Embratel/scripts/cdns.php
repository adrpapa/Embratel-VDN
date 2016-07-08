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
		
		// CREATE CONTENT ORIGIN
		$origin = new ContentOrigin("origin-".$this->name,$this->origin_server,$this->origin_domain,$this->description);
		if ( !$origin->create() ) {
			\APS\LoggerRegistry::get()->info("cdns:provisioning() Error creating Content Origin: " . $origin->getMessage());
		}
		else {
			// CREATE DELIVERY SERVICE
			$ds = new DeliveryService("ds-".$this->name,$origin->getID(),$this->description);
			if ( !$ds->create() ) {
				\APS\LoggerRegistry::get()->info("cdns:provisioning() Error creating Delivery Service: " . $ds->getMessage());
			}
			
			$this->delivery_service_id = $ds->getID();
			$this->content_origin_id = $origin->getID();
		}
		
		\APS\LoggerRegistry::get()->info("<-- Fim Provisionando CDN.Delivery Service ID:".$this->delivery_service_id.".Content Origin ID:".$this->content_origin_id);
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
