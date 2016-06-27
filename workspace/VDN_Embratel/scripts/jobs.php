<?php

if (!defined('APS_DEVELOPMENT_MODE')) define ('APS_DEVELOPMENT_MODE', 'on');

require_once "aps/2/runtime.php";
require_once "elemental_api/jobVOD.php";
require_once "elemental_api/deltaOutputTemplate.php";
require_once "elemental_api/deltaInput.php";

/**
 * @type("http://embratel.com.br/app/VDN_Embratel/job/1.0")
 * @implements("http://aps-standard.org/types/core/resource/1.0")
 */
class job extends \APS\ResourceBase {
	
	// Relation with the management context
	/**
	 * @link("http://embratel.com.br/app/VDN_Embratel/context/1.0")
	 * @required
	 */
	public $context;

	/**
	 * @type("string")
	 * @title("Name")
	 * @description("Job Name")
	 * @required
	 */
	public $name;	

	/**
	 * @type("string")
	 * @title("Description")
	 * @description("Job Description")
	 * @required
	 */
	public $description;	

	/**
	 * @type("string")
	 * @title("Screen Format")
	 * @description("4:3 / 16:9 ?")
	 */
	public $screen_format;

	/**
	 * @type("boolean")
	 * @title("Extended Configuration (Premium)")
	 * @description("Allow transcoder fine-tuning and multiple transmux packaging")
	 */
	public $premium;

	/**
	 * Readonly parameters obtained from Elemental Server
	 */
	 
	/**
	 * @type("integer")
	 * @title("Job ID")
	 * @description("Job ID in Elemental Server Conductor")
	 * @readonly
	 */
	public $job_id;

	/**
	 * @type("string")
	 * @title("Job name")
	 * @description("Job Name in Elemental Server Conductor")
	 * @readonly
	 */
	public $job_name;

	/**
	 * @type("integer")
	 * @title("Delta Input Filter ID")
	 * @description("Delta Input Filter ID")
	 * @readonly
	 */
	public $input_filter_id;

	/**
	 * @type("string")
	 * @title("State")
 	* @description("Job current state")
	 * @readonly
	 */
	public $state;

	/**
	 * @type("string")
	 * @title("Input URI")
	 * @description("Job Input URI for video ingestion")
	 * @readonly
	 */
	public $input_URI;
	
	/**
	 * @type("string")
	 * @title("Server Node")
	 * @description("Elemental Server node this job is assigned to")
	 * @readonly
	 */
	public $server_node;
	
#############################################################################################################################################
## Definition of the functions that will respond to the different CRUD operations
#############################################################################################################################################

	public function provision() { 
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/jobs.log");
		\APS\LoggerRegistry::get()->info("Iniciando provisionamento de conteudo(job) ".$this->aps->id);
		$clientid = sprintf("Client_%06d",$this->context->account->id);
		$level = ($this->premium ? 'std' : 'prm');
		
		ElementalRest::$auth = new Auth( 'elemental','elemental' );		// TODO: trazer usuario/api key
		$job = JobVOD::newJobVOD( $this->aps->id, $this->input_URI, $clientid, $level );
		
		$this->job_id = $job->id;
		$this->job_name = $job->name;
		$this->state = $job->status;
		$this->input_URI =  $job->inputURI;
		$this->input_filter_id = $job->inputFilterID;
		
		\APS\LoggerRegistry::get()->info("job_id:" . $this->job_id );
		\APS\LoggerRegistry::get()->info("job_name:" . $this->job_name );
		\APS\LoggerRegistry::get()->info("state:" . $this->state );
		\APS\LoggerRegistry::get()->info("input_URI:" . $this->input_URI );
    }

    public function configure($new) {

    }

	public function retrieve(){

	}

    public function upgrade(){

	}

    public function unprovision(){
    	\APS\LoggerRegistry::get()->setLogFile("logs/jobs.log");
    	
    	$clientid = sprintf("Client_%06d",$this->context->account->id);
    	
    	\APS\LoggerRegistry::get()->info(sprintf("Iniciando desprovisionamento para job %s-%s do cliente %s",
    			$this->job_id, $this->job_name, $clientid));
    	
    	\APS\LoggerRegistry::get()->info(sprintf("Excluindo Job %s",$this->job_id));

    	JobVOD::delete($this->job_id);
    	
    	\APS\LoggerRegistry::get()->info(sprintf("Fim desprovisionamento para job %s do cliente %s",
    			$this->job_id, $clientid));
	}
}
?>
