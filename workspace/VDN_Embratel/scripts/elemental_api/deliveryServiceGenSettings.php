<?php
    require_once "CDSM.php";

    // **
    // Class DeliveryServiceGenSettings
    // Permite customizar um Delivery Service
    // **
    class DeliveryServiceGenSettings extends CDSM {

    	protected $internal_deliveryService;
    	protected $internal_protocol;
    	protected $internal_Bitrate;
    	protected $internal_HashLevel;
    	protected $internal_TmpfsSize;
    	protected $internal_OsHttpPort;
    	protected $internal_ReadTimeout;
    	
        public function __construct( $deliveryService=null,$protocol="http",$Bitrate="1000",$HashLevel="0",
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
        			"hlsSessionTrack",
        			"deliveryService",
        			"Bitrate",
        			"HashLevel",
        			"OsProtocol",
        			"StreamingProtocol",
        			"TmpfsSize",
        			"OsHttpPort",
        			"ReadTimeout"
        	);        	
        	parent::__construct();
        	
        	$this->internal_deliveryService = $deliveryService;
        	$this->internal_protocol = $protocol;
        	$this->internal_Bitrate = $Bitrate;
        	$this->internal_HashLevel = $HashLevel;
        	$this->internal_TmpfsSize = $TmpfsSize;
        	$this->internal_OsHttpPort = $OsHttpPort;
        	$this->internal_ReadTimeout = $ReadTimeout;
        	/*
        	$this->deliveryService = $deliveryService;
			$this->Bitrate = $Bitrate;
			$this->OsProtocol=($protocol == "http" ? "0":"1");
			$this->StreamingProtocol=($protocol == "http" ? "0":"1");
			$this->HashLevel = $HashLevel;
			$this->TmpfsSize = $TmpfsSize;
			$this->OsHttpPort = $OsHttpPort;
			$this->ReadTimeout = $ReadTimeout;
			*/
        	
        	$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";      	
        }
             
        public function create($data=null) {      	     		
        	$this->action = "createDeliveryServiceGenSettings";
        	$this->deliveryService = $this->internal_deliveryService;
        	$this->Bitrate = $this->internal_Bitrate;
        	$this->OsProtocol=($this->internal_protocol == "http" ? "0":"1");
        	$this->StreamingProtocol=($this->internal_protocol == "http" ? "0":"1");
        	$this->HashLevel = $this->internal_HashLevel;
        	$this->TmpfsSize = $this->internal_TmpfsSize;
        	$this->OsHttpPort = $this->internal_OsHttpPort;
        	$this->ReadTimeout = $this->internal_ReadTimeout;
  
        	if ( is_null($this->deliveryService) ||
        			is_null($this->Bitrate) ||
        			is_null($this->OsProtocol) ||
        			is_null($this->StreamingProtocol) ||
        			is_null($this->HashLevel) ||
        			is_null($this->TmpfsSize) ||
        			is_null($this->OsHttpPort) ||
        			is_null($this->ReadTimeout) )
        	{
        		throw new invalidargumentexception("DeliveryServiceGenSettings::create() parameters required must not be null");
        	}        	
        	
        	return( parent::create($data) );
        }
        
        public function delete($id) {
        	$this->action = "deleteDeliveryServiceGenSettings";
        	$this->deliveryService = $id;
        	return ( parent::delete($id) );        	
        }
        
        public function update($id,$data=null) {
        	$this->action = "modifyDeliveryServiceGenSettings";
        	$this->deliveryService = $id;     
        	return ( parent::update($id,$data) );
        }        
    }
?>