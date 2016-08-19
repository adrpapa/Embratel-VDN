<?php

if (!defined('APS_DEVELOPMENT_MODE')) define ('APS_DEVELOPMENT_MODE', 'on');

require_once "aps/2/runtime.php";
require_once "elemental_api/live.php";
require_once "elemental_api/utils.php";
require_once "elemental_api/deltaOutputTemplate.php";
require_once "elemental_api/deltaContents.php";
require_once "elemental_api/deltaInput.php";
require_once "elemental_api/preset.php";

/**
 * @type("http://embratel.com.br/app/VDN_Embratel/channel/2.0")
 * @implements("http://aps-standard.org/types/core/resource/1.0")
 */
class channel extends \APS\ResourceBase {

    // Relation with the management context
    /**
        * @link("http://embratel.com.br/app/VDN_Embratel/context/2.0")
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
        * @title("Content ID")
        * @description("Content ID")
        * @readonly
        */
    public $content_id;

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

    /**
        * @type(string)
        * @title("CDN Origin Server")
        * @description("delta access fqdn for CDN content origin")
        * @readonly
        */
    public $cdn_origin_server;

    /**
        * @type(string)
        * @title("Start encoding time")
        * @description("Time event was started")
        * @readonly
        */
    public $start_encoding_time;

    /**
        * @type(number)
        * @title("accumulated encoding time")
        * @description("total encoding time for billing")
        * @readonly
        */
    public $accum_encoding_time;

    /**
        * @type(number)
        * @title("last reported encoding time")
        * @description("Last encoding time reported to billing")
        * @readonly
        */
    public $last_reported_encoding_time;

    /**
        * @type(string)
        * @title("Live Node")
        * @description("Elemental Live node this channell is assigned to")
        * @readonly
        */
    public $live_node;

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
        $logger->info("Iniciando provisionamento de canal ".$this->aps->id);
        $clientid = formatClientID($this->context);

        foreach( $this->context->channels as $channel ) {
            if( $channel->name == $this->name 
               && $channel->aps->id != $this->aps->id ) {
                throw new Exception(_("Live Channel with this name already exists. Please use a different name"));
            }
        }

        $level = ($this->premium ? 'prm' : 'std');
        $presets = new Presets();
        for($i=0;$i<count($this->resolutions);$i++ ) {
            $presets->addPreset(new Preset($this->resolutions[$i],
                    $this->video_bitrates[$i],$this->framerates[$i],
                    $this->audio_bitrates[$i]),$i);
        }

        $event = LiveEvent::newLiveEvent( $this->name, $clientid, $level, $presets );
        
        $this->live_event_id = $event->id;
        $this->live_event_name = $event->name;
        $this->state = $event->status;
        $this->input_URI =  $event->inputURI;
        $this->live_node = $event->live_node;
        $this->input_filter_id = $event->inputFilterID;

        $logger->info("live_event_id:" . $this->live_event_id );
        $logger->info("live_event_name:" . $this->live_event_name );
        $logger->info("state:" . $this->state );
        $logger->info("input_URI:" . $this->input_URI );
        $logger->info("live_node:" . $this->live_node );
        throw new \Rest\Accepted($this, "Event Created", 10); // Return "202 Accepted"
    }

    public function provisionAsync() {
        $logger = \APS\LoggerRegistry::get();
        $logger->setLogFile("logs/channels.log");
        $logger->info("Verificando se provisionamento de canal está completo para ID=".$this->aps->id);
        $event = LiveEvent::getStatus($this->live_event_id);
        $creation_time=$event->xpath('audit_messages/audit/created_at');
        
        echo "Count(creation_time) = ".count($creation_time);
        $this->state = $event->status."";
        if( count($creation_time) < 1 ){
            if( $event->status != "running" ) {
                return;
            }
            $logger->info("live event:" . $this->live_event_id." Has not received input yet" );
            $this->state = _("Waiting start rtmp");
            throw new \Rest\Accepted($this, "Event Created", 10); // Return "202 Accepted"
        }
        
        echo "Event Start Encoding Time is: ".$creation_time[0];
        $content = DeltaContents::getContentsForEvent($this);
        if( $content == null ){
            throw new \Rest\Accepted($this, "Event Created", 10); // Return "202 Accepted"
        }
        $this->content_id = $content->id;
        $this->start_encoding_time = $creation_time[0]."";
        $this->accum_encoding_time = 0;
        $this->last_reported_encoding_time = 0;

        $apsc = \APS\Request::getController();
        $apsc2 = $apsc->impersonate($this);
        $context = $apsc2->getResource($this->context->aps->id);
        $cdn = \APS\TypeLibrary::newResourceByTypeId("http://embratel.com.br/app/VDN_Embratel/cdn/2.0");
        $axName = cleanName($this->name);
        $proto = $this->https ? "https" : "http";
        $cdnName = sprintf("%s_%s",formatClientID($this->context),$axName);
        $originServer = sprintf("l%d%s.origemcdn.embratelcloud.com.br",
                                $this->context->account->id,$axName);
        $originPath = sprintf("out/u/%s/%s",formatClientID($this->context), $proto);

        $cdn->name = $cdnName;
        $cdn->description = $cdnName;
        $cdn->alias = $axName;
        $cdn->origin_server = $originServer;
        $this->origin_server = $originServer;
        $cdn->origin_path = $originPath;
        $cdn->https = $this->https;
        $cdn->live = "true";
        $apsc2->linkResource($context, 'live_cdns', $cdn);
    }

    public function configure($new) {
    	\APS\LoggerRegistry::get()->setLogFile("logs/channels.log");
        $event = LiveEvent::getStatus($this->live_event_id);
        
        if( $this->state != $new->state ) {
            \APS\LoggerRegistry::get()->info (sprintf("Changing channel %s state from %s to %s",
                $this->state, $new->state));
            switch( $new->state ){
                case 'stop':
                    \APS\LoggerRegistry::get()->info("Stopping event $this->live_event_id wiht accum time = $this->accum_encoding_time");
                    $this->accum_encoding_time += $this->getElapsedEncodingTime();
                    $this->start_encoding_time = null;
                    \APS\LoggerRegistry::get()->info("New accum time for event $this->live_event_id = $this->accum_encoding_time");
                    $event->stop();
                    break;
                case 'start':
                    \APS\LoggerRegistry::get()->info("Starting event $this->live_event_id");
                    $event->start();
                    $now = new DateTime();
                    $this->start_encoding_time = $now->format('Y-m-d\TH:i:sP')."\n";
                    \APS\LoggerRegistry::get()->info("Event $this->live_event_id started at $this->start_encoding_time");
                    break;
            }
            $event = LiveEvent::getStatus($this->live_event_id);
        }
//         não tem atributos a alterar no original      $this->_copy($new);
        $this->state = $event->status;
    }

    /*
     * Calcula o tempo que o evento esta running
     */
    private function getElapsedEncodingTime(){
        if( $this->state != 'running' || gettype($this->start_encoding_time) != "object" ){
            return 0;
        }
        $creationTime = DateTime::createFromFormat("Y-m-d\TH:i:sT",$this->start_encoding_time);
        $now = new DateTime();
        $interval = ($now->getTimestamp() - $creationTime->getTimestamp()) / 60;
        return $interval;
    }
    
    public function upgrade(){
        \APS\LoggerRegistry::get()->setLogFile("logs/channels.log");
        \APS\LoggerRegistry::get()->info("Entrando na função upgrade de canal");

    }

    public function unprovision(){
        \APS\LoggerRegistry::get()->setLogFile("logs/channels.log");
        $clientid = formatClientID($this->context);
        \APS\LoggerRegistry::get()->info(sprintf("Iniciando desprovisionamento para evento %s-%s do cliente %s",
            $this->live_event_id, $this->live_event_name, $clientid));

        \APS\LoggerRegistry::get()->info(sprintf("Excluindo Content %s",$this->content_id));
        DeltaContents::delete($this->content_id);
        
        \APS\LoggerRegistry::get()->info(sprintf("Excluindo Input Filter %s",$this->input_filter_id));
        DeltaInputFilter::delete($this->input_filter_id);
        
        \APS\LoggerRegistry::get()->info(sprintf("Excluindo LiveEvent %s",$this->live_event_id));
        LiveEvent::delete($this->live_event_id);

        \APS\LoggerRegistry::get()->info(sprintf("Fim desprovisionamento para evento %s do cliente %s",
                $this->live_event_id, $clientid));
    }

    /**
        * Update live encoding time / DVR time
        * @verb(GET)
        * @path("/updateLiveUsage")
        */
    public function updateLiveUsage () {
        \APS\LoggerRegistry::get()->setLogFile("logs/channels.log");
        $usage = array();
        $currentEncodingTime = round($this->getElapsedEncodingTime() + $this->accum_encoding_time);
        $usage["VDN_Live_Encoding_Minutes"] = $currentEncodingTime - $this->last_reported_encoding_time;
        if( $this->DVR ) {
            $usage["VDN_Live_DVR_Minutes"] = $usage["VDN_Live_Encoding_Minutes"];
        } else {
            $usage["VDN_Live_DVR_Minutes"] = 0;
        }
        $this->last_reported_encoding_time = $currentEncodingTime;
        return $usage;
    }
}
?>
