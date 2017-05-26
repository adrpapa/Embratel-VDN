<?php

require_once "configConsts.php";
require_once "elementalRest.php";

// ** Classe CDSM
// Encapsula as funcionalidades principais para manutenção de Service no CDMS
// **
abstract class CDSM {
	protected $xml_result;
	protected $internal_id;
	protected $internal_status;
	protected $internal_message;
	//protected $cdmAddress;
	//protected $cdmPort;
	protected $taskAPI;
	protected $action;
	protected $urlString;
	//protected $cdmUserName;
	//protected $cdmPassword;
	protected $params;
	protected $optional_params_names;

	public function __construct() {
		$this->urlString = "https://" . ConfigConsts::$CDMS_ADDRESS .
			 ":" . ConfigConsts::$CDMS_PORT . "/servlet/";
	}

	protected function setInternalStatus() {
		if ( count($this->xml_result->xpath("/*/message/@status")) > 0 ) {
			$r = $this->xml_result->xpath("/*/message/@status");
			$this->setStatus( $r[0]->__toString() );
		}
		if ( count($this->xml_result->xpath("/*/message/@message")) > 0 ) {
			$r = $this->xml_result->xpath("/*/message/@message");
			$this->setMessage( $r[0]->__toString() );
		}
	}

	protected function setInternalID() {
		if ( count($this->xml_result->xpath("/*/record/@Id")) > 0 ) {
			$array_ids = array();
			$r = $this->xml_result->xpath("/*/record/@Id");
			foreach( $r as $k=>$v ) {
				$array_ids[] = $v[0][0]->__toString();
			}
			$this->setID( implode(',',$array_ids) );
		}
	}

	public function searchFieldValue($field_name,$value) {
		if ( count($this->xml_result->xpath("/*/record")) > 0 ) {
			$array_rec = array();
			$r = $this->xml_result->xpath("/*/record");
			foreach( $r as $k=>$v ) {
				if ( isset($v[0][$field_name]) && $v[0][$field_name]->__toString() == $value ) {
					return $v;
				}
			}
		}
		return null;
	}	
	
	protected function setMessage($message) {
		$this->internal_message = $message;
	}
	
	protected function setStatus($status) {
		$this->internal_status = $status;
	}
	
	public function setID($id) {
		$this->internal_id = $id;
	}
	
	public function getID() {
		return $this->internal_id;
	}

	public function getStatus() {
		return $this->internal_status;
	}

	public function getMessage() {
		return $this->internal_message;
	}

	public function create($data=null) {
		$this->exec($data);
		return( $this->getStatus() == "success" );
	}

	public function delete($key) {
		$this->exec();
		return( $this->getStatus() == "success" );
	}

	public function update($key,$data=null) {
		$this->exec($data);
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
			
//		$credentials = $this->cdmUserName . ':' . $this->cdmPassword;
		$credentials = ConfigConsts::$CDMS_USER . ':' . ConfigConsts::$CDMS_PWD;
		$this->urlString = "https://" . ConfigConsts::$CDMS_ADDRESS .
			 ":" . ConfigConsts::$CDMS_PORT . "/servlet/";
		$this->urlString .= $this->taskAPI . "?action=" . $this->action . $optional_params;
		
		try {
			$curl_obj = new ElementalRest(ConfigConsts::$CDMS_ADDRESS,'servlet');
			$curl_obj->uri = $this->urlString;
			$this->xml_result = $curl_obj->restCDSM( base64_encode($credentials),$data );
		} catch(Exception $ex) { 
			print("ops something didn't work as we expected.... sorry.To help you: ".$this->urlString." message: ".$ex->getMessage());
		}
			
		$this->unsetParams();
		
		$this->setInternalStatus();
		$this->setInternalID();
	}
	
	protected function unsetParams() {
		foreach ($this->optional_params_names as $k => $v) {
			if ( isset($this->{$v}) && !is_null($this->{$v}) ) {
				unset($this->{$v});
			}
		}		
	}
}

?>
