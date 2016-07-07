<?php
    require_once "CDSM.php";

    // **
    // Class DeliveryServiceGenSettings
    // Permite customizar um Delivery Service
    // **
    class FileMgmt extends CDSM {

        public function __construct( $file_type="20",$dest_name,$import_method="upload" ) {
                                	
        	$this->optional_params_names = array(
					"fileType",
        			"destName",
        			"importMethod",
        			"originUrl",
        			"ttl",
        			"username",
        			"password",
        			"domain",
        			"disableBasicAuth"
        	);        	
        	parent::__construct();
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
        }
        
        public function update($id) {
        }        
        
        public function createUrlRewriteRule($domain,$path) {
        	$xml_rule = $this->getXML();
        	// Change xml with domain and path
        	$this->create( $xml_rule );
        }
        
        protected function getXML() {
        	$templateFilename = ConfigConsts::TEMPLATE_PATH.'/rule-url-rwr.xml';
        	$xml = simplexml_load_file($templateFilename);
        	return( $xml->asXml() );
        }
    }
?>