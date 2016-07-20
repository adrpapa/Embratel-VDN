<?php
	require_once "aps/2/runtime.php";
    require_once "configConsts.php";
    require_once "elementalRest.php";
    require_once "deltaInput.php";
    require_once "preset.php";
    require_once "utils.php";

    /*
    ** Classe que cria e controla job (Elemental Server)
    */
    class JobVOD {
        /*
        ** cria objeto JOB, e monta XML para inclusão do mesmo
        */
    	public static function newJobVOD( $name, $file_input_uri, $clientID, $level, Presets $presets=NULL ) {
			\APS\LoggerRegistry::get()->setLogFile("logs/jobs.log");
			$axCli = cleanClientID($clientID);
			$axNam = cleanName($name);
			$axVideoName = pathinfo($file_input_uri, PATHINFO_FILENAME);
			
			$tpl = ConfigConsts::TEMPLATE_PATH."/jobs_".$level.".xml";
			if( ! file_exists($tpl) )
				throw new Exception("File $tpl does not exist \n");
			$job = simplexml_load_file($tpl);
    		$job->input->name = $name;
    		$job->input->file_input->uri = $file_input_uri;
    		if ( !is_null($presets) ) {
    			$job = $presets->customizePresets( $job->name, $job );
    		}
    		$job->output_group->apple_live_group_settings->destination->uri = ConfigConsts::DELTA_WF_INCOMMING_URI . '/' . $clientID .'/'.$axVideoName.'/';
    		\APS\LoggerRegistry::get()->info( $job->asXml().'\n');
    		$jobVOD = new self();
    		$jobVOD->setPropertiesFromXML(JobVOD::getElementalRest()->postRecord(null, null, $job));
    		return( $jobVOD );
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
     	
    	public static function getElementalRest() {
			return new ElementalRest(ConfigConsts::VOD_CONDUCTOR_HOST, 'jobs');
    	}
    	   	
    	public static function getJobList( $id="", $filter="" ) {
    		/** Valores possíveis para filtro de JOB�s:
    		pending: Jobs in the pending state 
    		active: Jobs in the preprocessing, running or postprocessing state 
    		pre: Jobs in the preprocessing state running Jobs in the running state 
    		post: Jobs in the postprocessing state 
    		complete: Jobs in the complete state 
    		cancelled: Jobs in the cancelled state 
    		error: Jobs in the error state 
    		archived: Jobs that have been archived
    		**/
    		return JobVOD::getElementalRest()->restGet($id, $filter);
    	}
    	
    	public static function getStatus( $id ) {
    		return JobVOD::getElementalRest()->restCall($id=$id, $command='status');
    	}
    	
    	public function setPropertiesFromXML( $event ) {
    		$this->name = $event->name."";
    		$this->inputURI = $event->input->file_input->uri."";
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
    	
    	public function isStatusComplete() {
    		return $this->status == 'complete';
    	}
    	
    	public function isStatusCancelled() {
    		return $this->status == 'cancelled';
    	}

    	public function isStatusError() {
    		return $this->status == 'error';
    	}    	    	
    	
    	public function isStatusArchived() {
    		$job = JobVOD::getJobList(null, "filter=archived");
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
    		
    		$job->archive();
    		
    		/**
    		while(! $job->isStatusArchived() ) {
    			$job->archive();
    			$job = JobVOD::getJobVODById($id);
    		}
    		**/
    		# LiveEvent::getElementalRest()->restDelete($liveEvent->id);
    	}
    	
    	public function cancel() {
    		JobVOD::getElementalRest()->postRecord($this->id, "cancel", "<cancel></cancel>");
    		$this->refresh();
    	}
    	
    	public function archive() {
    		JobVOD::getElementalRest()->postRecord($this->id, "archive", "<archive></archive>");
    	}
    }
    
//    ElementalRest::$auth = new Auth( 'elemental','elemental' );
//	$status=JobVOD::getElementalRest()->restCall($id=95);
//	print_r(output_group$status);


// 	$jobVOD = JobVOD::newJobVOD( 'big_buck_bunny', 'http://www.sample-videos.com/video/mp4/720/big_buck_bunny_720p_1mb.mp4',
// 				   'Client_000004', 'std' );
// 	print_r($jobVOD);
?>
