<?php

if (!defined('APS_DEVELOPMENT_MODE')) define ('APS_DEVELOPMENT_MODE', 'on');

require_once "aps/2/runtime.php";
require_once "elemental_api/deltaContents.php";
require_once "elemental_api/preset.php";

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
	 * @description("Content ID")
	 * @required
	 */
	public $content_īd;

	/**
	 * @type(string)
	 * @title("Content Name")
	 * @description("Content Name")
	 * @required
	 */
	public $name;	

	/**
	 * @type(string)
	 * @title("Content Path")
	 * @description("Content Path")
	 * @required
	 */
	public $path;

	/**
	 * @type(string)
	 * @title("Screen Format")
	 * @description("4:3 / 16:9 ?")
	 */
	public $screen_format;

	/**
	 * @type(boolean)
	 * @title("Extended Configuration (Premium)")
	 * @description("Allow transcoder fine-tuning and multiple transmux packaging")
	 */
	public $premium;

	/**
	 * @type(boolean)
	 * @title("HTTPS")
	 * @description("Turn on HTTPS feature for live")
	 */
	public $https;	
	
	/**
	 * Readonly parameters obtained from Elemental Server
	 */
	 
	/**
	 * @type(integer)
	 * @title("Job ID")
	 * @description("Job ID in Elemental Server Conductor")
	 * @readonly
	 */
	public $job_id;

	/**
	 * @type(integer)
	 * @title("Delta Input Filter ID")
	 * @description("Delta Input Filter ID")
	 * @readonly
	 */
	public $input_filter_id;

	/**
	 * @type(string)
	 * @title("Input URI")
	 * @description("Job Input URI for video ingestion")
	 */
	public $input_URI;
	
	/**
	 * @type(string[])
	 * @title("Resolutions")
	 * @description("Array of Video Resolutions for the generated streams")
	 * @readonly
	 */
	public $resolutions;
	
	/**
	 * @type(string[])
	 * @title("Frame Rates")
	 * @description("Array of Frame Rates for the generated streams")
	 * @readonly
	 */
	public $framerates;
	
	/**
	 * @type(string[])
	 * @title("Video Bitrates")
	 * @description("Array of Video Bitrates for the generated streams")
	 * @readonly
	 */
	public $video_bitrates;

	/**
	 * @type(string[])
	 * @title("Audio Bitrates")
	 * @description("Array of Audio Bitrates for the generated streams")
	 * @readonly
	 */
	public $audio_bitrates;
	
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
    			$this->content_īd, $this->content_name, $clientid));
    	\APS\LoggerRegistry::get()->info(sprintf("Excluindo Job %s",$this->job_id));

    	try {
    		ElementalRest::$auth = new Auth( 'elemental','elemental' );
     		DeltaContents::delete($this->content_īd);
    	} catch (Exception $fault) {
    		$this->logger->info("Error while deleting content job, :\n\t" . $fault->getMessage());
    		throw new Exception($fault->getMessage());
    	}    	
    	
    	\APS\LoggerRegistry::get()->info(sprintf("Fim desprovisionamento do conteudo %s-%s do cliente %s",
				$this->content_id, $this->content_name, $clientid));

	}
}
?>
