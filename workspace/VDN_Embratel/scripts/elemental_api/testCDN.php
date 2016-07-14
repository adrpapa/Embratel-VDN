<?php
require_once "deliveryService.php";
require_once "contentOrigin.php";
require_once "deliveryServiceGenSettings.php";
require_once "fileMgmt.php";
require_once "serviceEngine.php";

$origin=null;
$ds=null;
$dsgs=null;
$rule=null;

provision();
unprovision();

function provision() {
	$alias = "eventoteste";
	$subscription = "10001";
	$custom_name   = $alias . "-" . $subscription;
	$custom_domain = $alias . "." . $subscription;
	$origin_domain = $custom_domain . ".csi.cds.cisco.com";
	$origin_server = $custom_name . ".obj.osp.embratelcloud.com.br";

    // CREATE CONTENT ORIGIN
	$origin = new ContentOrigin("co-".$custom_name,$origin_server,$origin_domain,"co-".$custom_name);
	if ( !$origin->create() ) {
		print("Error:".$origin->getMessage());
	}

	// CREATE DELIVERY SERVICE
	$ds = createDeliveryService($origin,$custom_name);

	// CREATE DELIVERY SERVICE GENERAL SETTINGS
	$dsgs = createDeliveryServiceGenSettings($origin,$ds);

	// ASSIGN RULE TO DELIVERY SERVICE
	$rule = asssignRule($origin,$ds,$dsgs,$custom_name,$origin_domain);

}

function unprovision(){	 
	$rule = new FileMgmt();
	$rule->delete( $rule->getID() );
	 
	$dsgs = new DeliveryServiceGenSettings();
	$dsgs->delete( $dsgs->getID() );
	 
	$ds = new DeliveryService();
	$ds->delete( $ds->getID() );
	 
	$origin = new ContentOrigin();
	$origin->delete( $origin->getID() );
}

/*********************************************************************
 * Custom Functions
 * Functions to create delivery service, content origin and rule file
 *********************************************************************
 */
function createDeliveryService($origin,$custom_name) {
	$ds = new DeliveryService("ds-".$custom_name,$origin->getID(),"ds-".$custom_name);
	$ds->live = "false"; //($this->live ? "true":"false");
	if ( !$ds->create() ) {
		print("Error:".$ds->getMessage());
		// Rollback
		if ( !is_null($origin) ) $origin->delete( $origin->getID() );
	}

	// ASSIGN SEs
	if ( !$ds->assignSEs() ) {
		// Rollback
		print("Error:".$ds->getMessage());
		if ( !is_null($ds) ) $ds->delete( $ds->getID() );
		if ( !is_null($origin) ) $origin->delete( $origin->getID() );
		//
	}

	return $ds;
}

function createDeliveryServiceGenSettings($origin,$ds) {
	// CUSTOMIZE PROTOCOL: HTTP or HTTPS
	$dsgs = new DeliveryServiceGenSettings($ds->getID(), "http" );
	if ( !$dsgs->create() ) {
		// Rollback
		print("Error:".$dsgs->getMessage());
		if ( !is_null($ds) ) $ds->delete( $ds->getID() );
		if ( !is_null($origin) ) $origin->delete( $origin->getID() );
		//
	}

	return $dsgs;
}

function asssignRule($origin,$ds,$dsgs,$custom_name,$origin_domain) {
	$rule = new FileMgmt("20",$custom_name . "-url-rwr-rule","upload");
	if( !$rule->createUrlRewriteRule($origin_domain,"videos/teste") ) {
		// Rollback
		print("Error:".$rule->getMessage());
		if ( !is_null($dsgs) ) $dsgs->delete( $dsgs->getID() );
		if ( !is_null($ds) ) $ds->delete( $ds->getID() );
		if ( !is_null($origin) ) $origin->delete( $origin->getID() );
		//
	}

	if ( !$ds->applyRuleFile( $rule->getID() ) ) {
		// Rollback
		if ( !is_null($rule) ) $rule->delete( $rule->getID() );
		if ( !is_null($dsgs) ) $dsgs->delete( $dsgs->getID() );
		if ( !is_null($ds) ) $ds->delete( $ds->getID() );
		if ( !is_null($origin) ) $origin->delete( $origin->getID() );
		//
	}

	return $rule;
}

?>