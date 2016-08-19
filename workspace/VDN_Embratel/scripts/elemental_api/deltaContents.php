<?php
    require_once "configConsts.php";
    require_once "elementalRest.php";
    require_once "utils.php";
    require_once "jobVOD.php";
    require_once "live.php";

    class DeltaContents {
    /*
    ** Classe que Lista conteúdos do cliente
    */
        public static function delete($id) {
        
            $outTemp = DeltaContents::getElementalRest()->restDelete($id);
        }

        public static function getElementalRest() {
            return new ElementalRest($hostname=ConfigConsts::DELTA_HOST,
                        $apiEndpoint='contents', $port=ConfigConsts::DELTA_PORT);
        }

        public static function getHLSFilter() {
            return new ElementalRest($hostname=ConfigConsts::DELTA_HOST,
                        $apiEndpoint='hls_contents', $port=ConfigConsts::DELTA_PORT);
        }

        public static function getContentsFromJob($jobID) {
            echo "Buscando informação do job $jobID\n";
            $jobXml = JobVOD::getElementalRest()->restGet($jobID);
//             print($jobXml->asXml());
            $input = $jobXml->input;
            $content = new DeltaContents();
            $content->input_uri  =   $input->file_input->uri."";
            $content->fileName = end(explode('/', $input->file_input->uri));
            // transformação do formato 2mn 26s em segundos
            preg_match_all("|([\d\.]+)\s*([^\d^\s^\.]+)|", $input->input_info->general->duration, $time_parts);
            $multi = array("ms"=>1, "s"=>1000, "mn"=>60*1000, "h"=>60*60*1000, "d"=>24*60*60*1000 );
            $content->totalMiliSeconds = 0;
//              var_dump($time_parts);
            for( $ix=0; $ix < count($time_parts[0]); $ix++ ) {
                $unit = $time_parts[2][$ix];
                $multiplier = $multi[$unit] * $time_parts[1][$ix];
                $content->totalMiliSeconds += $multiplier;
                next($multi);
            }
            echo "file_size: ".$input->input_info->general->file_size."\n";
            $content->file_size = $input->input_info->general->file_size."";
            
            echo "input_duration: ".$jobXml->content_duration->input_duration."\n";
            $content->input_duration = $jobXml->content_duration->input_duration."";
            
            echo "stream_count: ".$jobXml->content_duration->stream_count."\n";
            $content->stream_count = $jobXml->content_duration->stream_count."";
            
            echo "total_stream_duration: ".$jobXml->content_duration->total_stream_duration."\n";
            $content->total_stream_duration = $jobXml->content_duration->total_stream_duration."";

            echo "jobDestination: ".$jobXml->output_group->apple_live_group_settings->destination->uri."\n";
            $content->jobDestination = $jobXml->output_group->apple_live_group_settings->destination->uri."";

            echo "Buscando informação do job $jobID\n";
            $cut = strpos($content->jobDestination, "Client_");
            // remove mount point thru client id from job destination
            $outpath = substr($content->jobDestination, $cut);
//             echo "Job Destination = $content->jobDestination outputpath = $outpath\n";
            
            echo "Listando conteúdo do Delta\n";
            $allContents = DeltaContents::getElementalRest()->restGet();
//             echo "Looking for outputpath = $outpath\n";
            foreach( $allContents->content as $xmlContent ){
                $path = $xmlContent->path;
                $cut = strpos($path, "Client_");
                $path = dirname(substr($path, $cut))."/";
                if( $path != $outpath ) {
                    continue;
                }
                $content->id = idFromHref($xmlContent);
                $content->path = $xmlContent->path."";
                
                try {
                    $thisContent = DeltaContents::getHLSFilter()->restGet($content->id);
                    $content->endpoint = "";
                    $sep="";
                    foreach( $thisContent->xpath("*/custom_endpoint_uri") as $ep ) {
                        $content->endpoint .= $ep.$sep;
                        $sep=" | ";
                    }
                }
                catch (InvalidArgumentException $ex)
                {
                    $content->endpoint = "N/A";
                }
                return $content;
            }
            throw new Exception('Job with ID $jobID does not have a matching content!!!');
        }

        public $input_uri;
        public $fileName;
        public $id;
        public $path;
        public $endpoint;
        public $channel;
        public $eventpath;
        
        public static function getContentsForEvent($channel) {
            echo "Buscando conteudo para evento $channel->live_event_id\n";
            $event = LiveEvent::getElementalRest()->restGet($channel->live_event_id);

            $content = new DeltaContents();
            $content->input_uri = $channel->inputURI;
            $content->fileName = end(explode('/', $content->input_uri));
            $content->channel = $channel;
            // Remove raiz do WebDav
            $axpath = substr($event->output_group->apple_live_group_settings->destination->uri."",strlen(ConfigConsts::DELTA_WEBDAV_URI_ROOT));
            $evPathParts = explode("/", $axpath);
            $content->eventpath = $evPathParts[0]."/".$evPathParts[1];

            $eventPathLen = strlen($content->eventpath);
            $fixedPathLen = strlen(ConfigConsts::DELTA_LIVE_STORAGE_LOCATION);
            
            echo "Listando conteúdo do Delta\n";
            $allContents = DeltaContents::getElementalRest()->restGet();
            foreach( $allContents->content as $xmlContent ){
                $path = substr($xmlContent->path, $fixedPathLen+1, $eventPathLen);
                if( $path != $content->eventpath ) {
                    echo "\n$path != $content->eventpath\n";
                    continue;
                }
                $content->id = idFromHref($xmlContent);
                $content->path = $xmlContent->path."";
                $allFilters = DeltaContents::getElementalRest()->restGet($content->id, null, "filters");
                foreach( $allFilters->filter as $filter ) {
                    DeltaContents::getElementalRest()->restDelete($content->id, "filters/".$filter->id);
                }

                $content->createFiltersForEvent();
                try {
                    $thisContent = DeltaContents::getElementalRest()->restGet($content->id, null, "filters");
                    $content->endpoint = "";
                    $sep="";
                    foreach( $thisContent->xpath("filter") as $filter ) {
                        if($filter->filter_type == "passthrough"){
                            if( $filter->parent_id == "" )
                                DeltaContents::getElementalRest()->restDelete($content->id, "filters/".$filter->id);
                            continue;
                        }
                        $content->endpoint .= $sep.$filter->custom_endpoint_uri;
                        $sep=" | ";
                    }
                }
                catch (InvalidArgumentException $ex)
                {
                    $content->endpoint = "N/A";
                }
                return $content;
            }
            return null;
//             throw new Exception('Live Event with ID $channel->live_event_id does not have a matching content!!!');
        }

        public function createFiltersForEvent() {
            $indexDuration = $this->channel->dvr ? 60*60*2 : 120;
            $https = ($this->channel->https);
            $filters = array();
            if( $https ) {
                $hls = $this->putOutputFilter(new HLSPackage($indexDuration, null, null, "EVENT"));
                $this->putOutputFilter( new HLSEncryption($this->eventpath,  $hls->filter[0]->id.""));
            } else {
                 $this->putOutputFilter(new HLSPackage($indexDuration, $this->eventpath ));
                if($this->channel->premium){
                    $this->putOutputFilter(new HDSPackage($indexDuration, $this->eventpath));
                    $this->putOutputFilter(new MSSPackage($indexDuration, $this->eventpath));
                    $this->putOutputFilter(new DashISOPackage($indexDuration, $this->eventpath));
                }
            }
            foreach( $filters as $filter ){
                echo $filter->xml->asXML();
            }
        }

        private function putOutputFilter($filter){
            echo $filter->xml->asXML();
            return DeltaContents::getElementalRest()->putRecord($this->id, null, $filter->xml);
        }
    }

    class Filter{
        public $xml;
        public $filterSettings;
        public function __construct($filterType, $indexDuration, $outputUrl=null, $parentFilter=null){
            $this->xml = new SimpleXmlElement("<content></content>");
            $this->filter = $this->xml->addChild('filter');
            $this->filter->name = "filter_".$filterType;
            $this->filter->parent_id = $parentFilter;
            if($outputUrl==null){
                $this->filter->endpoint = "false";
            } else {
                $this->filter->endpoint = "true";
                $this->filter->output_url = $outputUrl;
            }
            $this->filter->filter_type = $filterType;
            $this->filter->use_default_stream_sets = "true";
            $this->filterSettings = $this->filter->addChild('filter_settings');
            if( $indexDuration != null ){
                $this->filterSettings->index_duration = $indexDuration;
            }
        }
    }

    class HLSEncryption extends Filter {
        public function __construct( $outputUrl=null, $parentFilter ) {
            parent::__construct("hls_encryption", null, $outputUrl,$parentFilter );
            $this->filterSettings->encryption_type = "AES-128";
            $this->filterSettings->key_rotation_count = 3;
            $this->filterSettings->constant_iv = "";
            $this->filterSettings->key_format = "";
            $this->filterSettings->key_format_versions = "";
            $this->filterSettings->key_id = "";
            $this->filterSettings->keyprovider_type = "self_generated";
            $this->filterSettings->server_url = "";
            $this->filterSettings->service_id = "";
            $this->filterSettings->content_key_base64 = "";
            $this->filterSettings->content_key_hex = "";
            $this->filterSettings->license_url = "";
            $this->filterSettings->ui_license_url = "";
            $this->filterSettings->custom_attributes = "";
            $this->filterSettings->repeat_ext_x_key = "false";
            $keyProviderSettings = $this->filterSettings->addChild("keyprovider_settings");
            $keyProviderSettings->common_key = "false";
            $keyProviderSettings->key_prefix = "https://201.31.12.36";
        }
    }

    class HLSPackage extends Filter {
        public function __construct($indexDuration,$outputUrl=null, $parentFilter=null, $playlistType="EVENT") {
            parent::__construct("hls_package", $indexDuration, $outputUrl,$parentFilter );
            $this->filterSettings->segment_duration             = 10;
            $this->filterSettings->playlist_type                = $playlistType;
            $this->filterSettings->avail_trigger                = "all";
            $this->filterSettings->ad_markers                   = "none";
            $this->filterSettings->broadcast_time               = "false";
            $this->filterSettings->ignore_web_delivery_allowed  = "false";
            $this->filterSettings->ignore_no_regional_blackout  = "false";
            $this->filterSettings->enable_blackout              = "false";
            $this->filterSettings->enable_network_end_blackout  = "false";
            $this->filterSettings->network_id                   = "";
            $this->filterSettings->include_program_date_time    = "false";
            $this->filterSettings->program_date_time_interval   = "";
        }
    }
    
    class HDSPackage extends Filter {
        public function __construct($indexDuration, $outputUrl=null, $parentFilter=null) {
            parent::__construct("hds_package", $indexDuration, $outputUrl,$parentFilter );
            $this->filterSettings->fragment_duration            = 10;
            $this->filterSettings->external_bootstrap           = "true";
            $this->filterSettings->avail_trigger                = "all";
            $this->filterSettings->ad_markers                   = "none";
            $this->filterSettings->broadcast_time               = "false";
            $this->filterSettings->ignore_web_delivery_allowed  = "false";
            $this->filterSettings->ignore_no_regional_blackout  = "false";
            $this->filterSettings->absolute_timestamps          = "false";
        }
    }

    class MSSPackage extends Filter {
        public function __construct($indexDuration, $outputUrl=null, $parentFilter=null) {
            parent::__construct("mss_package", $indexDuration, $outputUrl,$parentFilter );
            $this->filterSettings->fragment_duration = 2;
            $this->filterSettings->enable_events = "true";

        }
    }

    class DashISOPackage extends Filter {
        public function __construct($indexDuration,$outputUrl=null, $parentFilter=null) {
            parent::__construct("dash_iso_package", $indexDuration, $outputUrl,$parentFilter );
            $this->filterSettings->fragment_duration = 2;
            $this->hbbtv = "";
            $this->min_update_period = 30;
            $this->suggested_presentation_delay = 25;
        }
    }
        
// $channel = new stdClass();
// $channel->live_event_id = 35;
// $channel->inputURI = "Dummy/Dummy";
// $channel->dvr = true;
// $channel->https = false;
// $channel->premium = true;
// $content = DeltaContents::getContentsForEvent($channel);
// print_r($content);


//  DeltaContents::getContentsByFolder('/data/server/drive/watchfolders/Cliente1/')
//  foreach( DeltaContents::getContentsByFolder("/data/server/drive/watchfolders/Cliente1/") as $content ) {
//      var_dump($content);
//  }

//          print_r(DeltaContents::getContentsFromJob(171));
?>
