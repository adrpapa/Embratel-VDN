<?php
    require_once "configConsts.php";
    require_once "elementalRest.php";
    require_once "deltaInput.php";
    require_once "preset.php";
    require_once "utils.php";

    /*
    ** Classe que cria e controla live events
    */
    class JobVOD {
        /*
        ** cria objeto LiveEvent, e monta XML para inclusão do mesmo
        */
    	public static function newJobVOD( $name, $file_input_uri, $clientID, $level, $presets=NULL ) {
    		$templateID = $level === 'premium'
    				? ConfigConsts::VOD_TEMPLATE_PREMIUM
    				: ConfigConsts::VOD_TEMPLATE_STANDARD;
    	
    		$job = new self();
    		$job->name = cleanName($name);
    		$job->clientID = cleanClientID($clientID);
    		$job->xml = JobVOD::getElementalRest()->getTemplate($templateID, "ElementalVOD");
    		if ( !is_null($presets) ) {
    			$job->xml = $presets->customizePresets( $job->xml );
    		}
    		$job->xml->name = $name;
    		$job->xml->file_input->uri = $file_input_uri;
    		$job->xml->output_group->output->udp_settings->destination->uri = ConfigConsts::DELTA_WF_INCOMMING_URI . '/' . $clientID . '/' . $level;
    		print $job->xml->asXml().'\n';
    		$job->setPropertiesFromXML(JobVOD::getElementalRest()->postRecord(null, null, $job->xml));
    		return( $job );
    	}    	

    	// cria objeto LiveEvent para perfil Standard, e monta XML para inclusão do mesmo
    	public static function newStandardJobVOD( $name, $file_input_uri, $clientID ) {
    		return JobVOD::newJobVOD( $name, $file_input_uri, $clientID, 'std' );
    	}
    	
    	// cria objeto LiveEvent para perfil Premium, e monta XML para inclusão do mesmo
    	public static function newPremiumJobVOD( $name, $file_input_uri, $clientID ) {
    		return JobVOD::newJobVOD( $name, $file_input_uri, $clientID, 'prm' );
    	}
    	
    	public static function jobVODFromXML( $xml_job ) {
    		$job = new self();
    		$job->setPropertiesFromXML( $xml_job );
    		return $job;
    	}
    	
    	public static function getJobVODById($id) {
    		return JobVOD::jobVODFromXML(
    				JobVOD::getElementalRest()->restGet($id)
    				);
    	}
    	
    	public static $elementaRest = null;
    	
    	public static function getElementalRest() {
    		if (JobVOD::$elementaRest == null){
    			JobVOD::$elementaRest = new ElementalRest(ConfigConsts::VOD_CONDUCTOR_HOST, 'jobs');
    		}
    		return JobVOD::$elementaRest;
    	}
    	
    	public static function getJobList( $id="", $filter="" ) {
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
    		return JobVOD::getElementalRest()->restGet($id, $filter);
    	}
    	
    	public static function getStatus( $id ) {
    		return JobVOD::getElementalRest()->restCall($id=$id, $command='status');
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
    		$this->setPropertiesFromXML(JobVOD::getElementalRest()->restGet($this->id));
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
    		$job = JobVOD::getJobList($this->id, "filter-=archived");
    		var_dump($job);
    		return $job->id == $this->id;
    	}
    	
    	public static function delete($id) {
    		$job = JobVOD::getJobVODById($id);
    		printf("%s - %s %s\n",$job->id, $job->name, $job->status);
    		while($job->isStatusPending()) {
    			$job->cancel();
    			$job = JobVOD::getJobVODById($id);
    		}
    		while(! $job->isStatusArchived() ) {
    			$job->archive();
    			$job = JobVOD::getJobVODById($id);
    		}
    		# LiveEvent::getElementalRest()->restDelete($liveEvent->id);
    	}
    	
    	public function start() {
    		JobVOD::getElementalRest()->postRecord($this->id, "start");
    	}
    	
    	public function stop() {
    		JobVOD::getElementalRest()->postRecord($this->id, "stop");
    		$this->refresh();
    	}
    	
    	public function cancel() {
    		JobVOD::getElementalRest()->postRecord($this->id, "cancel");
    		$this->refresh();
    	}
    	
    	public function archive() {
    		JobVOD::getElementalRest()->postRecord($this->id, "archive");
    	}
    }
?>
