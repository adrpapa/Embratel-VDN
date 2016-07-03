<?php

require_once "configConsts.php";
require_once "elementalRest.php";

// ** Classe CDSM
// Encapsula as funcionalidades principais para manutenчуo de Service no CDMS
// **
abstract class CDSM {
	protected $id;
	protected $name;
	protected $description;
	protected $status;
	protected $message;
	protected $cdmAddress = ConfigConsts::CDMS_ADDRESS;
	protected $cdmPort = ConfigConsts::CDMS_PORT;
	protected $taskAPI;
	protected $action;
	protected $urlString;
	protected $userName = ConfigConsts::CDMS_USER;
	protected $password = ConfigConsts::CDMS_PWD;
	protected $params;
	protected $optional_params_names;

	public function __construct() {
		$this->urlString = "https://" . $this->cdmAddress . ":" . $this->cdmPort . "/servlet/";
	}

	protected function setInternalStatus($xml_result) {
		if ( count($xml_result->xpath("/*/message/@status")) > 0 ) {
			$this->status = $xml_result->xpath("/*/message/@status")[0];
			$this->setStatus( $this->status->__toString() );
		}
		if ( count($xml_result->xpath("/*/message/@message")) > 0 ) {
			$this->message = $xml_result->xpath("/*/message/@message")[0];
			$this->setMessage( $this->message->__toString() );
		}
	}

	protected function setInternalID($xml_result) {
		if ( count($xml_result->xpath("/*/record/@Id")) > 0 ) {
			$this->id = $xml_result->xpath("/*/record/@Id")[0];
			$this->setID( $this->id->__toString() );
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

	public function create() {
		$this->exec();
		return( $this->getStatus() == "success" );
	}

	public function delete() {
		$this->exec();
		return( $this->getStatus() == "success" );
	}

	public function update() {
		$this->exec();
		return( $this->getStatus() == "success" );
	}

	public function exec() {
		$optional_params = "";
		foreach ($this->optional_params_names as $k => $v) {
			if ( isset($this->{$v}) && !is_null($this->{$v}) ) {
				$optional_params .= "&" . $v . "=" . $this->{$v};
			}
		}
			
		$credentials = $this->userName . ':' . $this->password;
		$this->urlString = "https://" . $this->cdmAddress . ":" . $this->cdmPort . "/servlet/";
		$this->urlString .= $this->taskAPI . "?action=" . $this->action . $this->params . $optional_params;
			
		try {
			$curl_obj = new ElementalRest($this->cdmAddress,'servlet');
			$curl_obj->uri = $this->urlString;
			$xml_result = $curl_obj->restCDSM( base64_encode($credentials) );
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