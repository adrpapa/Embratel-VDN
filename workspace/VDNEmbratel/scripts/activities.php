<?php

require_once 'loader.php';
 
/**
* Classs atividades registra todas as atividades que são executadas contra os recursos da APS
* @type("http://embratel.com.br/app/VDNEmbratel/activity/1.0")
* @implements("http://aps-standard.org/types/core/resource/1.0")
* @access(referrer,true)
*/
class activity extends \APS\ResourceBase {
	/**
	 * @link("http://embratel.com.br/app/VDNEmbratel/context/2.1")
	 * @required
	 */
	public $context;

	/**
	 * @type(string)
	 * @title("Nome do Recurso")
	 */
 	public $resource_name;
	
	/**
	 * @type(string)
	 * @title("login do usuário")
	 */
	public $usuer_login;
	
	/**
	 * @type(string)
	 * @title("Nome do usuário")
	 */
	public $user_name;
	
	/**
	 * @type(string)
	 * @title("Timestamp da operação")
	 */
	public $operation_timestamp;
	
	/**
	 * @type(string)
	 * @title("Tipo de operação")
	 */
	public $operation_type;
	
	/**
	 * @type(string)
	 * @title("Tipo do recurso")
	 */
	public $resource_type;
	
	/**
	 * @type(string)
	 * @title("Conteúdo do registro depois da operação")
	 */
	public $resource_after;
	
	/**
	 * @type(string)
	 * @title("Conteúdo do registro antes da operação")
	 */
	public $resource_before;
	
	/**
	 * @type(string)
	 * @title("Resultado")
	 */
	public $result;

	/**
	 * @type(string)
	 * @title("Observações")
	 */
	public $notes;


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