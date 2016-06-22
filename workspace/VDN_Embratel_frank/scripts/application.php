<?php

	define('APS_DEVELOPMENT_MODE', true);
	require "aps/2/runtime.php";	

	/**
	* @type("http://embratel.com.br/app/VDN_Embratel/application/1.0")
	* @implements("http://aps-standard.org/types/core/application/1.0")
	*/

	class VDN_Embratel extends \APS\ResourceBase
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
		/**
		 * @link("http://embratel.com.br/app/VDN_Embratel/context/1.0")
		 */
		public $context;
		
		# Global content provider settings
		# Must be forwarded to the app end-point
		/**
		 * @type(string)
		 * @title("plano")
		 * @description("Plano de Serviços Std/Premium")
		 */
		public $plano;
		
		/**
		 * @type(integer)
		 * @title("volume_in")
		 * @description("Franquia de volume(h) mensal ingerido")
		 */
		public $volume_in;
		
		/**
		 * @type(integer)
		 * @title("canais")/home/fastlane/Dropbox/Clientes/Fastlane/Embratel/pgms/scripts/apsstandardVpn
		 * @description("Número de eventos ao vivo simultâneos")
		 */
		public $canais;
		
		/**
		 * @type(integer)
		 * @title("retencao")
		 * @description("Retenção(H) de trasmissões ao vivo - DVR")
		 */
		public $retencao;
		
		/**
		 * @type(integer)
		 * @title("volume_out")
		 * @description("Franquia de volume(TB) entregue aos consumidores de conteúdo")
		 */
		public $volume_out;
		
		/**
		 * @type(boolean)
		 * @title("https")
		 * @description("Usar protocolo HTTPS para trasmissão de conteúdo")
		 */
		public $https;
		
		/**
		 * @type(boolean)
		 * @title("jit_encrypt")
		 * @description("Utilizar criptografia na trasmissão de conteúdo")
		 */
		public $jit_encrypt;
	}

?>
