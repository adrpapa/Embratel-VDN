<?php
    require_once "utils.php";
    require_once "preset.php";
    require_once "deltaInput.php";
    require_once "configConsts.php";
    require_once "elementalRest.php";

    /*
    ** Classe que cria e controla live events
    */
    class LiveEvent {
        /*
        ** cria objeto LiveEvent, e monta XML para inclusão do mesmo
        */
        public static function newLiveEvent( $name, $clientID, $level, Presets $presets=NULL, $DVR=false, $HTTPS=false) {
            $axCli = cleanClientID($clientID);
            $axNam = cleanName($name);
            $axStream = $axCli.'/'.$axNam;
            
            $selectedNode='';
            $nodeIpAddress='';
            $lightestCount='a';
            // Calcula automaticamente o nó Live onde será gerado o evento
            $nodes = LiveEvent::getNodesRest()->restGet();
            foreach( $nodes->xpath('node') as $node ){
                if( $node->product == "Live" && $node->status == 'active' ) {
                    if( $node->running_count < $lightestCount ) {
                        $toks = explode('/',$node['href']."");
                        $selectedNode = $toks[count($toks)-1];
                        echo "\n\n**** Selected $node->hostname ***** \n\n";
                        $nodeIpAddress = ConfigConsts::$LIVE_NODES[$node->hostname.""];
                    }
                }
            }
            if( $selectedNode == '' ) {
                throw new invalidargumentexception("No active live node exists in Conductor Live ".LIVE_CONDUCTOR_HOST);
            }
            
            $tpl = dirname(__FILE__) . '/../' . ConfigConsts::TEMPLATE_PATH."/live_input_filter.xml";
            $inputFilter = new SimpleXmlElement("<input_filter></input_filter>");
            $inputFilter->filter_type = "webdav_input";
            $inputFilter->label =  $axCli.'_'.$axNam;
            $filterSettings = $inputFilter->addChild('filter_settings');
//             $inputFilter->filter_settings->seconds_to_keep = ($DVR ? 7800 : 600);
            $filterSettings->template_id = "";
            $filterSettings->content_window_type = "packager_controlled";
            $filterSettings->storage_location = ConfigConsts::DELTA_WEBDAV_STORAGE_ROOT."/".$axStream."/";
            $filterSettings->aws_credential_id;
            $filterSettings->input_user_id = 1;
            $filterSettings->relative_uri = $axStream;
            $filterSettings->vod_content = 'false';
//             print_r($inputFilter);
            $inputFilter = DeltaInputFilter::getElementalRest()->postRecord(null, null, $inputFilter);
            $inputFilterId = idFromHref($inputFilter);
            
//             print_r($inputFilter);
            echo "\n\nCreated Input filter with ID = $inputFilterId\n\n";

            try {
                $tpl = dirname(__FILE__) . '/../' . ConfigConsts::TEMPLATE_PATH."/live_events_".$level.".xml";
                $live_event = simplexml_load_file($tpl);
                $live_event->name = $axNam;
                $live_event->input->hot_backup_pair="false";
                $live_event->node_id = $selectedNode;
                $live_event->input->network_input->uri = ConfigConsts::LIVE_NODE_URL.$axStream;
                $live_event->failure_rule->backup_rule = "none";
                $live_event->output_group->apple_live_group_settings->destination->uri = ConfigConsts::DELTA_WEBDAV_URI_ROOT.$axStream."/".$axNam;
                if ( !is_null($presets) ) {
                    $live_event = $presets->customizePresets( $live_event->name, $live_event );
                }
                $live_event = LiveEvent::getElementalRest()->postRecord(null, null, $live_event);
                $liveEvent = LiveEvent::liveEventFromXML($live_event);
                $liveEvent->start($liveEvent->id);
                $liveEvent->node_id = $selectedNode;
                $liveEvent->inputURI = "rtmp://".$nodeIpAddress.":1935/".$axStream;
                $liveEvent->live_node = $nodeIpAddress;
                $liveEvent->inputFilterID = $inputFilterId;
                return( $liveEvent );
            }
            catch( Exception $e ) {
                echo "Error creating live event\n";
                echo $e->getMessage()."\n";
                echo $live_event->asXML();
                DeltaInputFilter::getElementalRest()->restDelete($inputFilterId);
                throw $e;
            }
        }

        public static function liveEventFromXML( $event ) {
            $liveEvent = new self();
            $liveEvent->setPropertiesFromXML($event);
            return $liveEvent;
        }

        public static function getLiveEventById($id) {
            return LiveEvent::liveEventFromXML(
                    LiveEvent::getElementalRest()->restGet($id)
                );
        }

        public static function getElementalRest() {
            return new ElementalRest(ConfigConsts::LIVE_CONDUCTOR_HOST, 'live_events');
        }

        public static function getNodesRest() {
            return new ElementalRest(ConfigConsts::LIVE_CONDUCTOR_HOST, 'nodes');
        }

        public static function getEventList( $id="", $filter="" ) {
        // Valores possíveis para filtro de eventos Live:
        // pending      Live Events in the pending state
        // active       Live Events in the preprocessing, running or postprocessing state
        // pre          Live Events in the preprocessing state
        // running      Live Events in the running state
        // post         Live Events in the postprocessing state
        // complete     Live Events in the complete state
        // cancelled    Live Events in the cancelled state
        // error        Live Events in the error state
        // archived     Live Events that have been archived
            return LiveEvent::getElementalRest()->restGet($id, $filter);
        }

        public static function getStatus( $id ) {
            $eventXml = LiveEvent::getElementalRest()->restCall($id=$id, $command='status');
            $event = new self();
            $event->id = $id;
            $event->status = $eventXml->status;
            $event->created_at = $eventXml->xpath('audit_messages/audit/created_at');
            return $event;
        }

        public function setPropertiesFromXML( $event ) {
            $this->name = $event->name."";
            $this->inputURI = $event->input->network_input->uri."";
            // Extraimos o cliente da uri ex: rtmp://localhost:1935/sgr/sgrstream
            $toks = explode(':',$this->inputURI);
            if( count($toks) > 2 ) {
                $ax = explode('/',$toks[2]); 
                $this->clientID = $ax[1];
            } else {
                $this->clientID = "";
            }
            $ax =$event->xpath('status');
            $this->status =$ax [0]."";
            $this->id = idFromHref($event);
        }
        
        public function isStatusArchived($id) {
            $event = LiveEvent::getEventList($id, "filter-=archived");
//             var_dump($event);
            return $event->id == $this->id;
        }

        public static function delete($id) {
            $liveEvent = LiveEvent::getLiveEventById($id);
            printf("%s - %s %s\n",$liveEvent->id, $liveEvent->name, $liveEvent->status);
            if( $liveEvent->status == "running") {
                $liveEvent->stop($id);
                $liveEvent = LiveEvent::getLiveEventById($id);
            }
            if( $liveEvent->status == "pending") {
                $liveEvent->cancel($id);
                $liveEvent = LiveEvent::getLiveEventById($id);
            }
            try {
                $liveEvent->archive($id);
            } catch( Exception $fault ) {
            }
            # LiveEvent::getElementalRest()->restDelete($liveEvent->id);
        }

        public static function start($id) {
            $liveEvent = LiveEvent::getStatus($id);
            if( $liveEvent->status == "complete" || $liveEvent->status == "cancelled" ) {
                LiveEvent::getElementalRest()->postRecord($id, "reset");
                $liveEvent = LiveEvent::getStatus($id);
            }    
            if( $liveEvent->status == "pending") {
                LiveEvent::getElementalRest()->postRecord($id, "start");
            }    
        }

        public static function stop($id) {
            LiveEvent::getElementalRest()->postRecord($id, "stop");
        }

        public static function cancel($id) {
            LiveEvent::getElementalRest()->postRecord($id, "cancel");
        }

        public static function archive($id) {
            LiveEvent::getElementalRest()->postRecord($id, "archive");
        }
    }
        
// LiveEvent::delete( 47 );
// $event = LiveEvent::newLiveEvent( "LiveEventTest1", 'ClienteTesteAPI', 'std', $presets=NULL, $DVR=false, $HTTPS=false);
// echo "\n\nID = $event->id;\n";
// echo "Name = $event->name\n";
// echo "Status = $event->status\n";
// echo "Input URI = $event->inputURI\n";
// echo "Live Node: $event->node_id\n";
// echo "Input Filter ID = $event->inputFilterID\n\n";


// $event =  LiveEvent::getElementalRest()->restGet(35);
// $fixLen = strlen(ConfigConsts::DELTA_WEBDAV_URI_ROOT);
// $destpath = substr($event->output_group->apple_live_group_settings->destination->uri."",$fixLen);
// $fixLen = strlen($destpath) * -1;
// $destpath = substr($event->output_group->apple_live_group_settings->destination->uri."",$fixLen);
// echo "Destination = $destpath\n";

// $event = LiveEvent::getLiveEventById();
// $event->stop();
// $event->archive();
// $event->delete();

//         $event = LiveEvent::getStatus(36);
//         $creation_time=$event->xpath('audit_messages/audit/created_at');
//         echo "Status = $event->status\n";
//         echo "Count(creation_time) = ".count($creation_time)."\n";
//         echo "Creation time: $creation_time[0]\n";

//         2016-08-17 15:45:46-03:00
//         $creationTime = DateTime::createFromFormat("Y-m-d\TH:i:sT",$creation_time[0]);
//         $now = new DateTime();
//         echo $now->format('Y-m-d\TH:i:sP')."\n";
//         $interval = ($now->getTimestamp() - $creationTime->getTimestamp()) / 60;
//         var_dump($interval);


//LiveEvent::newLiveEvent( "LiveEventTest1", "ClienteTesteAPI", "std", $presets=NULL );
?>
