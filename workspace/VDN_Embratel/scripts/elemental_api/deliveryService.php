<?php
    require_once "CDSM.php";

    // ** Class DeliveryService
    // Permite criar e excluir CDMS Delivery Service
    // **
    class DeliveryService extends CDSM {
    	protected $deliveryService;
    	protected $contentOrigin;  
    	
    	
        public function __construct( $id,$deliveryService=null,$contentOrigin=null,$description=null ) {
        	$this->optional_params_names = array(
        			"weakCert",
        			"skipEncrypt",
        			"priority",
        			"mcastEnable",
        			"live",
        			"quota",
        			"qos",
        			"failoverIntvl",
        			"deliveryQos",
        			"sessionQuota",
        			"sessionQuotaAugBuf",
        			"bandQuota",
        			"bandQuotaAugBuf",
        			"storagePriorityClass"
        	);        	
        	parent::__construct();
        	$this->id = $id;
        	$this->deliveryService = $deliveryService;
        	$this->contentOrigin = $contentOrigin;     
        	$this->description = $description;
        	$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";      	
        }
         
        public function create() {
        	if ( is_null($this->deliveryService) || is_null($this->contentOrigin) || is_null($this->description) )
        	{
        		throw new invalidargumentexception("DeliveryService::create() deliveryService,contentOrigin and description must not be null");
        	}        	
        	$this->action = "createDeliveryService";
        	$this->params = "&deliveryService=" . $this->deliveryService;
        	$this->params .= "&contentOrigin=" . $this->contentOrigin;
        	$this->params .= "&desc=" . urlencode($this->description);        	
        	return ( parent::create() );
        }
        
        public function delete() {
        	$this->action = "deleteDeliveryServices";
        	$this->params = "&deliveryService=" . $this->id;
        	return ( parent::delete() );        	
        }
        
        public function update() {
        }        
    }
?>