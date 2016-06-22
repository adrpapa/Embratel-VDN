<?php
# Offer sets limitations of VPS parameters. A customer can select the needed Offer when creating a VPS.

require_once "aps/2/runtime.php";

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

/**
 * Class offer
 * @type("http://company.example/app/CloudBasic/offer/1.1")
 * @implements("http://aps-standard.org/types/core/resource/1.0")
 */
class offer extends \APS\ResourceBase
{
    ## Offers must be bound to the application, which resources are used to generate a VPS.
    /**
     * @link("http://company.example/app/CloudBasic/cloud/1.1")
    * @required
    */
    public $cloud;

    ## Every VPS must refer to an Offer from which it takes parameters.
    /**
     * @link("http://company.example/app/CloudBasic/vps/2.0[]")
    */
    public $vpses;

    ## Below we define a subset of attributes - Offer name and description.
    ## Provider must set them for each Offer.
    /**
     * @type(string)
    * @title("Offer Name")
    */
    public $name;

    /**
     * @type(string)
     * @title("Offer Description")
     */
    public $description;

    ## Below we define the set of VPS parameters that the provider must set for each Offer.
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
    
    static function createDefaultOffer()
    {
        $defaultOffer = new offer();
        $defaultOffer->name = 'DefaultLimits';
        $defaultOffer->description = 'Default offer with default VPS limitation';
        $defaultOffer->hardware->memory = 2048;
        $defaultOffer->hardware->diskspace = 50;
        $defaultOffer->hardware->CPU->number = 4;
        $defaultOffer->platform->OS->name = 'centos-6';
        
        return $defaultOffer;        
    }

}
