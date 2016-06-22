<?php
# It is the context of the subscription, in which a customer can manage its Resources
# It must correspond to a tenant created for the subscriber in the remote application system.

require_once "aps/2/runtime.php";
require_once "elemental_api/deltaInput.php";
require_once "elemental_api/deltaOutputTemplate.php";


/**
* Class context
* @type("http://embratel.com.br/app/VDN_Embratel/context/1.0")
* @implements("http://aps-standard.org/types/core/resource/1.0")
*/

class context extends \APS\ResourceBase
{
	/**
	 * @link("http://embratel.com.br/app/VDN_Embratel/channel/1.0[]")
	 */
	public $vpses;	
	
	/**
	 * @link("http://embratel.com.br/app/VDN_Embratel/channel/1.0[]")
	 */
	public $channels;
	
## Strong relation (link) to the application instance
		
	/**
	* @link("http://embratel.com.br/app/VDN_Embratel/cloud/1.0")
	* @required
	*/
	public $cloud;

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

# Flag indicating if this is a premium subscription (comes from PBA?)
	/**
	* @type(boolean)
	* @title("Premium")
	*/

	public $isPremium;

## Subset of attributes marked as read-only,
## which means only the application can change them.
## The password is "encrypted", which prevents customers to get its value.

	/**
	* @type(integer)
	* @title("Delta Live Output Template ID")
	* @readonly
	*/

	public $liveOutputTemplateID;

	/**
	* @type(integer)
	* @title("Delta VOD Input Filter ID")
	* @readonly
	*/
	public $vodInputFilterID;

	/**
	* @type(integer)
	* @title("Delta VOD Output Template ID")
	* @readonly
	*/
	public $vodOutputTemplateID;

	public function __construct() {
        // setting new log file, path relative to your script current directory
        $this->logger = \APS\LoggerRegistry::get();
        $this->logger->setLogFile("logs/contexts.log");
    }

    public function provision() {
    	$clientid = sprintf("Client_%06d",$this->account->id);
    	$this->logger->debug("Iniciando provisionamento de context para o cliente ".$clientid);

        // Create Live output template
    	$liveOutputTemplate = DeltaOutputTemplate::newOutputTemplate($clientid, 'live', 
                $this->isPremium ? 'premium' : 'std');
        $this->liveOutputTemplateID = $liveOutputTemplate->id;
    	$this->logger->debug("Criado Delta Output Template para Live ID=".$this->liveOutputTemplateID);

        // Create VOD input filter
        $vodInputFilter = DeltaInputFilter::newVodInputFilter( $clientid, $this->isPremium? 'premium': 'std');
        $this->vodInputFilterID = $vodInputFilter->id;
    	$this->logger->debug("Criado Delta input filter para VOD ID=".$this->vodInputFilterID);

        // Create VOD output template
        $vodOutputTemplate = DeltaOutputTemplate::newOutputTemplate($clientid, 'vod', 
                $this->isPremium ? 'premium' : 'std');
        $this->vodOutputTemplateID = $liveOutputTemplate->id;
    	$this->logger->debug("Criado Delta Output Template para VOD ID=".$this->vodOutputTemplateID);

    }
    
    public function unprovision(){
    	$clientid = sprintf("Client_%06d",$this->account->id);
    	$this->logger->debug("Iniciando desprovisionamento de context para o cliente ".$clientid);

    	//Delete Live output template
    	$this->logger->debug("Deletando Delta Output Template para Live ID=".$this->liveOutputTemplateID);
    	DeltaOutputTemplate::delete($this->liveOutputTemplateID);

    	// Delete vod input filter
    	$this->logger->debug("Deletando Delta input filter para VOD ID=".$this->vodInputFilterID);
    	DeltaInputFilter::delete($this->vodInputFilterID);

    	// Delete vod output template
    	$this->logger->debug("Deletando Delta Output Template para VOD ID=".$this->vodOutputTemplateID);
    	DeltaOutputTemplate::delete($this->vodOutputTemplateID);
    }
}

?>
