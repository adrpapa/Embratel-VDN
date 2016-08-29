<?php
# It is the context of the subscription, in which a customer can manage its Resources
# It must correspond to a tenant created for the subscriber in the remote application system.

require_once "aps/2/runtime.php";
require_once "utils/niceSSH.php";
require_once "utils/BillingLog.php";
require_once "elemental_api/deltaInput.php";
require_once "elemental_api/deltaOutputTemplate.php";

// Definition of type structures

/**
** Classe que associa um input filter ao 
** output template correspondente
*/
class InOutPath {
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
class ProtoPaths {
    /**
        * @type("InOutPath")
        * @title("HTTP path")
        */
    public $http;

    /**
        * @type("InOutPath")
        * @title("HTTPS path")
        */
    public $https;
    /*
        * 
        */
    public function __construct() {
        $this->http = new InOutPath();
        $this->https = new InOutPath();
    }
}

/*
** Classe que armazena os IDs dos objetos Delta
** que serão utilizados como input filters e output
** templates para conteudos Standard e Premium
** utilizando transporte HTTP e HTTPS
*/
class DeltaPaths {
    /**
        * @type("ProtoPaths")
        * @title("Standard paths")
        */
    public $standard;

    /**
        * @type("ProtoPaths")
        * @title("Premium paths")
        */
    public $premium;
    /*
        * 
        */
    public function __construct() {
        $this->standard = new ProtoPaths();
        $this->premium = new ProtoPaths();
    }
}

/**
* Class context
* @type("http://embratel.com.br/app/VDN_Embratel/context/2.0")
* @implements("http://aps-standard.org/types/core/resource/1.0")
*/

class context extends \APS\ResourceBase
{
    /**
        * @link("http://embratel.com.br/app/VDN_Embratel/channel/2.0[]")
        */
    public $channels;

    /**
        * @link("http://embratel.com.br/app/VDN_Embratel/job/2.0[]")
        */
    public $jobs;

    /**
        * @link("http://embratel.com.br/app/VDN_Embratel/vod/2.0[]")
        */
    public $vods;

    /**
        * @link("http://embratel.com.br/app/VDN_Embratel/cdn/2.0[]")
        */
    public $cdns;

    ## Strong relation (link) to the application instance
    /**
    * @link("http://embratel.com.br/app/VDN_Embratel/cloud/2.0")
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
    * @type("DeltaPaths")
    * @title("Delta objects used for VOD")
    * @readonly
    */
    public $vodDeltaPaths;

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

    /**********************************************************
        ************************** VOD ***************************
        **********************************************************/

    /**
        * @type("http://aps-standard.org/types/core/resource/1.0#Counter")
        * @description("VOD Encoding time in minutes")
        * @unit("unit")
        */
    public $VDN_VOD_Encoding_Minutes;

    /**
        * @type("http://aps-standard.org/types/core/resource/1.0#Counter")
        * @description("VOD Encoding time in minutes - premium streams")
        * @unit("unit")
        */
    public $VDN_VOD_Encoding_Minutes_Premium;

    /**
        * @type("http://aps-standard.org/types/core/resource/1.0#Counter")
        * @description("Encoding time in minutes")
        * @unit("mb-h")
        */
    public $VDN_VOD_Storage_MbH;

    /**********************************************************
        ************************** DVR ***************************
        **********************************************************/

    /**
        * @type("http://aps-standard.org/types/core/resource/1.0#Counter")
        * @description("Live encoding time in minutes")
        * @unit("unit")
        */
    public $VDN_Live_Encoding_Minutes;

    /**
        * @type("http://aps-standard.org/types/core/resource/1.0#Counter")
        * @description("Live encoding time in minutes - premium streams")
        * @unit("unit")
        */
    public $VDN_Live_Encoding_Minutes_Premium;

    /**
        * @type("http://aps-standard.org/types/core/resource/1.0#Counter")
        * @description("Live encoding time in minutes")
        * @unit("unit")
        */
    public $VDN_Live_DVR_Minutes;



    ###############################################################################
    # F U N C O E S   P A R A   I N S T A N C I A M E N T O   D E   C O N T E X T O
    ###############################################################################

    public function provision() {
        $logger = \APS\LoggerRegistry::get();
        $logger->setLogFile("logs/context.log");
        $clientid = formatClientID( $this );
        $logger->info("Iniciando provisionamento de context para o cliente ".$clientid);

        // Initialize counters
        $this->VDN_VOD_Encoding_Minutes = new \org\standard\aps\types\core\resource\Counter();
        $this->VDN_VOD_Encoding_Minutes->usage = 0;
        $this->VDN_VOD_Encoding_Minutes_Premium = new \org\standard\aps\types\core\resource\Counter();
        $this->VDN_VOD_Encoding_Minutes_Premium->usage = 0;
        $this->VDN_VOD_Storage_MbH = new \org\standard\aps\types\core\resource\Counter();
        $this->VDN_VOD_Storage_MbH->usage = 0;
        $this->VDN_Live_Encoding_Minutes = new \org\standard\aps\types\core\resource\Counter();
        $this->VDN_Live_Encoding_Minutes->usage = 0;
        $this->VDN_Live_Encoding_Minutes_Premium = new \org\standard\aps\types\core\resource\Counter();
        $this->VDN_Live_Encoding_Minutes_Premium->usage = 0;
        $this->VDN_Live_DVR_Minutes = new \org\standard\aps\types\core\resource\Counter();
        $this->VDN_Live_DVR_Minutes->usage = 0;
        $this->VDN_HTTP_Traffic = new \org\standard\aps\types\core\resource\Counter();
        $this->VDN_HTTP_Traffic->usage = 0;
        $this->VDN_HTTPS_Traffic = new \org\standard\aps\types\core\resource\Counter();
        $this->VDN_HTTPS_Traffic->usage = 0;
        
        // Create output template for all options: Live/Vod Premium/Std http/https
        $this->vodDeltaPaths = new DeltaPaths();
        $vodLiveArr = array('vod' => $this->vodDeltaPaths);
        foreach( $vodLiveArr as $type => $vodLive ) {
            $stdPremArr = array( 'std' => $vodLive->standard, 'prm' => $vodLive->premium );
            foreach( $stdPremArr as $level => $stdPrem ) {
                $httpHttpsArr = array('http' => $stdPrem->http, 'https' => $stdPrem->https );
                foreach( $httpHttpsArr as $proto => $path ) {
                    $logger->info("Criandos Input Filter e Output Template $clientid $proto $type $level");
                    /*
                        Delta Input filters e output templates estão sendo criados aqui, mas se desejado
                        podemos usar essa estrutura para verificar se já existe, e criá-los à medida que
                        forem necessários
                    */
                    $inputFilter=DeltaInputFilter::getClientInputFilter( $clientid, $type, $proto, $level );
                    $path->inputFilter = $inputFilter->id;
                    $path->outputTemplate = $inputFilter->template_id;
                }
            }
        }
        print_r($this);
        $logger->info("Encerrando provisionamento de context para o cliente ".$clientid);
    }

    public function unprovision(){
        $logger = \APS\LoggerRegistry::get();
        $logger->setLogFile("logs/channels.log");
        $clientid = $this->aps->id;
        $logger->info("Iniciando desprovisionamento de contexto para o cliente ".$clientid);

        // Delete output template for all options: Live/Vod Premium/Std http/https
        $vodLiveArr = array('vod' => $this->vodDeltaPaths);
        foreach( $vodLiveArr as $type => &$vodLive ) {
            $stdPremArr = array( 'std' => $vodLive->standard, 'prm' => $vodLive->premium );
            foreach( $stdPremArr as $level => &$stdPrem ) {
                $httpHttpsArr = array('http' => $stdPrem->http, 'https' => $stdPrem->https );
                foreach( $httpHttpsArr as $proto => &$path ) {
                    $logger->info("Removendo Input Filter e Output Template $clientid $proto $type $level");
                    DeltaInputFilter::delete($path->inputFilter);
                    DeltaOutputTemplate::delete($path->outputTemplate);
                }
            }
        }
    }
    
    public function retrieve() {
        $logger = \APS\LoggerRegistry::get();
        $logger->setLogFile("logs/context.log");
        $logger->info(sprintf("Fetching CDN traffic usage. Current values: http=%f https=%f",
                $this->VDN_HTTP_Traffic->usage, $this->VDN_HTTPS_Traffic->usage));
        $cdnTrafficLog = new BillingLog($this, "cdnTraffic");
        ## Connect to the APS controller
        $apsc = \APS\Request::getController();
        
        ## Reset the local variables
        $httpTraffic = $this->VDN_HTTP_Traffic->usage;
        $http_s_Traffic = $this->VDN_HTTPS_Traffic->usage;

        ## Collect resource usage from all CDNs
        foreach ( $this->cdns as $cdn ) {
            $usage = $apsc->getIo()->sendRequest(\APS\Proto::GET,
                    $apsc->getIo()->resourcePath($cdn->aps->id, 'updateResourceUsage'));
            $usage = json_decode($usage);
            $httpTraffic +=  $usage->httpTrafficActualUsage * 1024 * 1024; // convert GB to MB
            $http_s_Traffic += $usage->httpsTrafficActualUsage * 1024 * 1024; // convert GB to MB
            $cdnTrafficLog->log("$cdn->origin_domain,http;$usage->httpTrafficActualUsage");
            $cdnTrafficLog->log("$cdn->origin_domain,https;$usage->httpsTrafficActualUsage");
        }


        // acumula MBxh em kbxs para converter no final
        $totalKBs=0;
        $VDN_VOD_Encoding_Minutes = 0;
        $VDN_VOD_Encoding_Minutes_Premium = 0;
        $logger->info(sprintf("Computing VoD Encoding Usage. Current usage=%f min Premium=%f min",
                              $this->VDN_VOD_Encoding_Minutes->usage,
                              $this->VDN_VOD_Encoding_Minutes_Premium->usage));
        $vodEncodingLog = new BillingLog($this, "vodEncoding");
        $vodStorageLog = new BillingLog($this, "vodStorage");
        foreach ( $this->vods as $vod ) {
            $logger->debug(sprintf("obtaining VoD billing for ".$vod->path));
            $usage = $apsc->getIo()->sendRequest(\APS\Proto::GET,
                    $apsc->getIo()->resourcePath($vod->aps->id, 'updateVodUsage'));
            $usage = json_decode($usage);
            $VDN_VOD_Encoding_Minutes += $usage->VDN_VOD_Encoding_Minutes;
            if($vod->premium){
                $VDN_VOD_Encoding_Minutes_Premium += $usage->VDN_VOD_Encoding_Minutes;
            }
            $vodEncodingLog->log("$vod->path;$VDN_VOD_Encoding_Minutes;$VDN_VOD_Encoding_Minutes_Premium");
            $totalKBs += ($usage->size * $usage->age);
            $vodStorageLog->log("$vod->path;$usage->size;$usage->age");
        }

        $logger->info(sprintf("Computing Live Encoding Current Usage values: %f min Premium: %f min / DVR: %f min",
                $this->VDN_Live_Encoding_Minutes->usage,
                $this->VDN_Live_Encoding_Minutes_Premium->usage,
                $this->VDN_Live_DVR_Minutes->usage));
        $liveEncodingLog = new BillingLog($this, "liveEncoding");
        foreach ( $this->channels as $channel ) {
            $usage = $apsc->getIo()->sendRequest(\APS\Proto::GET,
                    $apsc->getIo()->resourcePath($channel->aps->id, 'updateLiveUsage'));
            $usage = json_decode($usage);
            $this->VDN_Live_Encoding_Minutes->usage += $usage->VDN_Live_Encoding_Minutes;
            if( $channel->premium ) {
                $this->VDN_Live_Encoding_Minutes_Premium->usage += $usage->VDN_Live_Encoding_Minutes_Premium;
            }
            $this->VDN_Live_DVR_Minutes->usage += $usage->VDN_Live_DVR_Minutes;
            $liveEncodingLog->log($channel->name.";".$this->VDN_Live_Encoding_Minutes->usage.";".$this->VDN_Live_DVR_Minutes->usage);
        }

        ## Update the APS resource counters
        $this->VDN_HTTP_Traffic->usage = round($httpTraffic, 0);
        $this->VDN_HTTPS_Traffic->usage = round($http_s_Traffic, 0);
        $logger->info(sprintf("Resource usage after update: http=%f https=%f",
                $this->VDN_HTTP_Traffic->usage, $this->VDN_HTTPS_Traffic->usage));

        $this->VDN_VOD_Encoding_Minutes->usage += round($VDN_VOD_Encoding_Minutes);
        $this->VDN_VOD_Encoding_Minutes_Premium->usage += round($VDN_VOD_Encoding_Minutes_Premium);
        $this->VDN_VOD_Storage_MbH->usage = round($totalKBs/1024/60/60);
//         print_r($this);
    }
}

?>
