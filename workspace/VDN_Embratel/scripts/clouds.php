<?php

define('APS_DEVELOPMENT_MODE', true);
require "aps/2/runtime.php";

/**
 * Class cloud presents application and its global parameters
 * @type("http://embratel.com.br/app/VDN_Embratel/cloud/1.0")
 * @implements("http://aps-standard.org/types/core/application/1.0")
 */
class cloud extends APS\ResourceBase {
# Link to collection of contexts. Pay attention to [] brackets at the end of the @link line.
	/**
	 * @link("http://embratel.com.br/app/VDN_Embratel/context/1.0[]")
	 */
	public $contexts;

# Global connection settings will be configured by the provider
# Must be forwarded to the app end-point to set the connection with the external system
/**
* Constantes serão configuradas no arquivo config_constants.php
*/
//         /**
//         * @type(string)
//         * @title("Template Path")
//         * @description("Path for all elemental default templates ")
//         */
//         public $templates = $TEMPLATE_PATH = 'templates';
// 
//         /**
//         * @type(string)
//         * @title("Conductor Live hostname")
//         */
//         public $LIVE_CONDUCTOR_HOST = '201.31.12.4';
//         
//         /**
//         * @type(integer)
//         * @title("Live Standard Template ID")
//         * @description("Live event that will be base of channels for new Standard subscriptions")
//         */
//         public $LIVE_TEMPLATE_STANDARD = 13;
//         
//         /**
//         * @type(integer)
//         * @title("Live Premium Template ID")
//         * @description("Live event that will be base of channels for new  Premium subscriptions")
//         */
//         public $LIVE_TEMPLATE_PREMIUM = 13;
//         
//         /**
//         * @type(string)
//         * @title("Delta hostname")
//         */
//         public $DELTA_HOST = '201.31.12.36';
//         
//         /**
//         * @type(string)
//         * @title("Delta Port")
//         */
//         public $DELTA_PORT = '8080';
//         
//         /**
//         * @type(integer)
//         * @title("Delta Input Filter Template ID")
//         * @description("UDP Input filter that will be base for channels subscriptions")
//         */
//         public $DELTA_UDP_INPUT_FILTER_TEMPLATE = 274;
//         
//         /**
//         * @type(integer)
//         * @title("Delta Watch Folder Template ID")
//         * @description("Watch folder input filter that will be base for VOD subscriptions")
//         */
//         public $DELTA_WF_INPUT_FILTER_TEMPLATE = 42;
//         
//         /**
//         * @type(string)
//         * @title("Live storage path")
//         * @description("Path for live data storage in delta server")
//         */
//         public $DELTA_LIVE_STORAGE_LOCATION = '/data/server/drive/live';
//         
//         /**
//         * @type(string)
//         * @title("VOD storage path")
//         * @description("Path for VOD storage in delta server")
//         */
//         public $DELTA_VOD_STORAGE_LOCATION = '/data/server/drive/vod';
//         
//         /**
//         * @type(string)
//         * @title("Watch Folder base path")
//         * @description("Base path for incomming VOD in delta server")
//         */
//         public $DELTA_WF_INCOMMING_URI = '/data/server/drive/watchfolders';
//         
//         /**
//         * @type(integer)
//         * @title("Standard Delta Live output Template ID")
//         * @description("Output template that will be base for Live Standard subscriptions")
//         */
//         public $DELTA_STD_EVENT_OUTPUT_TEMPLATE = 10;
//         
//         /**
//         * @type(integer)
//         * @title("Premium Delta Live output Template ID")
//         * @description("Output template that will be base for Live Premium subscriptions")
//         */
//         public $DELTA_PREMIUM_EVENT_OUTPUT_TEMPLATE = 10;
//         
//         /**
//         * @type(integer)
//         * @title("Standard Delta VOD output Template ID")
//         * @description("Output template that will be base for VOD Standard subscriptions")
//         */
//         public $DELTA_STD_VOD_OUTPUT_TEMPLATE = 10;
//         
//         /**
//         * @type(integer)
//         * @title("Premium Delta VOD output Template ID")
//         * @description("Output template that will be base for VOD Premium subscriptions")
//         */
//         public $DELTA_PREMIUM_VOD_OUTPUT_TEMPLATE = 10;
//         
// //         /**
// //         * @type(string)
// //         * @title("APS API Version")
// //         * @readonly
// //         */
// //         public $API_VERSION = 'v1';
//         
//         /**
//         * @type(string)
//         * @title("Live node URL")
//         * @readonly
//         */
//         public $LIVE_NODE_URL = 'rtmp://localhost:1935/';

# Functions to process link/unlink requests
/* Commented out since we don't need to redefine the parent operations
        public function contextsLink() { }
        public function contextsUnlink() { }
*/

# Functions to process CRUD operations:
/* Commented out since we don't need to redefine the parent operations */
	public function provision() {  // create new instance
	# Declaration is needed
	}

	public function configure($new=null) { // change properties
	}
	
	public function retrieve() { // get data
	}
	
	public function unprovision() { 
	}
# Custom functions should be declared here

}

?>
