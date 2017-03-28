
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
        
        public function assignSEs($id) {
        	$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
        	$this->action = "assignSEs";
        	$this->deliveryService = $id;
        	$this->contentAcquirer = ServiceEngine::getSEList();
        	$this->se = 'all';
        	return ( parent::update(null) );
        }

        public function unassignSEs($id) {
        	$this->taskAPI = "com.cisco.unicorn.ui.ChannelApiServlet";
        	$this->action = "unassignSEs";
        	$this->deliveryService = $id;
        	$this->se = 'all';
        	return ( parent::update(null) );        	
        }

    }


	require_once "contentOrigin.php";
    class TestDeliveryService {
		protected $custom_name = 'testeBand';
		protected $origin_server = 'testeband.org';
		protected $origin_domain = 'testeband.org';
		protected $description = 'teste cdn para unassign Service Engines';
		protected $dsid = 'Channel_3009';
		protected $orid = 'WebSite_3007';
		
		public static function assignSE(){
			$ds = new DeliveryService();
			if( ! $ds->assignSEs($dsid) ) {
				throw new RuntimeException( $ds->getMessage());
			}
			return;	
		}
		
		public static function deleteDS(){
			$ds = new DeliveryService();
			if( ! $ds->delete($dsid) ) {
				throw new RuntimeException( $ds->getMessage());
			}
			return;	
		}
		
		public static function createDS(){
			$origin = new ContentOrigin("co-".$custom_name, $origin_server, $origin_domain, $description);
			if ( !$origin->create() ) {
				throw new RuntimeException( $origin->getMessage());
			}
			print "Content Origin created with ID=".$origin->getID()."\n";
			$ds = new DeliveryService($custom_name, $origin->getID(), $description);
			
			/* INICIO Alteração sugeridas pelo Daniel Pinkas da Cisco */
			$ds->live = "false";	// ($this->live ? "true":"false");
			/* FIM Alteração sugeridas pelo Daniel Pinkas da Cisco */
			
			if ( !$ds->create() ) {
				throw new RuntimeException( $ds->getMessage());
			}
			
			// ASSIGN SEs
			if ( ! $ds->assignSEs($ds->getID() ) ) {
				throw new RuntimeException( $ds->getMessage());
			}		
			
			print "\n\n\nDelivery service created with ID=".$ds->getID()."\n";
			print "Content Origin created with ID=".$origin->getID()."\n";
		}
    }
?>