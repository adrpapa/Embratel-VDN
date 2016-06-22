<?php

require "aps/2/runtime.php";

// Definition of type structures

class CPU {
	/**
	 * @type("integer")
	 * @title("Number of CPUs")
	 * @description("Number of CPU cores")
	 */
	public $number;
}

class OS {
	/**
	 * @type("string")
	 * @title("OS Name")
	 * @description("Operating System Name")
	 */
	public $name;

	/**
	 * @type("string")
	 * @title("OS Version")
	 * @description("Operating System version")
	 */
	public $version;
}

class Hardware {
	/**
	 * @type("integer")
	 * @title("RAM Size")
	 * @description("RAM size in GB")
	 */
	public $memory;

	/**
	 * @type("integer")
	 * @title("Disk Space")
	 * @description("Disk space in GB")
	 */
	public $diskspace;

	/**
	 * @type("CPU")
	 * @title("CPU")
	 * @description("Server CPU parameters")
	 */
	public $CPU;
}

class Platform {
	/**
	 * @type("string")
	 * @title("Architecture")
	 * @description("Platform architecture")
	 */
	public $arch;

	/**
	 * @type("OS")
	 * @title("OS Parameters")
	 * @description("Parameters of operating system")
	 */
	public $OS;
}


// Main class
/**
 * @type("http://company.example/app/CloudBasic/vps/2.0")
 * @implements("http://aps-standard.org/types/core/resource/1.0")
 */
class vps extends APS\ResourceBase {
	
	// Relation with the management context
	/**
	 * @link("http://company.example/app/CloudBasic/context/1.1")
	 * @required
	 */
	public $context;

	// Relation with an offer
	/**
	 * @link("http://company.example/app/CloudBasic/offer/1.1")
	 * @required
	 */
	 public $offer;
	
	// VPS properties
	
	/**
	 * @type("string")
	 * @title("name")
	 * @description("Server Name")
	 */
	public $name;
	
	/**
	 * @type("string")
	 * @title("Description")
	 * @description("Server Description")
	 */
	public $description;
	
	/**
	 * @type("string")
	 * @title("state")
	 * @description("Server State")
	 */
	public $state;
	
	// VPS complex properties (structures) - defined as classes above
	
	/**
	 * @type("Hardware")
	 * @title("Hardware")
	 * @description("Server Hardware")
	 */
	public $hardware;
	
	/**
	 * @type("Platform")
	 * @title("Platform")
	 * @description("OS Platform")
	 */
	public $platform;
}

