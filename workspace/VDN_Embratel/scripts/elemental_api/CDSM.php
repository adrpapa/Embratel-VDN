<?php

require_once "configConsts.php";
require_once "elementalRest.php";

// ** Classe CDSM
// Encapsula as funcionalidades principais para manutenчуo de Service no CDMS
// **
abstract class CDSM {
	public    $id;
	protected $status;
	protected $message;
	protected $cdmAddress = ConfigConsts::CDMS_ADDRESS;
	protected $cdmPort = ConfigConsts::CDMS_PORT;
	protected $taskAPI;
	protected $action;
	protected $urlString;
	protected $cdmUserName = ConfigConsts::CDMS_USER;
	protected $cdmPassword = ConfigConsts::CDMS_PWD;
	protected $params;
	protected $optional_params_names;

	public function __construct() {
		$this->urlString = "https://" . $this->cdmAddress . ":" . $this->cdmPort . "/servlet/";
	}

	protected function setInternalStatus($xml_result) {
		if ( count($xml_result->xpath("/*/message/@status")) > 0 ) {
			$r = $this->status = $xml_result->xpath("/*/message/@status");
			$this->status = $r[0];
			$this->setStatus( $this->status->__toString() );
		}
		if ( count($xml_result->xpath("/*/message/@message")) > 0 ) {
			$r = $xml_result->xpath("/*/message/@message");
			$this->message = $r[0];
			$this->setMessage( $this->message->__toString() );
		}
	}

	protected function setInternalID($xml_result) {
		if ( count($xml_result->xpath("/*/record/@Id")) > 0 ) {
			$array_ids = array();
			$r = $xml_result->xpath("/*/record/@Id");
			foreach( $r as $k=>$v ) {
				$array_ids[] = $v[0][0]->__toString();
			}
			$this->id = implode(',',$array_ids);
			$this->setID( $this->id );
		}
	}

	protected function setMessage($message) {
		$this->message = $message;
	}
	
	protected function setStatus($status) {
		$this->status = $status;
	}
	
	public function setID($id) {
		$this->id = $id;
	}
	
	public function getID() {
		return $this->id;
	}

	public function getStatus() {
		return $this->status;
	}

	public function getMessage() {
		return $this->message;
	}

	public function create($data=null) {
		$this->exec($data);
		return( $this->getStatus() == "success" );
	}

	public function delete($key) {
		$this->exec();
		return( $this->getStatus() == "success" );
	}

	public function update($key) {
		$this->exec();
		return( $this->getStatus() == "success" );
	}

	public function get($key) {
		$this->exec();
		return( $this->getStatus() == "success" );		
	}
	
	public function exec($data=null) {
		$optional_params = "";
		foreach ($this->optional_params_names as $k => $v) {
			if ( isset($this->{$v}) && !is_null($this->{$v}) ) {
				$optional_params .= "&" . $v . "=" . $this->{$v};
			}
		}
			
		$credentials = $this->cdmUserName . ':' . $this->cdmPassword;
		$this->urlString = "https://" . $this->cdmAddress . ":" . $this->cdmPort . "/servlet/";
		$this->urlString .= $this->taskAPI . "?action=" . $this->action . $optional_params;
		
		try {
			$curl_obj = new ElementalRest($this->cdmAddress,'servlet');
			$curl_obj->uri = $this->urlString;
			$xml_result = $curl_obj->restCDSM( base64_encode($credentials),$data );
		} catch(Exception $ex) { 
			print("ops something didnДt work as we expected.... sorry.To help you: ".$ex->getMessage());
		}
			
		$this->setInternalStatus($xml_result);
		$this->setInternalID($xml_result);
			
		foreach ($this->optional_params_names as $k => $v) {
			if ( isset($this->{$v}) && !is_null($this->{$v}) ) {
				unset($this->{$v});
			}
		}
	}
}

?>