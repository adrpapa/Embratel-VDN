<?php
    require_once "utils.php";
    require_once "preset.php";
    require_once "deltaInput.php";
    require_once "deltaOutputTemplate.php";
    require_once "configConsts.php";
    require_once "elementalRest.php";

    // Alteracoes feitas por Adriano

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
            $lightestCount='a';
            $nodes = LiveEvent::getNodesRest()->restGet();
            foreach( $nodes->xpath('node') as $node ){
                if( $node->product == "Live" && $node->status == 'active' ) {
                    if( $node->running_count < $lightestCount ) {
                        $toks = explode('/',$node['href']."");
                        $selectedNode = $toks[count($toks)-1];
                    }
                }
            }
            if( $selectedNode == '' ) {
                throw new invalidargumentexception("No active live node exists in Conductor Live ".LIVE_CONDUCTOR_HOST);
            }
            /*
                * Criação do Output template
                */
            $tpl = ConfigConsts::TEMPLATE_PATH."/live_output_templates_".$level.".xml";
            if( ! file_exists($tpl) )
                throw new Exception("File $tpl does not exist \n");
            $outputTemplate = simplexml_load_file($tpl);
            $outputTemplate->name = $axNam;
            foreach( $outputTemplate->xpath("/*/filter") as $filter ) {
                $filter->output_url = $axStream;
                if($DVR)
                    $filter->filter_settings->index_duration='7200';
                if( $filter->filter_type == 'hls_package' )
                    $filter->filter_settings->playlist_type = 'EVENT';
            }
            $outputTemplate = DeltaOutputTemplate::getElementalRest()->postRecord(null, null, $outputTemplate);

            try {
                $tpl = ConfigConsts::TEMPLATE_PATH."/live_input_filter.xml";
                $inputFilter = simplexml_load_file($tpl);
                $inputFilter->label =  $axStream;
                $inputFilter->filter_settings->seconds_to_keep = ($DVR ? 7800 : 600);
                $inputFilter->filter_settings->relative_uri = $axStream;
                $inputFilter->filter_settings->template_id = $outputTemplate->id;
                $inputFilter->filter_settings->storage_location = DELTA_WEBDAV_STORAGE_ROOT.$axStream;
                $inputFilter->filter_settings->vod_content = 'false';
                $inputFilter = DeltaInputFilter::getElementalRest()->postRecord(null, null, $inputFilter);

                try {
                    


                    $tpl = ConfigConsts::TEMPLATE_PATH."/live_events_".$level.".xml";
                    $live_event = simplexml_load_file($tpl);
                    $live_event->name($name);
                    $live_event->hot_backup_pair="false";
                    $live_event->input->network_input->uri = ConfigConsts::LIVE_NODE_URL.$axCli.'/'.$axNam;
                    $live_event->failure_rule->backup_rule = "none";
                    $live_event->output_group->apple_live_group_settings->destination->uri = DELTA_WEBDAV_URI_ROOT.$axStream;
                    $live_event = LiveEvent::getElementalRest()->postRecord(null, null, $live_event);
                    
                    $liveEvent = LiveEvent::setPropertiesFromXML($live_event);

                // TODO Calcular automaticamente o nó Live onde será gerado o evento
                    $liveEvent->live_node = ConfigConsts::LIVE_NODE_URL;
                    $liveEvent->inputURI = $liveEvent->live_node;
                    $liveEvent->inputURI .= $liveEvent->clientID.'/';
                    $liveEvent->inputURI .= preg_replace('/\s+/', '', $liveEvent->name);
                // TODO Verificar como será criado no delta - se haverá conductor qual URL
                    $liveEvent->outputURI = "udp://".ConfigConsts::DELTA_HOST.':'.$liveEvent->udpPort;
                    $liveEvent->xml = LiveEvent::getElementalRest()->getTemplate(
                            $templateID, "ElementalLive".$templateID);
                    $liveEvent->xml->name = $liveEvent->name;
                    $liveEvent->xml->input->network_input->uri = $liveEvent->inputURI;
                    $liveEvent->xml->output_group->output->udp_settings->destination->uri = $liveEvent->outputURI;
                    print $liveEvent->xml->asXml().'\n';
                    $liveEvent->setPropertiesFromXML();
                    $liveEvent->inputFilterID = $inputFilter->id;
                    $liveEvent->outputTemplateID = $inputFilter->template_id;
                    return( $liveEvent );
                }
                catch( Exception $e ) {
                    DeltaInputFilter::getElementalRest()->restDelete($inputFilter->id);
                }
            }
            catch( Exception $e ) {
                DeltaOutputTemplate::getElementalRest()->restDelete($outputTemplate->id);
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
            return LiveEvent::getElementalRest()->restCall($id=$id, $command='status');
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
            $this->href = $event["href"]."";      
            $toks = explode('/',$this->href);
            $this->id = $toks[count($toks)-1];
        }
        
        public function refresh(){
            $this->setPropertiesFromXML(LiveEvent::getElementalRest()->restGet($this->id));
        }
        
        public function isStatusPending() {
            return $this->status == 'pending';
        }

        public function isStatusCancelled() {
            return $this->status == 'cancelled';
        }

        public function isStatusRunning() {
            return $this->status == 'running';
        }

        public function isStatusArchived() {
            $event = LiveEvent::getEventList($this->id, "filter-=archived");
            var_dump($event);
            return $event->id == $this->id;
        }

        public static function delete($id) {
            $liveEvent = LiveEvent::getLiveEventById($id);
//             printf("%s - %s %s\n",$liveEvent->id, $liveEvent->name, $liveEvent->status);
            while($liveEvent->isStatusPending()) {
                $liveEvent->cancel();
                $liveEvent = LiveEvent::getLiveEventById($id);
            }
            while(! $liveEvent->isStatusArchived() ) {
                $liveEvent->archive();
                $liveEvent = LiveEvent::getLiveEventById($id);
            }
            # LiveEvent::getElementalRest()->restDelete($liveEvent->id);
        }

        public function start() {
            LiveEvent::getElementalRest()->postRecord($this->id, "start");
        }

        public function stop() {
            LiveEvent::getElementalRest()->postRecord($this->id, "stop");
            $this->refresh();
        }

        public function cancel() {
            LiveEvent::getElementalRest()->postRecord($this->id, "cancel");
            $this->refresh();
        }

        public function archive() {
            LiveEvent::getElementalRest()->postRecord($this->id, "archive");
        }
    }
// 	$event = LiveEvent::newLiveEvent( "LiveEventTest1", 'ClienteTesteAPI', 'std', $presets=NULL, $DVR=false, $HTTPS=false);
// 	var_dump($event);
?>
