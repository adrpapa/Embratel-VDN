<?php

require_once 'loader.php';

/**
 * Class globais presents application and its global parameters
 * @type("http://embratel.com.br/app/VDNEmbratel/globais/1.0")
 * @implements("http://aps-standard.org/types/core/application/1.0")
 */
class globais extends \APS\ResourceBase {
# Link to collection of contexts. Pay attention to [] brackets at the end of the @link line.
	/**
	 * @link("http://embratel.com.br/app/VDNEmbratel/context/2.0[]")
	 */
	public $contexts;
	
	/**
	* Constantes estão configuradas no arquivo config_constants.php
	*/
	/**
	 * @type(string)
	 * @title("URL de provisionamento")
	 */
 	public $CDMS_ADDRESS = '192.118.77.183';
	
	/**
	 * @type(string)
	 * @title("Porta de provisionamento")
	 */
	public $CDMS_PORT = "8443";
	
	/**
	 * @type(string)
	 * @title("Usuário de provisionamento")
	 */
	public $CDMS_USER = "admin";
	
	/**
	 * @type(string)
	 * @title("Senha de provisionamento")
	 */
	public $CDMS_PWD = "C1sc0CDN!";
	
	
	/**
	 * @type(string)
	 * @title("Domínio consumo")
	 */
	public $CDMS_DOMAIN = "csi.cds.cisco.com";
	
	/**
	 * @type(string)
	 * @title("URL para obtenção do consumo")
	 */
	public $SPLUNK_ADDRESS = '192.118.76.206';
	
	/**
	 * @type(string)
	 * @title("Endpoint para obtenção do consumo")
	 */
	public $SPLUNK_ENDPOINT = '/splunkApp/en-US/custom/CDN_Usage_Reporting/cdnusage/metric_data';
	
	/**
	 * @type(string)
	 * @title("Query para obtenção do consumo")
	 */
	public $SPLUNK_QUERY = '?metric=cdn_ds_bytes_delivered&time_range=%s&span=%s&delivery_service=%s&time_format';
	
	/**
	 * @type(string)
	 * @title("URL do Portal Analytics")
	 */
	public $PORTAL_ANALYTICS_URL = '192.118.76.206';
	
	/**
	 * @type(string)
	 * @title("Template de Email")
	 */
	public $EMAIL_TEMPLATE_NAME = 'Notificação de criação de Serviço de Entrega';
	
	/**
	 * @type(string)
	 * @title("Maximum bitrate limit per session")
	 */
	public $CDMS_MAX_BITRATE_PER_SESSION = '12000';
	/**
	 * @type(boolean)
	 * @title("Depuração")
	 */
 	public $debug = true;
	
# Functions to process link/unlink requests
/* Commented out since we don't need to redefine the parent operations
        public function contextsLink() { }
        public function contextsUnlink() { }
*/

# Functions to process CRUD operations:
/* Commented out since we don't need to redefine the parent operations */
    public function provision() {
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/" . __CLASS__ . "_" . date("Ymd") . ".log");
		$logger->debug("[".__METHOD__. '] ==');
    }

    public function unprovision(){
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/" . __CLASS__ . "_" . date("Ymd") . ".log");
		$logger->debug("[".__METHOD__. '] ==');
    }
    
    public function retrieve() {
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/" . __CLASS__ . "_" . date("Ymd") . ".log");
		$logger->debug("[".__METHOD__. '] ==');
    }

    public function disable() {
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/" . __CLASS__ . "_" . date("Ymd") . ".log");
		$logger->debug("[".__METHOD__. '] ==');
    }

    public function enable() {
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/" . __CLASS__ . "_" . date("Ymd") . ".log");
		$logger->debug("[".__METHOD__. '] ==');
    }

# Custom functions should be declared here

}

?>
