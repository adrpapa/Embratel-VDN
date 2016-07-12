<?php
require_once "deliveryService.php";
require_once "contentOrigin.php";
require_once "deliveryServiceGenSettings.php";
require_once "fileMgmt.php";
require_once "serviceEngine.php";

$name = "customer10";

/**
$se = new ServiceEngine();
if ( !$se->get(null) ) {
	print("falhou.".$se->getMessage());
}
if ( !$se->get( $se->getContentAcquirer() ) ){
	print("falhou.".$se->getMessage());
}
****************/

$name = "customer10";
//name,origin,fqdn,description
$origin = new ContentOrigin("co-".$name,$name.".obj.osp.embratelcloud.com.br",$name.".csi.cds.cisco.com","Content Origin Customer:".$name);
if ( $origin->create() ) {
	print("Sucesso criando content origin: ".$origin->getID()."\n");
}
else {
	print("Falhou:".$origin->getMessage()."\n");
	exit(1);
}

// Name,origin,description
$ds = new DeliveryService("ds-".$name,$origin->getID(),"Delivery Service Customer:".$name);
if ( $ds->create() ) {
	print ("Sucesso criando delivery service!\n");
	print ( $ds->getID() . "\n" );
}
else {
	print("Falhou:".$ds->getMessage()."\n");
	$origin->delete( $origin->getID() );
	exit(1);
}

// SET UP DELIVERY SERVICE TO HTTPS
$dsgs = new DeliveryServiceGenSettings($ds->getID(), "https");
if ( $dsgs->create() ) {
	print ("Sucesso criando delivery service settings!\n");
	print ( $dsgs->getMessage()."\n" );
	print ( $dsgs->getID()."\n" );
}
else {
	print("Falhou:".$dsgs->getMessage()."\n");
	$ds->delete( $ds->getID() );
	$origin->delete( $origin->getID() );
	exit(1);
}

// SET UP SERVICE ENGINES AND CONTENT ACQUIRER
if ( $ds->assignSEs() ) {
	print("Sucesso associando service enginess\n");
}
else {
	print("Falhou:".$ds->getMessage()."\n");
	$dsgs->delete( $dsgs->getID() );
	$ds->delete( $ds->getID() );
	$origin->delete( $origin->getID() );
	exit(1);
}

// CREATE RULE
$rule = new FileMgmt("20",$name."-url-rwr-rule","upload");
if( $rule->createUrlRewriteRule($name.".csi.cds.cisco.com","v1/AUTH_906b88ad732c4d39907987f1ad054814/elemental") ) {
	print("Sucesso!\n");
	print( $rule->getID() . "\n");
}
else {
	$dsgs->delete( $dsgs->getID() );
	$ds->delete( $ds->getID() );
	$origin->delete( $origin->getID() );
	exit(1);	
}

// ASSIGN RULE
if ( $ds->applyRuleFile( $rule->getID() ) ) {
	print("Sucesso!\n");
}
else {
	$dsgs->delete( $dsgs->getID() );
	$ds->delete( $ds->getID() );
	$origin->delete( $origin->getID() );
	exit(1);	
}

// DELETE ALL..........
$dsgs->delete( $dsgs->getID() );
$ds->delete( $ds->getID() );
$origin->delete( $origin->getID() );
$rule->delete( $rule->getID() );

/*******************************************************
// GET CONTENT ORIGIN ID
$origin = new ContentOrigin();
$origin->get("delta-customer10");

// GET DELIVERY ID
$ds = new DeliveryService();
$ds->get($origin->getID().":"."ds-customer10");
print( $ds->getID() );

// UPDATING.........
$ds->sessionQuota = "2000";
if ( $ds->update($ds->getID()) ) {
	print ("Sucesso!\n");
}
else {
	print ("Falhou: ".$ds->getMessage()."\n");
}


// DELETING..........

// DELETING DELIVERY SERVICE...
if ( $ds->delete($ds->getID()) ) {
	print ("Sucesso!\n");
}
else {
	print ("Falhou: ".$ds->getMessage()."\n");
}

// DELETING CONTENT ORIGIN......
if ( $origin->delete($origin->getID()) ) {
	print ("Sucesso!\n");
}
else {
	print ("Falhou: ".$origin->getMessage()."\n");
}
*******************************************************/

?>