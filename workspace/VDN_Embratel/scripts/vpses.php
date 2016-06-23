<?php

 if (!defined('APS_DEVELOPMENT_MODE')) define ('APS_DEVELOPMENT_MODE', 'on');

require_once "aps/2/runtime.php";
require_once "elemental_api/live.php";
require_once "elemental_api/deltaOutputTemplate.php";
require_once "elemental_api/deltaInput.php";

// Definition of type structures

class CPU {
	/**
	 * @type("integer")
	 * @title("Number of CPUs")
	 * @description("Number of CPU cores")
	 */
	public $number;
}

class OS {
	/**
	 * @type("string")
	 * @title("OS Name")
	 * @description("Operating System Name")
	 */
	public $name;

	/**
	 * @type("string")
	 * @title("OS Version")
	 * @description("Operating System version")
	 */
	public $version;
}

class Hardware {
	/**
	 * @type("integer")
	 * @title("RAM Size")
	 * @description("RAM size in GB")
	 */
	public $memory;

	/**
	 * @type("integer")
	 * @title("Disk Space")
	 * @description("Disk space in GB")
	 */
	public $diskspace;

	/**
	 * @type("CPU")
	 * @title("CPU")
	 * @description("Server CPU parameters")
	 */
	public $CPU;
}

class Platform {
	/**
	 * @type("string")
	 * @title("Architecture")
	 * @description("Platform architecture")
	 */
	public $arch;

	/**
	 * @type("OS")
	 * @title("OS Parameters")
	 * @description("Parameters of operating system")
	 */
	public $OS;
}


// Main class
/**
 * @type("http://embratel.com.br/app/VDN_Embratel/vps/1.0")
 * @implements("http://aps-standard.org/types/core/resource/1.0")
 */
class vps extends \APS\ResourceBase {
	
	// Relation with the management context
	/**
	 * @link("http://embratel.com.br/app/VDN_Embratel/context/1.0")
	 * @required
	 */
	public $context;

	// VPS properties
	
	/**
	 * @type("string")
	 * @title("name")
	 * @description("Server Name")
	 */
	public $name;
	
	/**
	 * @type("string")
	 * @title("Description")
	 * @description("Server Description")
	 */
	public $description;
	
	/**
	 * @type("string")
	 * @title("state")
	 * @description("Server State")
	 */
	public $state;
	
	// VPS complex properties (structures) - defined as classes above
	
	/**
	 * @type("Hardware")
	 * @title("Hardware")
	 * @description("Server Hardware")
	 */
	public $hardware;
	
	/**
	 * @type("Platform")
	 * @title("Platform")
	 * @description("OS Platform")
	 */
	public $platform;

 
    #############################################################################################################################################
    ## Definition of the functions that will respond to the different CRUD operations
    #############################################################################################################################################

    public function provision() {
        $logger = \APS\LoggerRegistry::get();
        $logger->setLogFile("logs/vpses.log");
    	$logger->debug("Iniciando provisionamento");
        \APS\LoggerRegistry::get()->debug("Iniciando provisionamento de canal ".$this->aps->id);
        $clientid = sprintf("Client_%06d",$this->context->account->id);
        $event = LiveEvent::newStandardLiveEvent( $this->aps->id, $clientid );
        \APS\LoggerRegistry::get()->debug("live_event_id:" . $event->id );
        \APS\LoggerRegistry::get()->debug("live_event_name:" . $event->name );
        \APS\LoggerRegistry::get()->debug("state:" . $event->status );
        \APS\LoggerRegistry::get()->debug("input_URI:" . $event->inputURI );
        \APS\LoggerRegistry::get()->debug("delta_port:" . $event->udpPort );
        \APS\LoggerRegistry::get()->debug("live_node:" . $event->live_node );
        \APS\LoggerRegistry::get()->debug("inputFilterID:" . $event->inputFilterID );
        
        
        //     	\APS\LoggerRegistry::get()->error(var_dump($this->context->account ) );
//     	 $event = LiveEvent::newPremiumLiveEvent( $this->name, $this->context->account->clientID);
//         \APS\LoggerRegistry::get()->error(var_dump($event) );
    }

    public function configure($new) {
			\APS\LoggerRegistry::get()->info ("Estado atual do canal: ".$this->state);
			\APS\LoggerRegistry::get()->info ("Novo estado atual do canal: ".$new->state);
    }

		public function retrieve(){
    		\APS\LoggerRegistry::get()->trace("Entrando na função retrieve");
			\APS\LoggerRegistry::get()->debug("Entrando na função retrieve");
			\APS\LoggerRegistry::get()->info ("Entrando na função retrieve");
			\APS\LoggerRegistry::get()->error("Entrando na função retrieve");
			\APS\LoggerRegistry::get()->fatal("Entrando na função retrieve");

		}

		public function upgrade(){
    		\APS\LoggerRegistry::get()->trace("Entrando na função upgrade");
			\APS\LoggerRegistry::get()->debug("Entrando na função upgrade");
			\APS\LoggerRegistry::get()->info ("Entrando na função upgrade");
			\APS\LoggerRegistry::get()->error("Entrando na função upgrade");
			\APS\LoggerRegistry::get()->fatal("Entrando na função upgrade");

		}

		public function unprovision(){
    		\APS\LoggerRegistry::get()->trace("Entrando na função unprovision");
			\APS\LoggerRegistry::get()->debug("Entrando na função unprovision");
			\APS\LoggerRegistry::get()->info ("Entrando na função unprovision");
			\APS\LoggerRegistry::get()->error("Entrando na função unprovision");
			\APS\LoggerRegistry::get()->fatal("Entrando na função unprovision");

		}

}
?>
