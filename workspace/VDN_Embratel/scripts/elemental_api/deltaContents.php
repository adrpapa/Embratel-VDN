<?php
    require_once "configConsts.php";
    require_once "elementalRest.php";
    require_once "utils.php";

    class DeltaContents {
    /*
    ** Classe que Lista conteúdos do cliente
    */
        public function __construct( $xml ) {
            $this->setPropertiesFromXML($xml);
        }
        
        public function setPropertiesFromXML( $xml ) {
			$this->id = end(explode('/', $xml['href']));
			foreach( $xml as $name => $obj ) {
				$this->{$name} = $obj.'';
			}
			print_r($this);
        }

        /*
        ** Obtem / list de conteúdo por input filter
        */
        public static function getContentsByFolder( $folder ) {
			$allContent=[];
            foreach( DeltaContents::getElementalRest()->restGet() as $xmlContent ){
                $allContent[] = new DeltaContents($xmlContent);
			}
        }
        
        public static function delete($id) {
        
            $outTemp = DeltaContents::getElementalRest()->restDelete($id);
        }

        public static function getElementalRest() {
			return new ElementalRest($hostname=ConfigConsts::DELTA_HOST,
                        $apiEndpoint='contents', $port=ConfigConsts::DELTA_PORT);
        }
    }
// 	DeltaContents::getContentsByFolder('/data/server/drive/watchfolders/Cliente1/')
?>
