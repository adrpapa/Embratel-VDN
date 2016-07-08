<?php
    require_once "CDSM.php";

    // ** Class DeliveryService
    // Permite criar e excluir CDMS Delivery Service
    // **
    class DeliveryService extends CDSM {

        public function __construct( $deliveryService=null,$contentOrigin=null,$description=null ) {
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
        			"storagePriorityClass",
        			"deliveryService",
        			"contentOrigin",		
        			"desc",
        			"param",
        			"ruleFile"
        	);        	
        	parent::__construct();
        	$this->deliveryService = $deliveryService;
        	$this->contentOrigin = $contentOrigin;     
        	$this->desc = is_null($description) ? null:urlencode($description);
        	$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";      	
        }
         
        public function create($data=null) {
        	if ( is_null($this->deliveryService) || is_null($this->contentOrigin) || is_null($this->desc) )
        	{
        		throw new invalidargumentexception("DeliveryService::create() deliveryService,contentOrigin and description must not be null");
        	}        	
        	$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
        	$this->action = "createDeliveryService";

        	return ( parent::create($data) );
        }
        
        public function delete($id) {
        	$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
        	$this->action = "deleteDeliveryServices";
        	$this->deliveryService = $id;
        	return ( parent::delete($id) );        	
        }
        
        public function update($id) {
        	$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
        	$this->action = "modifyDeliveryService";
        	$this->deliveryService = $id;
        	return ( parent::update($id) );        	
        }                      
        
        public function get($name) {
        	$this->taskAPI = "com.cisco.unicorn.ui.ListApiServlet";
        	$this->action = "getDeliveryServices";
        	$this->param  = $name;
        	return ( parent::get($name) );
        }        
        
        public function applyRuleFile($id,$rule_id) {
        	$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
        	$this->action = "applyRuleFile";
        	$this->deliveryService = $id;
        	$this->ruleFile = $rule_id;
        	return ( parent::update($id) );  
        }
    }
?>