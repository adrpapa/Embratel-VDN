<?php

require_once "CDSM.php";

// ** Class ContentOrigin
// Permite criar e deletar CDMS Content Origin
// **
class ContentOrigin extends CDSM {
	protected $origin;
	protected $fqdn;
	 
	public function __construct( $id,$name=null,$origin=null,$fqdn=null,$description=null ) {
		$this->optional_params_names = array(
				"contentBasedRouting",
				"nasFile",
				"wmtAuth",
				"httpAuthType",
				"httpAuthHeader",
				"httpAuthSharedKey",
				"httpAuthHeaderPrefix",
				"httpAuthSharedSecKey"

		);
		$this->id = $id;
		$this->name = $name;
		$this->origin = $origin;
		$this->fqdn = $fqdn;
		$this->description = $description;

		parent::__construct();
		$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
	}

	public function create() {
		if ( is_null($this->name) || is_null($this->origin) || is_null($this->fqdn) ||
				is_null($this->description) )
		{
			throw new invalidargumentexception("ContentOrigin::create() Name,origin,fqdn and description must not be null");
		}

		$this->action = "createContentOrigin";
		$this->params = "&name=" . $this->name;
		$this->params .= "&origin=" . $this->origin;
		$this->params .= "&fqdn=" . $this->fqdn;
		$this->params .= "&description=" . urlencode($this->description);
		parent::create();
	}

	public function delete() {
		$this->action = "deleteContentOrigins";
		$this->params = "&contentOrigin=" . $this->id;
		parent::delete();
	}

	public function update() {
	}
}

?>