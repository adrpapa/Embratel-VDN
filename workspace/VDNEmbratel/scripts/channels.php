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
 * @type("http://embratel.com.br/app/VDNEmbratel/channel/2.1")
 * @implements("http://aps-standard.org/types/core/resource/1.0")
 */
class channel extends \APS\ResourceBase {

    // Relation with the management context
    /**
        * @link("http://embratel.com.br/app/VDNEmbratel/context/1.0")
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
        * @title("request")
        * @description("request for channel start/stop")
        */
    public $request;

    /**
        * @type(string)
        * @title("Input URI")
        * @description("Live Channel Input URI for client's transmission")
        * @readonly
        */
    public $input_URI;

    /**
        * @type(string)
        * @title("CDN APS ID")
        * @description("ID of cdn created for event")
        * @readonly
        */
    public $cdn_aps_id;

    /**
        * @type(string)
        * @title("Service Routing Domain Name")
        * @description("Service Routing Domain Name")
        * @readonly
        */
    public $cdn_routing_domain;

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
        */
    public $resolutions;

    /**
        * @type(string[])
        * @title("Frame Rates")
        * @description("Array of Frame Rates for the generated streams")
        */
    public $framerates;

    /**
        * @type(string[])
        * @title("Video Bitrates")
        * @description("Array of Video Bitrates for the generated streams")
        */
    public $video_bitrates;

    /**
        * @type(string[])
        * @title("Audio Bitrates")
        * @description("Array of Audio Bitrates for the generated streams")
        */
    public $audio_bitrates;

#############################################################################################################################################
## Definition of the functions that will respond to the different CRUD operations
#############################################################################################################################################

    public function provision() { 
        $logger = getLogger("channels.log");
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
        $this->state = $this->pendingState();
        $this->input_URI =  $event->inputURI; 
        $this->live_node = $event->live_node;
        $this->input_filter_id = $event->inputFilterID;
        $this->start_encoding_time = null;
        $this->accum_encoding_time = 0;
        $this->last_reported_encoding_time = 0;

        $axName = cleanName($this->name);
        $proto = $this->https ? "https" : "http";
        $cdnName = sprintf("%s_%s",formatClientID($this->context),$axName);
        $this->cdn_origin_server = sprintf("l%d%s.origemcdn.embratelcloud.com.br",
                                $this->context->account->id,$axName);
        $originPath = sprintf("out/u/%s",formatClientID($this->context));

        $cdn = \APS\TypeLibrary::newResourceByTypeId("http://embratel.com.br/app/VDNEmbratel/cdn/1.0");
        $cdn->name = $cdnName;
        $cdn->description = $cdnName;
        $cdn->alias = $axName;
        $cdn->origin_server = $this->cdn_origin_server;
        $cdn->origin_path = $originPath;
        $cdn->https = $this->https;
        $cdn->live = "true";
        $logger->info("Creating CDN with values: \n".print_r($cdn,true));
        $apsc = \APS\Request::getController();
        $apsc2 = $apsc->impersonate($this);
        $context = $apsc2->getResource($this->context->aps->id);        
        $cdnxml = $apsc2->linkResource($context, 'cdns', $cdn);
    
        foreach( $this->context->cdns as $cdn2 ) {
            if( $cdn2->origin_server == $cdn->origin_server) {
                $this->cdn_routing_domain = $cdn2->origin_domain;
                $this->cdn_aps_id = $cdn2->aps->id;
                $logger->info("Found routing domain ");
                break;
            }
        }

        $logger->info("Finished provisionning event ID:" . $this->live_event_id );
        $logger->info("\tlive_event_id:" . $this->live_event_id );
        $logger->info("\tlive_event_name:" . $this->live_event_name );
        $logger->info("\tstate:" . $this->state );
        $logger->info("\tcdn_origin_server:" . $this->cdn_origin_server );
        $logger->info("\tinput_URI:" . $this->input_URI );
        $logger->info("\tlive_node:" . $this->live_node );
        
    }
    public function provisionAsync() {
        return;
    }
    
    private function pendingState(){
        return _("Wait rtmp start");
    }
    
    public function upgrade(){
        $logger = getLogger("channels.log");
        $logger->info("Entrando na função upgrade de canal");

    }

    public function unprovision(){
        $logger=getLogger("channels.log");
        $logger->info(sprintf("Iniciando desprovisionamento para evento %s-%s",
            $this->live_event_id, $this->live_event_name));

        $logger->info(sprintf("Excluindo LiveEvent %s",$this->live_event_id));
        try {
            LiveEvent::delete($this->live_event_id);
        } catch (Exception $fault){
            $logger->error("Erro na exclusão do Live Event:\n");
            $logger->error($fault->getMessage());
        }

        try {
            $logger->info(sprintf("Excluindo CDN %s",$this->cdn_aps_id));
            $apsc = \APS\Request::getController();
            $apsc2 = $apsc->impersonate($this);
            $cdn = $apsc2->getResource($this->cdn_aps_id);        
            $apsc2->unprovisionResource($cdn);
        } catch (Exception $fault){
            $logger->error("Erro na exclusão do CDN:\n");
            $logger->error($fault->getMessage());
        }

        try {
            if( $this->content_id != null ){
                $logger->info(sprintf("Excluindo Content %s",$this->content_id));
                DeltaContents::delete($this->content_id);
            }
        } catch (Exception $fault){
            $logger->error("Erro na exclusão do Content:\n");
            $logger->error($fault->getMessage());
        }

        try {
            $logger->info(sprintf("Excluindo Input Filter %s",$this->input_filter_id));
            DeltaInputFilter::delete($this->input_filter_id);
        } catch (Exception $fault){
            $logger->error("Erro na exclusão do Input Filter:\n");
            $logger->error($fault->getMessage());
        }
        
        $logger->info(sprintf("Fim desprovisionamento para evento %s",
                $this->live_event_id));
    }

    public function configure($new) {
    	$logger = getLogger("channels.log");
        $event = LiveEvent::getStatus($this->live_event_id);
        
        $logger->info (sprintf("Channel %s with state %s was requested to %s",
                $this->live_event_id,  $this->state, $new->request));
        
        switch( $new->request ){
            case 'stop':
                if( $event->status == 'running') {
                    $elapsed = $this->getElapsedEncodingTime();
                    $logger->info("Stopping event $this->live_event_id Elapsed time: $elapsed accum time = $this->accum_encoding_time");
                    $this->accum_encoding_time += $elapsed;
                    $this->start_encoding_time = null;
                    $logger->info("New accum time for event $this->live_event_id = $this->accum_encoding_time");
                    LiveEvent::stop($this->live_event_id);
                    $this->state = "stopping";
                }
                break;
            case 'start':
                if( $event->status[0] != 'running') {
                    $logger->info("Starting event $this->live_event_id");
                    LiveEvent::start($this->live_event_id);
                    $this->state = "starting";
                }
                break;
            otherwise:
                $this->state = $event->status."";
                break;
//          não tem atributos a alterar no original      $this->_copy($new);
        }
    }

    /*
    ** C u s t o m   p r o c e d u r e s
    */
    
    /**
     * get event current status
     * @verb(GET)
     * @path("/updateChannelStatus")
     * @param()
     * @returns {object}
     */
    public function updateChannelStatus () {
        $logger = getLogger("channels.log");
        $logger->info("Called updateChannelStatus for event id=".$this->live_event_id.". Current status = ".$this->state);
        $event = LiveEvent::getStatus($this->live_event_id);
        $logger->info("Elemental Live status for event id=".$this->live_event_id.
                " is: ".$event->status);

        $this->state = $event->status.'';
        if( count($event->created_at) < 1 ){
            $logger->info("live event:" . $this->live_event_id." has not received input yet" );
            $this->state = $this->pendingState();
        }
        else {
            if( $this->content_id == null ) {
                $content = DeltaContents::getContentsForEvent($this);
                if( $content != null ){
                    $this->content_id = $content->id;
                    $logger->info("Event Content ID: $this->content_id");
                }
            }
            $eventEncondingTime = $event->created_at[0]."";
            if( $this->start_encoding_time != $eventEncondingTime ) {
                $logger->info("Event Start Encoding Time is: $eventEncondingTime");
                $this->start_encoding_time = $eventEncondingTime;
            }
        }
        $apsc = \APS\Request::getController();
        $apsc->updateResource($this);
        return $this;
    }

    /*
     * Calcula o tempo que o evento esta running
     */
    private function getElapsedEncodingTime(){
        if( $this->state != 'running' || $this->start_encoding_time == null ){
            return 0;
        }
        $creationTime = DateTime::createFromFormat("Y-m-d\TH:i:sT",$this->start_encoding_time);
        $now = new DateTime();
        $interval = ($now->getTimestamp() - $creationTime->getTimestamp()) / 60;
        return $interval;
    }
    
    /**
        * Update live encoding time / DVR time
        * @verb(GET)
        * @path("/updateLiveUsage")
        */
    public function updateLiveUsage () {
        $logger = getLogger("channels.log");
        $logger->debug("Called updateLiveUsage for event id=".$this->live_event_id);
        $this->updateChannelStatus();
        $currentEncodingTime = $this->accum_encoding_time + round($this->getElapsedEncodingTime());
        $usage = array();
        $usage["VDN_Live_Encoding_Minutes"] = $currentEncodingTime - $this->last_reported_encoding_time;
        if( $this->dvr ) {
            $usage["VDN_Live_DVR_Minutes"] = $usage["VDN_Live_Encoding_Minutes"];
        } else {
            $usage["VDN_Live_DVR_Minutes"] = 0;
        }
        $logger->info("Event id ".$this->live_event_id." start_encoding_time: ".$this->start_encoding_time.
                      " last_reported_encoding_time: ". $this->last_reported_encoding_time." currentEncodingTime: ".$currentEncodingTime);
        $this->last_reported_encoding_time = $currentEncodingTime;
        $apsc = \APS\Request::getController();
        $apsc->updateResource($this);
        return $usage;
    }
}
?>
