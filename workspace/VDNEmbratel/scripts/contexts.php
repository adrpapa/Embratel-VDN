<?php


# It is the context of the subscription, in which a customer can manage its Resources
# It must correspond to a tenant created for the subscriber in the remote application system.

require_once 'loader.php';

require_once "utils/niceSSH.php";
require_once "utils/BillingLog.php";

// Definition of type structures

/**
* Class context
* @type("http://embratel.com.br/app/VDNEmbratel/context/2.0")
* @implements("http://aps-standard.org/types/core/resource/1.0")
*/

class context extends \ APS \ ResourceBase {
	/**
	* @link("http://embratel.com.br/app/VDNEmbratel/cdn/1.1[]")
	*/
	public $cdns;

	## Strong relation (link) to the application instance
	/**
	* @link("http://embratel.com.br/app/VDNEmbratel/globais/1.0")
	* @required
	*/
	public $global;

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

	/**********************************************************
	    ******************* BILLING COUNTERS *********************
	    **********************************************************/

	/**********************************************************
	    ************************** CDN ***************************
	    **********************************************************/

	/**
	    * @type("http://aps-standard.org/types/core/resource/1.0#Counter")
	    * @description("VDN Total Traffic HTTP")
	    * @unit("kb")
	    */
	public $VDN_HTTP_Traffic;

	/**
	    * @type("http://aps-standard.org/types/core/resource/1.0#Counter")
	    * @description("VDN Total Traffic HTTPS")
	    * @unit("kb")
	    */
	public $VDN_HTTPS_Traffic;

	###############################################################################
	# F U N C O E S   P A R A   I N S T A N C I A M E N T O   D E   C O N T E X T O
	###############################################################################

	public function __construct() {
		$this->logger = \APS\LoggerRegistry::get();
		$this->logger->setLogFile("logs/" . __CLASS__ . "_" . date("Ymd") . ".log");
	}

	public function provision() {
		$this->logger->debug("[" . __METHOD__ . '] >>');
		ConfigConsts::loadConstants($this);
		try {
			$clientid = formatClientID($this);
			$this->logger->info("Iniciando provisionamento de context para o cliente " . $clientid);

			// Initialize counters
			$this->VDN_HTTP_Traffic = new \ org \ standard \ aps \ types \ core \ resource \ Counter();
			$this->VDN_HTTP_Traffic->usage = 0;
			$this->VDN_HTTPS_Traffic = new \ org \ standard \ aps \ types \ core \ resource \ Counter();
			$this->VDN_HTTPS_Traffic->usage = 0;

			print_r($this);
			$this->logger->info("Encerrando provisionamento de context para o cliente " . $clientid);
		} catch (Exception $fault) {
			$userError = "Erro no provisionamento do contexto";
			$this->logger->error($userError);
			$this->logger->error($fault->getMessage());
			throw new \ Rest \ RestException(500, $userError, $fault->getMessage());
		}
		$this->logger->debug("[" . __METHOD__ . '] <<');
	}

	public function unprovision() {
		$this->logger->debug("[" . __METHOD__ . '] >>');
		ConfigConsts::loadConstants($this);
		try {
			$clientid = $this->aps->id;
			$this->logger->info("Iniciando desprovisionamento de contexto para o cliente " . $clientid);

			$this->logger->info("Fim de desprovisionamento de contexto para o cliente " . $clientid);
		} catch (Exception $fault) {
			$userError = "Erro no desprovisionamento do contexto";
			$this->logger->error($userError);
			$this->logger->error($fault->getMessage());
			throw new \ Rest \ RestException(500, $userError, $fault->getMessage());
		}
		$this->logger->debug("[" . __METHOD__ . '] <<');
	}

	public function retrieve() {
		$this->logger->debug("[" . __METHOD__ . '] >>');
		ConfigConsts::loadConstants($this);
		if ($this->disabled) {
			$this->logger->info("Subscription disabled. No billing data will be collected for " . formatClientID($this));
			return;
		}
		$this->logger->info("Start retrieve billing data for " . formatClientID($this));

		## Connect to the APS controller
		$apsc = \ APS \ Request :: getController();

		## Reset the local variables
		$VDN_HTTP_Traffic = 0;
		$VDN_HTTPS_Traffic = 0;
		$cdnTrafficLog = new BillingLog($this, "cdn");
		## Collect resource usage from all CDNs
		foreach ($this->cdns as $cdn) {
			$this->logger->info("Fetching CDN traffic usage. for $cdn->origin_domain");
			$usage = $apsc->getIo()->sendRequest(\ APS \ Proto :: GET, $apsc->getIo()->resourcePath($cdn->aps->id, 'updateResourceUsage'));
			$usage = json_decode($usage);
			$httpTraffic = $usage->httpTrafficActualUsage * 1024 * 1024; // convert GB to MB
			$http_s_Traffic = $usage->httpsTrafficActualUsage * 1024 * 1024; // convert GB to MB
			$logline = "$cdn->origin_domain;$httpTraffic;$http_s_Traffic";
			$this->logger->debug($logline);
			$cdnTrafficLog->log($logline);
		}
		$this->VDN_HTTP_Traffic->usage += round($VDN_HTTP_Traffic * 1024 * 1024); // convert GB to MB;
		$this->VDN_HTTPS_Traffic->usage += round($VDN_HTTPS_Traffic * 1024 * 1024);

		## Update the APS resource counters
		$this->logger->info(sprintf("Resource usage after update: http=%f https=%f", $this->VDN_HTTP_Traffic->usage, $this->VDN_HTTPS_Traffic->usage));

		//         print_r($this);
		$this->logger->debug("[" . __METHOD__ . '] <<');
	}

	public function disable() {
		$this->logger->debug("[" . __METHOD__ . '] >>');
		ConfigConsts::loadConstants($this);
		$this->logger->info("Subscription disable requested for subscription " . formatClientID($this));

		## Connect to the APS controller
		$apsc = \ APS \ Request :: getController();

		## Call cdn do disable endpoint by removing SEs from the DS
		foreach ($this->cdns as $cdn) {
			$this->logger->info("Disabling service on cds $cdn->origin_domain");
			$apsc->getIo()->sendRequest(\ APS \ Proto :: GET, $apsc->getIo()->resourcePath($cdn->aps->id, 'unassignServiceEngines'));
		}
		$this->logger->debug("[" . __METHOD__ . '] <<');
	}

	public function enable() {
		$this->logger->debug("[" . __METHOD__ . '] >>');
		ConfigConsts::loadConstants($this);
		$this->logger->info("Subscription enable requested for subscription " . formatClientID($this));

		## Connect to the APS controller
		$apsc = \ APS \ Request :: getController();

		## Call cdn do disable endpoint by removing SEs from the DS
		foreach ($this->cdns as $cdn) {
			$this->logger->info("Enabling service on cds $cdn->origin_domain");
			$apsc->getIo()->sendRequest(\ APS \ Proto :: GET, $apsc->getIo()->resourcePath($cdn->aps->id, 'assignServiceEngines'));
		}
		$this->logger->debug("[" . __METHOD__ . '] <<');
	}

	/**
	 * @verb(PUT)
	 * @path("/resource_usage")
	 * @param(string, body)
	 * @access(referrer, true)
	 */
	public function getResourceUsageDetailsReport($request){
		$this->logger->debug("[" . __METHOD__ . '] >>');
		ConfigConsts::loadConstants($this);
		try{
			$request = json_decode($request);
			$headers = array(
					_("Serviço de Entrega"),
					_("Data/Hora"),
					_("Traffico HTTP"),
					_("Traffico HTTPS")
			);
			$data = array(
				array(_("Serviço de Entrega")=>"Serviço1", "Data/Hora"=>"01/01/2001", _("Traffico HTTP")=>"150", _("Traffico HTTPS")=>"980"),
				array(_("Serviço de Entrega")=>"Serviço2", "Data/Hora"=>"01/01/2001", _("Traffico HTTP")=>"250", _("Traffico HTTPS")=>"880"),
				array(_("Serviço de Entrega")=>"Serviço3", "Data/Hora"=>"01/01/2001", _("Traffico HTTP")=>"350", _("Traffico HTTPS")=>"780"),
			);
			$return = array(
				"titles" => $headers,
				"data" => $data
			);
			print_r($return);
			$this->logger->debug("[".__METHOD__. '] << ');
			return $return;
		}catch (\Exception $e){
			$this->log->error("[".__METHOD__. "]".$e->getMessage()." ".$e->getFile()."(".$e->getLine().")".PHP_EOL.$e->getTraceAsString());
			throw new \Rest\RestException(500, _("An error has ocurred while trying to retrieve the usage data."), $e->getMessage());
		}
	}
}
//		function _($str){
//			return $str;
//		}


//		$request = (object)[];
//		$request->CDMS_ADDRESS                 = "";
//		$request->CDMS_PORT                    = "";
//		$request->CDMS_USER                    = "";
//		$request->CDMS_PWD                     = "";
//		$request->CDMS_DOMAIN                  = "";
//		$request->CDMS_MAX_BITRATE_PER_SESSION = ""; 
//		$request->SPLUNK_ADDRESS               = "";
//		$request->SPLUNK_ENDPOINT              = ""; 
//		$request->SPLUNK_QUERY                 = "";
//		
//		print_r($request);
//			$ctx = new context();
//			$ctx->global = $request;
//			$ctx->getResourceUsageDetailsReport("{}");

?>
