<?php

if (!defined('APS_DEVELOPMENT_MODE')) define ('APS_DEVELOPMENT_MODE', 'on');

require "aps/2/runtime.php";
require_once "elemental_api/live.php";
require_once "elemental_api/deltaOutputTemplate.php";
require_once "elemental_api/deltaInput.php";
	
	/**
	 * Class channel
	 * @type("http://embratel.com.br/app/VDN_Embratel/channel/1.0")
	 * @implements("http://aps-standard.org/types/core/resource/1.0")
	 */
	
	class channel extends \APS\ResourceBase
	{
		/**
		 * @link("http://embratel.com.br/app/VDN_Embratel/context/1.0")
		 * @required
		 */
		public $context;

		/**
		 * @type("string")
		 * @title("Name")
		 * @description("Channel Name")
		 * @required
		 */
		public $name;	

		/**
		 * @type("string")
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
		 * @type("string")
		 * @title("Premium parameters")
		 * @description("Parâmetros de override para subscriptions Premium")
		 */
		public $premium_parms;


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
		 * @title("Profile id")
		 */
		public $profile_id;

        public function __construct() {
            // setting new log file, path relative to your script current directory
            $this->logger = \APS\LoggerRegistry::get();
            $this->logger->setLogFile("logs/channels.log");
	     }

    #############################################################################################################################################
    ## Definition of the functions that will respond to the different CRUD operations
    #############################################################################################################################################

        public function provision() {
            $clientid = sprintf("Client_%06d",$this->context->account->id);
            $this->logger->debug("Iniciando provisionamento de canal para cliente ".$clientid);
            if( $this->context->isPremium ) {
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
    // 		$this->profile_id = $event->profile_id;

            $this->logger->debug("live_event_id:" . $this->live_event_id );
            $this->logger->debug("live_event_name:" . $this->live_event_name );
            $this->logger->debug("state:" . $this->state );
            $this->logger->debug("input_URI:" . $this->input_URI );
            $this->logger->debug("delta_port:" . $this->delta_port );
            $this->logger->debug("live_node:" . $this->live_node );
    // 		$this->logger->debug("profile_id:" . $this->profile_id );
        }

        public function configure($new) {
            $event = LiveEvent::getStatus($this->live_event_id);
            
            if( $this->state != $new->state ) {
                $this->logger->info (sprintf("Changing channel %s state from %s to %s",
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
			$this->logger->debug("Entrando na função retrieve de canal");

		}

        public function upgrade(){
			$this->logger->debug("Entrando na função upgrade de canal");

		}

        public function unprovision(){
            $clientid = sprintf("Client_%06d",$this->context->account->id);
            $this->logger->debug(sprintf("Iniciando desprovisionamento para evento %s-%s do cliente %s",
                $this->live_event_id, $this->live_event_name, $clientid));
            $this->logger->debug(sprintf("Excluindo Input Filter %s",$this->input_filter_id));
            DeltaInputFilter::delete($this->input_filter_id);
            $this->logger->debug(sprintf("Excluindo LiveEvent %s",$this->live_event_id));
            LiveEvent::delete($this->live_event_id);
            $this->logger->debug(sprintf("Fim desprovisionamento para evento %s do cliente %s",
                $this->live_event_id, $clientid));
		}
}
?>		
