<?php
require_once "deliveryService.php";
require_once "contentOrigin.php";
require_once "DeliveryServiceGenSettings.php";

// CREATE CONTENT ORIGIN
$origin = new ContentOrigin(null,"delta-customer10","customer10.dominio.com","customer10.csn.cdn.cisco.com","Isto e um teste de API");
$origin->contentBasedRouting = "false";
$origin->create();
print( $origin->getID() );

// CREATE DELIVERY SERVICE
$ds = new DeliveryService(null,"ds-customer10",$origin->getID(),"Teste de criacao via API");
$ds->sessionQuota = "1000";
$ds->bandQuota = "1000";
$ds->create();
print ( $ds->getMessage() );
print ( $ds->getID() );

// SET UP DELIVERY SERVICE TO HTTPS
$dsgs = new DeliveryServiceGenSettings($ds->getID(), "https");
$dsgs->create();
print ( $dsgs->getMessage() );
print ( $dsgs->getID() );

// DELETING..........
$ds->delete();
$origin->delete();

?>