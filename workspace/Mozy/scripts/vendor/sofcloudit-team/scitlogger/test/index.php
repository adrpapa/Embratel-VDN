<?php

/**
 * this sections define the global paths (by default is in framework.php)
 */
define("ROOT_PATH", dirname(__FILE__));
define("CONFIG_PATH", ROOT_PATH . "/configuration");

require_once "../vendor/autoload.php";

/**
 * Main code
 */
$logger = new \SCITLogger\SCITLogger();

$logger->emergency("Mensaje Emergency!");
$logger->alert("Mensaje Alert!");
$logger->critical("Mensaje Critical!");
$logger->error("Mensaje Error!");
$logger->warning("Mensaje Warning!");
$logger->notice("Mensaje Notice!");
$logger->info("Mensaje Info!");
$logger->debug("Mensaje Debug!");
$logger->log("INFO", "Mensaje Log!");