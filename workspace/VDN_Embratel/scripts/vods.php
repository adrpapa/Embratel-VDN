<?php

//if (!defined('APS_DEVELOPMENT_MODE')) define ('APS_DEVELOPMENT_MODE', 'on');

require_once "aps/2/runtime.php";
require_once "elemental_api/deltaContents.php";

/**
 * @type("http://embratel.com.br/app/VDN_Embratel/vod/1.0")
 * @implements("http://aps-standard.org/types/core/resource/1.0")
 */
class vod extends \APS\ResourceBase {
	
	// Relation with the management context
	/**
	 * @link("http://embratel.com.br/app/VDN_Embratel/context/1.0")
	 * @required
	 */
	public $context;

	/**
	 * @type(integer)
	 * @title("Content ID")
	 * @description("Content ID in Elemental Delta")
	 * @readonly
	 */
	public $content_id;

	/**
	 * @type(string)
	 * @title("Content Name")
	 * @description("Content Name")
	 * @readonly
	 */
	public $content_name;

	/**
	 * @type(string)
	 * @title("Content Path")
	 * @description("Content Path")
	 * @required
	 */
	public $path;



	/**
	 * @type(boolean)
	 * @title("HTTPS")
	 * @description("Turn on HTTPS feature for live")
	 * @readonly
	 */
	public $https;	
	
	/**
	 * @type(integer)
	 * @title("Job ID")
	 * @description("ID of job that submitted this content")
	 * @readonly
	 */
	public $job_id;

	/**
	 * @type(string)
	 * @title("Input URI")
	 * @description("Job Input URI for video ingestion")
	 * @readonly
	 */
	public $input_URI;

#############################################################################################################################################
## Definition of the functions that will respond to the different CRUD operations
#############################################################################################################################################
	public function provision() {
	}

    public function configure($new) {
    }

	public function retrieve(){

	}

    public function upgrade(){

	}

    public function unprovision(){
    	\APS\LoggerRegistry::get()->setLogFile("logs/vods.log");
    	
    	$clientid = sprintf("Client_%06d",$this->context->account->id);
    	
    	\APS\LoggerRegistry::get()->info(sprintf("Iniciando desprovisionamento do conteudo %s-%s do cliente %s",
    			$this->content_id, $this->content_name, $clientid));
    	\APS\LoggerRegistry::get()->info(sprintf("Excluindo Job %s",$this->job_id));

    	try {
    		ElementalRest::$auth = new Auth( 'elemental','elemental' );
     		DeltaContents::delete($this->content_id);
    	} catch (Exception $fault) {
    		$this->logger->info("Error while deleting content job, :\n\t" . $fault->getMessage());
    		throw new Exception($fault->getMessage());
    	}    	
    	
    	\APS\LoggerRegistry::get()->info(sprintf("Fim desprovisionamento do conteudo %s-%s do cliente %s",
				$this->content_id, $this->content_name, $clientid));
	}
}
?>
