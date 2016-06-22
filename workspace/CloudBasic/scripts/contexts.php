<?php
# It is the context of the subscription, in which a customer can manage its VPSes.
# It must correspond to a tenant created for the subscriber in the remote application system.
require "aps/2/runtime.php";

/**
* Class context
* @type("http://company.example/app/CloudBasic/context/1.1")
* @implements("http://aps-standard.org/types/core/resource/1.0")
*/

class context extends \APS\ResourceBase
{
## Strong relation (link) to the application instance
		
	/**
	* @link("http://company.example/app/CloudBasic/cloud/1.1")
	* @required
	*/
	public $cloud;
	
## Weak relation (link) to collection of VPSes
	/**
	 * @link("http://company.example/app/CloudBasic/vps/2.0[]")
	 */
	public $vpses;
	
## Strong relation with the Subscription.
## This way, we allow the service to access the operation resources
## with the limits and usage defined in the subscription.

	/**
	* @link("http://aps-standard.org/types/core/subscription/1.0")
	* @required
	*/
    public $subscription;

## Link to the account type makes account attributes available to the service,
## e.g., the account (subscriber) name, and all its other data.

	/**
	* @link("http://aps-standard.org/types/core/account/1.0")
    	* @required
    	*/
    public $account;

## Subset of attributes marked as read-only,
## which means only the application can change them.
## The password is "encrypted", which prevents customers to get its value.

	/**
	* @type(string)
	* @title("Context ID")
	* @readonly
	*/

	public $contextid;

	/**
	* @type(string)
	* @title("Context Password")
	* @readonly
	* @encrypted
	*/

	public $contextpass;

}
?>
