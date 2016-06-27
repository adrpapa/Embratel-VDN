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
	 * @link("http://embratel.com.br/app/VDN_Embratel/vps/1.0[]")
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

	## Subset of attributes marked as read-only,
	## which means only the application can change them.

	/**
	* @type(integer)
	* @title("Delta VOD Standard Input Filter ID")
	* @readonly
	*/
	public $vodStandardInputFilter;

	/**
	* @type(integer)
	* @title("Delta VOD Standard Output Template ID")
	* @readonly
	*/
	public $vodStandardOutputTemplate;

	/**
	* @type(integer)
	* @title("Delta VOD Premium Input Filter ID")
	* @readonly
	*/
	public $vodPremiumInputFilter;

	/**
	* @type(integer)
	* @title("Delta VOD Premium Output Template ID")
	* @readonly
	*/
	public $vodPremiumOutputTemplate;

	/**
	* @type(integer)
	* @title("Delta Liv Standard Output Template ID")
	* @readonly
	*/
	public $liveStandardOutputTemplate;

	/**
	* @type(integer)
	* @title("Delta Live Premium Output Template ID")
	* @readonly
	*/
	public $livePremiumOutputTemplate;

    public function provision() {
    	\APS\LoggerRegistry::get()->setLogFile("logs/context.log");
    	$clientid = sprintf("Client_%06d",$this->account->id);
    	\APS\LoggerRegistry::get()->info("Iniciando provisionamento de context para o cliente ".$clientid);

        // Create output template for all options: Live / Vod Premium / Stde
    	$this->liveStandardOutputTemplate = DeltaOutputTemplate::getClientOutputTemplate($clientid, 'live', 'std')->id;
    	$this->livePremiumOutputTemplate = DeltaOutputTemplate::getClientOutputTemplate($clientid, 'live', 'premium' )->id;

    	$this->vodStandardOutputTemplate = DeltaOutputTemplate::getClientOutputTemplate($clientid, 'vod', 'std')->id;
    	$this->vodPremiumOutputTemplate = DeltaOutputTemplate::getClientOutputTemplate($clientid, 'vod', 'premium' )->id;
    	\APS\LoggerRegistry::get()->info("Criados os Delta Output Templates para o cliente ".$clientid);
        

        // Create VOD input filters
    	\APS\LoggerRegistry::get()->info("Criando os Delta Input Filters para o cliente ".$clientid);
        $this->vodStandardInputFilter = DeltaInputFilter::getVodClientInputFilter( $clientid, 'std')->id;
        $this->vodPremiumInputFilter = DeltaInputFilter::getVodClientInputFilter( $clientid, 'premium')->id;
    	\APS\LoggerRegistry::get()->info("Delta Input Filters para o cliente $clientid Criandos");

    	\APS\LoggerRegistry::get()->info("Encerrando provisionamento de context para o cliente ".$clientid);

    }
    
    public function unprovision(){
    	\APS\LoggerRegistry::get()->setLogFile("logs/channels.log");
    	$clientid = sprintf("Client_%06d",$this->account->id);
    	\APS\LoggerRegistry::get()->info("Iniciando desprovisionamento de contexto para o cliente ".$clientid);

    	// Delete vod input filter
    	\APS\LoggerRegistry::get()->info("Deletando Delta input filters para o cliente $clientid");
    	DeltaInputFilter::delete($this->vodStandardInputFilter);
    	DeltaInputFilter::delete($this->vodPremiumInputFilter);
    	\APS\LoggerRegistry::get()->info("Delta input filters para o cliente $clientid Deletados");

    	//Delete output templates
    	\APS\LoggerRegistry::get()->info("Deletando Delta Output Templates para o cliente $clientid");
    	DeltaOutputTemplate::delete($this->liveStandardOutputTemplate);
    	DeltaOutputTemplate::delete($this->livePremiumOutputTemplate);
    	DeltaOutputTemplate::delete($this->vodStandardOutputTemplate);
    	DeltaOutputTemplate::delete($this->vodPremiumOutputTemplate);
    	\APS\LoggerRegistry::get()->info("Delta Output Templates para o cliente $clientid Deletados");
    }
}

?>
