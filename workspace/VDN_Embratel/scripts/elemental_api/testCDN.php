<?php
require_once "deliveryService.php";
require_once "contentOrigin.php";
require_once "DeliveryServiceGenSettings.php";
require_once "fileMgmt.php";

/**
$rule = new FileMgmt("20","url-teste-api","upload");
if( $rule->create() ) {
	print("Sucesso!\n");
	print( $rule->getID() . "\n");	
}
**/

$origin = new ContentOrigin();
$origin->get("teste-rule");

// GET DELIVERY ID
$ds = new DeliveryService();
$ds->get($origin->getID().":"."ds-teste-rule");
print( $ds->getID() );
if ( $ds->applyRuleFile($ds->getID(),"FileInfo_1927") ) {
	print("Sucesso!\n");
}

// CREATE CONTENT ORIGIN
$origin = new ContentOrigin("delta-customer10","customer10.dominio.com","customer10.csn.cdn.cisco.com","Isto e um teste de API");
$origin->contentBasedRouting = "false";
if ( $origin->create() ) {
	print("Sucesso!\n");
	print( $origin->getID() . "\n");
}

// CREATE DELIVERY SERVICE
$ds = new DeliveryService("ds-customer10",$origin->getID(),"Teste de criacao via API");
$ds->sessionQuota = "1000";
$ds->bandQuota = "1000";
if ( $ds->create() ) {
	print ("Sucesso!\n");
	print ( $ds->getID() . "\n" );
}
else {
	print("Falhou:".$ds->getMessage()."\n");
}

// SET UP DELIVERY SERVICE TO HTTPS
$dsgs = new DeliveryServiceGenSettings($ds->getID(), "https");
if ( $dsgs->create() ) {
	print ("Sucesso!\n");
	print ( $dsgs->getMessage()."\n" );
	print ( $dsgs->getID()."\n" );
}
	
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

?>