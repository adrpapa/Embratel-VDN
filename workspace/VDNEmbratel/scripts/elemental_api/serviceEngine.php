<?php

require_once "CDSM.php";
require_once "configConsts.php";

class ServiceEngine extends CDSM {
	
	protected $xml;

	public function __construct( ) {
		$this->optional_params_names = array(
			"param",
			"name"
		);
		parent::__construct();
		$this->loadXML();
		$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
	}	
	
	protected function loadXML() {
		$serviceEngineFilename = dirname(__FILE__).'/../'.ConfigConsts::TEMPLATE_PATH . '/service_engines.xml';

		if ( file_exists($serviceEngineFilename) ) {
			$this->xml = simplexml_load_file( $serviceEngineFilename );
			if ( $this->xml === FALSE ) {
				throw new Exception("Service Engine XML file is invalid.");
			}
		}
		else {
			throw new Exception("Service Engine XML file does not exist: ".$serviceEngineFilename);
		}
	}
	
	public function create($data=null) {
	}
	
	public function delete($id) {
	}
	
	public function update($id,$data=null) {
	}
	
	public function get($name) {
		$this->taskAPI = "com.cisco.unicorn.ui.ListApiServlet";
		$this->action = "getSEs";
		$list_of_ses = "";
		if ( !is_null($name) ) {
			$list_of_ses = $name;
		}
		else {
			$list_of_ses  = implode(",", $this->getEnableSEsName() );			
		}
		
		$this->param  = "name=".$list_of_ses;
		
		return ( parent::get($name) );
	}	
	
	public function getEnableSEsName() {
		$array_se = array();
		$ses = $this->xml->xpath("/SESettings/service_engines/SE");
		foreach($ses as $k => $v) {
			$attr = $v->attributes();
			if ( $attr["enable"]->__toString() == "true" ) {
				$array_se[] = $v->__toString();	
			}
		}
		
		return ( $array_se );
	}
	
	public function getContentAcquirer() {
		$ca = $this->xml->xpath("/SESettings/content-acquirer");
		return( $ca[0][0]->__toString() );
	}

	public static function getSEList() {
    	$se = new ServiceEngine();
    	if ( $se->get(null) ) {
    		if ( $se->get( $se->getContentAcquirer() ) ) 
	    		return $se->getID();
    	}
		return false;
	}

}

?>
