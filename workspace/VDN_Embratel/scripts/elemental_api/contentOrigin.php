<?php

require_once "CDSM.php";

// ** Class ContentOrigin
// Permite criar e deletar CDMS Content Origin
// **
class ContentOrigin extends CDSM {

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
		
		$this->name = $name;
		$this->origin = $origin;
		$this->fqdn = $fqdn;
		$this->description = is_null($description) ? null:urlencode($description);

		parent::__construct();
		$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
	}

	public function create($data=null) {
		if ( is_null($this->name) || is_null($this->origin) || is_null($this->fqdn) ||
				is_null($this->description) )
		{
			throw new invalidargumentexception("ContentOrigin::create() Name,origin,fqdn and description must not be null");
		}

		$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
		$this->action = "createContentOrigin";

		return ( parent::create($data) );
	}

	public function delete($id) {
		$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
		$this->action = "deleteContentOrigins";
		$this->contentOrigin=$id;
		return ( parent::delete($id) );
	}

	public function update($id) {
		$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
		$this->action = "modifyContentOrigin";
		$this->contentOrigin=$id;
		return ( parent::update($id) );		
	}
	
	public function get($name) {
		$this->taskAPI = "com.cisco.unicorn.ui.ListApiServlet";
		$this->action = "getContentOrigins";
		$this->param  = $name;
		return ( parent::get($name) );
	}	
}

?>