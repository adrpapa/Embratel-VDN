<?php
	# It is the context of the subscription, in which a customer can manage its Contents.
	# It must correspond to a tenant created for the subscriber in the remote application system.
	define('APS_DEVELOPMENT_MODE', true);
	require "aps/2/runtime.php";	
	
	/**
	 * Class context
	 * @type("http://embratel.com.br/app/VDN_Embratel/context/1.0")
	 * @implements("http://aps-standard.org/types/core/resource/1.0")
	 */
	
	class context extends \APS\ResourceBase
	{
		## Strong relation (link) to the application instance

		/**
		 * @link("http://embratel.com.br/app/VDN_Embratel/application/1.0")
		 * @required
		 */
		public $VDN_Embratel;
	
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
		
		/**
		 * @link("http://embratel.com.br/app/VDN_Embratel/job/1.0[]")
		 */
		public $jobs;
		
		/**
		 * @link("http://embratel.com.br/app/VDN_Embratel/channel/1.0[]")
		 */
		public $channels;
		
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
		