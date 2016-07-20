<?php

require_once "CDSM.php";

// ** Class ContentOrigin
// Permite criar e deletar CDMS Content Origin
// **
class ContentOrigin extends CDSM {

	protected $internal_name;
	protected $internal_origin;
	protected $internal_fqdn;
	protected $internal_description;
	
	public function __construct( $name=null,$origin=null,$fqdn=null,$description=null ) {
		$this->optional_params_names = array(
				"contentBasedRouting",
				"nasFile",
				"wmtAuth",
				"httpAuthType",
				"httpAuthHeader",
				"httpAuthSharedKey",
				"httpAuthHeaderPrefix",
				"httpAuthSharedSecKey",
				"name",		
				"origin",	
				"contentOrigin",
				"fqdn",		
				"description",	
				"param"

		);
		
		$this->internal_name = $name;
		$this->internal_origin = $origin;
		$this->internal_fqdn = $fqdn;
		$this->internal_description = is_null($description) ? null:urlencode($description);
		/*
		$this->name = $name;
		$this->origin = $origin;
		$this->fqdn = $fqdn;
		$this->description = is_null($description) ? null:urlencode($description);
		*/
		parent::__construct();
		$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
	}

	public function create($data=null) {
		$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
		$this->action = "createContentOrigin";
		$this->name = $this->internal_name;
		$this->origin = $this->internal_origin;
		$this->fqdn = $this->internal_fqdn;
		$this->description = $this->internal_description;

		if ( is_null($this->name) || is_null($this->origin) || is_null($this->fqdn) ||
				is_null($this->description) )
		{
			throw new invalidargumentexception("ContentOrigin::create() Name,origin,fqdn and description must not be null");
		}
		
		return ( parent::create($data) );
	}

	public function delete($id) {
		$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
		$this->action = "deleteContentOrigins";
		$this->contentOrigin=$id;
		return ( parent::delete($id) );
	}

	public function update($id,$data=null) {
		$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
		$this->action = "modifyContentOrigin";
		
		$this->name = $this->internal_name;
		$this->origin = $this->internal_origin;
		$this->fqdn = $this->internal_fqdn;
		$this->description = $this->internal_description;
		$this->contentOrigin=$id;
		
		return ( parent::update($id,$data) );		
	}
	
	public function get($name) {
		$this->taskAPI = "com.cisco.unicorn.ui.ListApiServlet";
		$this->action = "getContentOrigins";
		$this->param  = $name;
		return ( parent::get($name) );
	}	
}

?>