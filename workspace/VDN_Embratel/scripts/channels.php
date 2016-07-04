<?php

if (!defined('APS_DEVELOPMENT_MODE')) define ('APS_DEVELOPMENT_MODE', 'on');

require_once "aps/2/runtime.php";
require_once "elemental_api/live.php";
require_once "elemental_api/deltaOutputTemplate.php";
require_once "elemental_api/deltaInput.php";

/**
 * @type("http://embratel.com.br/app/VDN_Embratel/channel/1.0")
 * @implements("http://aps-standard.org/types/core/resource/1.0")
 */
class channel extends \APS\ResourceBase {
	
	// Relation with the management context
	/**
	 * @link("http://embratel.com.br/app/VDN_Embratel/context/1.0")
	 * @required
	 */
	public $context;

	/**
	 * @type(string)
	 * @title("Name")
	 * @description("Channel Name")
	 * @required
	 */
	public $name;	

	/**
	 * @type(string)
	 * @title("Description")
	 * @description("Channel Description")
	 * @required
	 */
	public $description;	

	/**
	 * @type(string)
	 * @title("Screen Format")
	 * @description("4:3 / 16:9 ?")
	 */
	public $screen_format;

	/**
	 * @type(boolean)
	 * @title("DVR")
	 * @description("Turn on DVR feature for live")
	 */
	public $dvr;

	/**
	 * @type(boolean)
	 * @title("HTTPS")
	 * @description("Turn on HTTPS feature for live")
	 */
	public $https;

	/**
	 * @type(boolean)
	 * @title("Extended Configuration (Premium)")
	 * @description("Allow transcoder fine-tuning and multiple transmux packaging")
	 */
	public $premium;

	/**
	 * Readonly parameters obtained from Elemental Live
	 */
	 
	/**
	 * @type(integer)
	 * @title("Live Event ID")
	 * @description("Live Event ID in Elemental Live Conductor")
	 * @readonly
	 */
	public $live_event_id;

	/**
	 * @type(string)
	 * @title("Live Event name")
	 * @description("Live Event Name in Elemental Live Conductor")
	 * @readonly
	 */
	public $live_event_name;

	/**
	 * @type(integer)
	 * @title("Delta Input Filter ID")
	 * @description("Delta Input Filter ID")
	 * @readonly
	 */
	public $input_filter_id;

	/**
	 * @type(string)
	 * @title("State")
 	* @description("Live channel current state")
	 * @readonly
	 */
	public $state;

	/**
	 * @type(string)
	 * @title("Input URI")
	 * @description("Live Channel Input URI for client's transmission")
	 * @readonly
	 */
	public $input_URI;
	/**
	 * @type(integer)
	 * @title("Delta UDP Port")
	 * @description("UDP Port used for communication with Elemental Delta")
	 * @readonly
	 */
	public $delta_port;

	/**
	 * @type(string)
	 * @title("Live Node")
	 * @description("Elemental Live node this channell is assigned to")
	 * @readonly
	 */
	public $live_node;
	
	/**
	 * @type(string)
	 * @title("Server Node")
	 * @description("Elemental Server node this job is assigned to")
	 * @readonly
	 */
	public $server_node;
	
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
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/channels.log");
        \APS\LoggerRegistry::get()->info("Iniciando provisionamento de canal ".$this->aps->id);
        $clientid = sprintf("Client_%06d",$this->context->account->id);
        \APS\LoggerRegistry::get()->info($this);
	}

	public function renamed_to_inhibit_call_of_provision() { 
		$logger = \APS\LoggerRegistry::get();
		$logger->setLogFile("logs/channels.log");
        \APS\LoggerRegistry::get()->info("Iniciando provisionamento de canal ".$this->aps->id);
        $clientid = sprintf("Client_%06d",$this->context->account->id);
        $event = LiveEvent::newStandardLiveEvent( $this->aps->id, $clientid );
		if( $this->premium ) {
			$event = LiveEvent::newPremiumLiveEvent( $this->aps->id, $clientid );
		} else {
			$event = LiveEvent::newStandardLiveEvent( $this->aps->id, $clientid );
		}
        
        $this->live_event_id = $event->id;
        $this->live_event_name = $event->name;
        $this->state = $event->status;
        $this->input_URI =  $event->inputURI;
        $this->delta_port = $event->udpPort;
        $this->live_node = $event->live_node;
        $this->input_filter_id = $event->inputFilterID;

        \APS\LoggerRegistry::get()->info("live_event_id:" . $this->live_event_id );
        \APS\LoggerRegistry::get()->info("live_event_name:" . $this->live_event_name );
        \APS\LoggerRegistry::get()->info("state:" . $this->state );
        \APS\LoggerRegistry::get()->info("input_URI:" . $this->input_URI );
        \APS\LoggerRegistry::get()->info("delta_port:" . $this->delta_port );
        \APS\LoggerRegistry::get()->info("live_node:" . $this->live_node );

    }

    public function configure($new) {
    	\APS\LoggerRegistry::get()->setLogFile("logs/channels.log");
        $event = LiveEvent::getStatus($this->live_event_id);
        
        if( $this->state != $new->state ) {
            \APS\LoggerRegistry::get()->info (sprintf("Changing channel %s state from %s to %s",
                $this->state, $new->state));
            switch( $new->state ){
                case 'Stopped':
                    $event->stop();
                    break;
                case 'Running':
                    $event->start();
                    break;
            }
            $event = LiveEvent::getStatus($this->live_event_id);
        }
        $this->_copy($new);
        $this->state = $event->status;
    }

	public function retrieve(){
    	\APS\LoggerRegistry::get()->setLogFile("logs/channels.log");
		\APS\LoggerRegistry::get()->info("Entrando na função retrieve de canal");

	}

    public function upgrade(){
    	\APS\LoggerRegistry::get()->setLogFile("logs/channels.log");
		\APS\LoggerRegistry::get()->info("Entrando na função upgrade de canal");

	}

    public function unprovision(){
    	\APS\LoggerRegistry::get()->setLogFile("logs/channels.log");
        $clientid = sprintf("Client_%06d",$this->context->account->id);
        \APS\LoggerRegistry::get()->info(sprintf("Iniciando desprovisionamento para evento %s-%s do cliente %s",
            $this->live_event_id, $this->live_event_name, $clientid));
        \APS\LoggerRegistry::get()->info(sprintf("Excluindo Input Filter %s",$this->input_filter_id));
        DeltaInputFilter::delete($this->input_filter_id);
        \APS\LoggerRegistry::get()->info(sprintf("Excluindo LiveEvent %s",$this->live_event_id));
        LiveEvent::delete($this->live_event_id);
        \APS\LoggerRegistry::get()->info(sprintf("Fim desprovisionamento para evento %s do cliente %s",
                $this->live_event_id, $clientid));
	}
}
?>
