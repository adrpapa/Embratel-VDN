<?php
	define('APS_DEVELOPMENT_MODE', true);
	require "aps/2/runtime.php";	
	include "aux_types.php";
	
	/**
	 * Class channel
	 * @type("http://embratel.com.br/app/VDN_Embratel/channel/1.0")
	 * @implements("http://aps-standard.org/types/core/resource/1.0")
	 */
	
	class channel extends \APS\ResourceBase
	{
		/**
	 	 * @link("http://embratel.com.br/app/VDN_Embratel/context/1.0")
		 * @required
		 */
		public $context;

		/**
		 * @type("string")
		 * @title("content_name")
		 * @description("Nome de referência do conteúdo")
		 * @required
		 */
		public $content_name;	

		/**
		 * @type(string)
		 * @title("input_URI")
		 * @required
		 */
		public $input_URI;	

		/**
		 * @type(string)
		 * @title("screen_format")
		 */
		public $screen_format;

		/**
		 * @type(string)
		 * @title("profile_id")
		 */
		public $profile_id;

		/**
		 * @type("premium_parms")
		 * @title("Premium parameters")
		 * @description("Parâmetros de override para subscriptions Premium")
		 */
		public $premium_parms;
		
		/**
		 * @type(string)
		 * @title("status")
		 * @readonly
		 */
		public $status;		

		/**
		 * @type(integer)
		 * @title("delta_port")
		 * @readonly
		 */
		public $delta_port;		
	}

?>
		