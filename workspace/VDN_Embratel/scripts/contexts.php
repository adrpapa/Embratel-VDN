<?php
# It is the context of the subscription, in which a customer can manage its Resources
# It must correspond to a tenant created for the subscriber in the remote application system.

require_once "aps/2/runtime.php";
require_once "elemental_api/deltaInput.php";
require_once "elemental_api/deltaOutputTemplate.php";

// Definition of type structures

/**
** Classe que associa um input filter ao 
** output template correspondente
*/
class inOutPath {
	/**
	 * @type("integer")
	 * @title("Delta Input Filter ID")
	 */
	public $inputFilter;
	/**
	 * @type("integer")
	 * @title("Delta Output Template ID")
	 */
	public $outputTemplate;
}

/**
** Classe que associa os caminhos de
** entrada / saída aos protocolos
*/
class protoPaths {
	/**
	 * @type("inOutPath")
	 * @title("HTTP path")
	 */
	public $http;

	/**
	 * @type("inOutPath")
	 * @title("HTTPS path")
	 */
	public $https;
}

/*
** Classe que armazena os IDs dos objetos Delta
** que serão utilizados como input filters e output
** templates para conteudos Standard e Premium
** utilizando transporte HTTP e HTTPS
*/
class deltaPaths {
	/**
	 * @type("protoPaths")
	 * @title("Standard paths")
	 */
	public $standard;

	/**
	 * @type("protoPaths")
	 * @title("Premium paths")
	 */
	public $premium;
}

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
	* @type("deltaPaths")
	* @title("Delta objects used for VOD")
	* @readonly
	*/
	public $vodDeltaPaths;


	/**
	* @type("deltaPaths")
	* @title("Delta objects used for Live events")
	* @readonly
	*/
	public $liveDeltaPaths;


	###############################################################################
	# F U N C O E S   P A R A   I N S T A N C I A M E N T O   D E   C O N T E X T O
	###############################################################################
    
    public function provision() {
    	\APS\LoggerRegistry::get()->setLogFile("logs/context.log");
    	$clientid = sprintf("Client_%06d",$this->account->id);
    	\APS\LoggerRegistry::get()->info("Iniciando provisionamento de context para o cliente ".$clientid);

        // Create output template for all options: Live/Vod Premium/Std http/https
    	$vodLiveArr = array('vod' => $this->vodDeltaPaths, 'live' => $this->liveDeltaPaths);
    	foreach( $vodLiveArr as $type => &$vodLive ) {
            $stdPremArr = array( 'std' => $vodLive->standard, 'premium' => $vodLive->premium );
            foreach( $stdPremArr as $level => &$stdPrem ) {
                $httpHttpsArr = array('http' => $stdPremArr->http, 'https' => $stdPremArr->https );
                foreach( $httpHttpsArr as $proto => &$path ) {
                    $path->outputTemplate =
                        \APS\LoggerRegistry::get()->info("Criandos Input Filter e Output Template $clientid_$proto_$type, $level");
                        DeltaOutputTemplate::getClientOutputTemplate($clientid, $type, $proto, $level )->id;
                    $path->inputFilter =
                        DeltaInputFilter::getClientInputFilter( $clientid, $type, $proto, $level )->id;
                }
            }
    	}
        \APS\LoggerRegistry::get()->info("Encerrando provisionamento de context para o cliente ".$clientid);

    }
    
    public function unprovision(){
    	\APS\LoggerRegistry::get()->setLogFile("logs/channels.log");
    	$clientid = sprintf("Client_%06d",$this->account->id);
    	\APS\LoggerRegistry::get()->info("Iniciando desprovisionamento de contexto para o cliente ".$clientid);

        // Delete output template for all options: Live/Vod Premium/Std http/https
    	$vodLiveArr = array('vod' => $this->vodDeltaPaths, 'live' => $this->liveDeltaPaths);
    	foreach( $vodLiveArr as $type => &$vodLive ) {
            $stdPremArr = array( 'std' => $vodLive->standard, 'premium' => $vodLive->premium );
            foreach( $stdPremArr as $level => &$stdPrem ) {
                $httpHttpsArr = array('http' => $stdPremArr->http, 'https' => $stdPremArr->https );
                foreach( $httpHttpsArr as $proto => &$path ) {
                    DeltaInputFilter::delete($path->outputTemplate);
                }
            }
    	}

    	
    	// Delete vod input filter
    	\APS\LoggerRegistry::get()->info("Deletando Delta Vod input filters para o cliente $clientid");
    	DeltaInputFilter::delete($this->vodStandardInputFilter4x3);
    	DeltaInputFilter::delete($this->vodStandardInputFilter16x9);
    	DeltaInputFilter::delete($this->vodPremiumInputFilter4x3);
    	DeltaInputFilter::delete($this->vodPremiumInputFilter16x9);
    	\APS\LoggerRegistry::get()->info("Delta Vod input filters para o cliente $clientid Deletados");

    	// Delete live input filter
    	\APS\LoggerRegistry::get()->info("Deletando Delta Live input filters para o cliente $clientid");
    	DeltaInputFilter::delete($this->liveStandardInputFilter4x3);
    	DeltaInputFilter::delete($this->liveStandardInputFilter16x9);
    	DeltaInputFilter::delete($this->livePremiumInputFilter4x3);
    	DeltaInputFilter::delete($this->livePremiumInputFilter16x9);
    	\APS\LoggerRegistry::get()->info("Delta Live input filters para o cliente $clientid Deletados");

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
