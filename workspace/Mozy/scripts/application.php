<?php

	define('APS_DEVELOPMENT_MODE', true);
	require "aps/2/runtime.php";	

	/**
	* @type("http://embratel.com.br/app/VDN/application/1.0")
	* @implements("http://aps-standard.org/types/core/application/1.0")
	*/

	class SampleAPS2Application extends \APS\ResourceBase
	{

		public function configure($new=null){

		}

		public function provision(){

		}

		public function retrieve(){

		}

		public function upgrade(){

		}

		public function unprovision(){

		}

	}

?>
