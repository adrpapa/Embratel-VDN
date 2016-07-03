<?php
    require_once "CDSM.php";

    // **
    // Class DeliveryServiceGenSettings
    // Permite customizar um Delivery Service
    // **
    class DeliveryServiceGenSettings extends CDSM {
    	protected $deliveryService;
    	protected $Bitrate;
    	protected $OsProtocol;
    	protected $StreamingProtocol;
    	protected $HashLevel;
    	protected $TmpfsSize;
    	protected $OsHttpPort;
    	protected $ReadTimeout;
    	
        public function __construct( $id,$protocol="http",$Bitrate="1000",$HashLevel="0",
                                     $TmpfsSize="2",$OsHttpPort="80",$ReadTimeout="5") {
                                	
        	$this->optional_params_names = array(
        			"HttpAllow",
        			"ContentFlowTrace",
        			"FilterTraceFlowToClient",
        			"HttpExtAllow",
        			"HttpExt",
        			"GreenCookie",
        			"EnableCacheError",
        			"CacheError",
        			"OSRedirectEnable",
        			"NrOfRedir",
        			"EnableAbrLive",
        			"SkipLL",
        			"WmtUserAgent",
        			"QuotaUsageReport",
        			"genericSessionTrack",
        			"hssSessionTrack",
        			"hlsSessionTrack"
        	);        	
        	parent::__construct();
        	$this->id = $id;
			$this->Bitrate = $Bitrate;
			$this->OsProtocol=($protocol == "http" ? "0":"1");
			$this->StreamingProtocol=($protocol == "http" ? "0":"1");
			$this->HashLevel = $HashLevel;
			$this->TmpfsSize = $TmpfsSize;
			$this->OsHttpPort = $OsHttpPort;
			$this->ReadTimeout = $ReadTimeout;
        	$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";      	
        }
             
        public function create() {      	
        	$this->action = "createDeliveryServiceGenSettings";
        	$this->params = "&deliveryService=" . $this->id;
        	$this->params .= "&Bitrate=" . $this->Bitrate;
        	$this->params .= "&OsProtocol=" . $this->OsProtocol;
        	$this->params .= "&StreamingProtocol=" . $this->StreamingProtocol;
        	$this->params .= "&HashLevel=" . $this->HashLevel;
        	$this->params .= "&TmpfsSize=" . $this->TmpfsSize;
        	$this->params .= "&OsHttpPort=" . $this->OsHttpPort;
        	$this->params .= "&ReadTimeout=" . $this->ReadTimeout;       	
        	parent::create();
        }
        
        public function delete() {
        }
        
        public function update() {
        }        
    }
?>