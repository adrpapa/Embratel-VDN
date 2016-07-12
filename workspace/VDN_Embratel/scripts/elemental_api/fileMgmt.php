<?php
    require_once "CDSM.php";

    // **
    // Class DeliveryServiceGenSettings
    // Permite customizar um Delivery Service
    // **
    class FileMgmt extends CDSM {

    	protected $internal_file_type;
    	
        public function __construct( $file_type="20",$dest_name=null,$import_method="upload" ) {
                                	
        	$this->optional_params_names = array(
					"fileType",
        			"destName",
        			"importMethod",
        			"originUrl",
        			"ttl",
        			"username",
        			"password",
        			"domain",
        			"disableBasicAuth",
        			"id"
        	);        	
        	parent::__construct();
        	$this->internal_file_type = $file_type;
        	$this->fileType = $file_type;
        	$this->destName = $dest_name;
        	$this->importMethod = $import_method;
        	$this->taskAPI = "com.cisco.unicorn.ui.FileMgmtApiServlet";      	
        }
             
        public function create($data=null) {      	
        	if ( is_null($this->fileType) ||
        			is_null($this->destName) ||
        			is_null($this->importMethod) )
        		{
        			throw new invalidargumentexception("FileMgmt::create() parameters required must not be null");
        		}
        		
        	$this->action = "registerFile";

        	return( parent::create( $data ) );
        }
        
        public function delete($id) {
        	$this->action = "deleteFile";
			$this->id = $id;
			$this->fileType = $this->internal_file_type;
        	return( parent::delete( $id ) );
        }
        
        public function update($id) {
        }        
        
        public function createUrlRewriteRule($domain,$path,$protocol="http") {
        	$xml_rule = $this->getXML();
        	
        	if( ConfigConsts::debug ) {
        		print( $xml_rule );
        	}
        	
        	//REGSUB = "<protocol>://(.*.<domain>)/(.*)"
        	//rewrite-url = "<protocol>://$1/<path>/$2"
        	
        	$regsub      = $xml_rule->xpath("/CDSRules/Rule_Actions/Rule_UrlRewrite/@regsub");
        	$rewrite_url = $xml_rule->xpath("/CDSRules/Rule_Actions/Rule_UrlRewrite/@rewrite-url");
        	
        	$regsub[0][0]      = $protocol . "://(.*." . $domain . ")/(.*)";
        	$rewrite_url[0][0] = $protocol . "://$1/" . $path . "/$2";
 
        	if( ConfigConsts::debug ) {
        		print( $xml_rule );
        	}        	
        	
        	return( $this->create( $xml_rule->asxml() ) );
        }
        
        protected function getXML() {
        	$templateFilename = ConfigConsts::TEMPLATE_PATH.'/rule-url-rwr.xml';
        	
        	if ( file_exists($templateFilename) ) {
        		$xml = simplexml_load_file($templateFilename);
        		if ( $xml === FALSE ) {
        			throw new Exception("Rule XML file is invalid.");
        		}
        	}
        	else {
        		throw new Exception("Rule XML file does not exist.");
        	}
        	return( $xml );
        }
    }
?>