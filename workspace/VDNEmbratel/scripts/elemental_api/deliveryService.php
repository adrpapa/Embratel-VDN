<?php
    require_once "CDSM.php";
    require_once "serviceEngine.php";

    // ** Class DeliveryService
    // Permite criar e excluir CDMS Delivery Service
    // **
    class DeliveryService extends CDSM {

    	protected $internal_delivery_service;
    	protected $internal_content_origin;
    	protected $internal_description;
    	
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
        			"deliveryServiceName",
        			"contentOrigin",		
        			"desc",
        			"param",
        			"ruleFile",
        			"contentAcquirer",
        			"se"
        	);        	
        	parent::__construct();
        	$this->internal_delivery_service = $deliveryService;
        	$this->internal_content_origin = $contentOrigin;
        	$this->internal_description = is_null($description) ? null:urlencode($description);
        	/**
        	$this->deliveryService = $deliveryService;
        	$this->contentOrigin = $contentOrigin;     
        	$this->desc = is_null($description) ? null:urlencode($description);
        	**/
        	$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";      	
        }
         
        public function create($data=null) {        	
        	$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
        	$this->action = "createDeliveryService";
        	$this->deliveryService = $this->internal_delivery_service;
        	$this->contentOrigin = $this->internal_content_origin;
        	$this->desc = $this->internal_description;

        	if ( is_null($this->deliveryService) || is_null($this->contentOrigin) || is_null($this->desc) )
        	{
        		throw new invalidargumentexception("DeliveryService::create() deliveryService,contentOrigin and description must not be null");
        	}        	
        	
        	return ( parent::create($data) );
        }
        
        public function delete($id) {
        	$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
        	$this->action = "deleteDeliveryServices";
        	$this->deliveryService = $id;
        	return ( parent::delete($id) );        	
        }
        
        public function update($id,$data=null) {
        	$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
        	$this->action = "modifyDeliveryService";
        	$this->deliveryService = $id;
        	$this->deliveryServiceName = $this->internal_delivery_service;
        	//$this->contentOrigin = $this->internal_content_origin;
        	$this->desc = $this->internal_description;        	
        	return ( parent::update($id,$data) );        	
        }                      
        
        public function get($name) {
        	$this->taskAPI = "com.cisco.unicorn.ui.ListApiServlet";
        	$this->action = "getDeliveryServices";
        	$this->param  = $name;
        	return ( parent::get($name) );
        }        
        
        public function applyRuleFile($rule_id) {
        	$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
        	$this->action = "applyRuleFile";
        	$this->deliveryService = $this->getID();
        	$this->ruleFile = $rule_id;
        	return ( parent::update(null,null) );  
        }
        
        public function assignSEs() {
        	$se = new ServiceEngine();
        	if ( $se->get(null) ) {
        		$list_ses = $se->getID();
        	}
        	else {
        		return false;
        	}
        	
        	$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
        	$this->action = "assignSEs";
        	$this->deliveryService = $this->getID();
        	if ( !$se->get( $se->getContentAcquirer() ) ) return false;
        	$this->contentAcquirer = $se->getID();
        	$this->se = $list_ses;
        	
        	return ( parent::update(null) );
        }
    }
?>