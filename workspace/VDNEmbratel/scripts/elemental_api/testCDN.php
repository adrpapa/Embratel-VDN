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

$origin_id=null;
$ds_id=null;
$dsgs_id = null;
$rule_id=null;

//provision();
configure("Channel_2153","DsvcGenSettings_2154","WebSite_2151","FileInfo_2155");

function provision() {
	$alias = "eventoteste";
	$subscription = "10008";
	$custom_name   = $alias . "-" . $subscription;
	$custom_domain = $alias . "." . $subscription;
	$origin_domain = $custom_domain . ".csi.cds.cisco.com";
	$origin_server = $custom_name . ".obj.osp.embratelcloud.com.br";

    // CREATE CONTENT ORIGIN
	$origin = new ContentOrigin("co-".$custom_name,$origin_server,$origin_domain,"co-".$custom_name);
	if ( !$origin->create() ) {
		print("Error:".$origin->getMessage());
	}

	$origin_id = $origin->getID();
	
	// CREATE DELIVERY SERVICE
	$ds = createDeliveryService($origin,$custom_name);

	$ds_id = $ds->getID();
	
	// CREATE DELIVERY SERVICE GENERAL SETTINGS
	$dsgs = createDeliveryServiceGenSettings($origin,$ds);

	$dsgs_id = $dsgs->getID();
	
	// ASSIGN RULE TO DELIVERY SERVICE
	$rule = asssignRule($origin,$ds,$dsgs,$custom_name,$origin_domain);

	$rule_id = $rule->getID();
	
	//unprovision($ds_id,$dsgs_id,$origin_id,$rule_id);
	configure($ds_id,$dsgs_id,$origin_id,$rule_id);
}

function unprovision($ds_id,$dsgs_id,$origin_id,$rule_id){	 
	$rule_del = new FileMgmt();
	$rule_del->delete( $rule_id );
	 
	$dsgs_del = new DeliveryServiceGenSettings();
	$dsgs_del->delete( $dsgs_id );
	 
	$ds_del = new DeliveryService();
	$ds_del->delete( $ds_id );
	 
	$origin_del = new ContentOrigin();
	$origin_del->delete( $origin_id );
}

function configure($ds_id=null,$dsgs_id=null,$origin_id=null,$rule_id=null) {
	
	$alias = "eventotesteupd";
	$subscription = "10008";
	$custom_name   = $alias . "-" . $subscription;
	$custom_domain = $alias . "." . $subscription;
	$origin_domain = $custom_domain . ".csi.cds.cisco.com";
	$origin_server = $custom_name . ".obj.osp.embratelcloud.com.br";
	
	$origin = new ContentOrigin("co-".$custom_name,$origin_server,$origin_domain,"co-".$custom_name);
	if ( !$origin->update($origin_id) ) {
		print("Error:".$origin->getMessage());
	}	
	
	$ds = new DeliveryService("ds-".$custom_name,"WebSite_2151","ds-".$custom_name);
	if ( !$ds->update($ds_id) ) {
		print("Error:".$ds->getMessage());
	}	
	
	$rule = new FileMgmt("20",$custom_name . "-url-rwr-rule","upload");
	if( !$rule->updateUrlRewriteRule($rule_id,$origin_domain,"videos/teste") ) {
		print("Error:".$rule->getMessage());
	}	
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