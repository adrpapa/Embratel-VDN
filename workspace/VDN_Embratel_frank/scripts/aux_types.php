<?php
	/**
	 * Class content
	 */
	
	class content {
		/**
		 * @type(string)
		 * @title("input_URI")
		 * @description("Endereço da origem do conteúdo")
		 * @required
		 */
		public $input_URI;	

		/**
		 * @type(string)
		 * @title("screen_format")
		 * @description("Formato da tela (4:3 / 16:9)")
		 */
		public $screen_format;

		/**
		 * @type(string)
		 * @title("profile_id")
		 * @description("Preset utilizado para fazer encoding do vídeo")
		 */
		public $profile_id;

		/**
		 * @type(string)
		 * @title("status")
		 * @description("Estado de processamento do conteúdo")
		 * @readonly
		 */
		public $status;		
	}
	
	class premium_parms {
		/**
		 * @type("string")
		 * @title("resolution")
		 * @description("Resolução 1600x1200 960x720 640x480 480x360 360x240 1920x1080 1280x720")
		 */
		public $resolution;
		
		/**
		 * @type("string")
		 * @title("bitrate")
		 * @description("Bitrate 5Mbps 3.5Mbps 2Mbps 1.2Mbps 800kbps 650kbps 480kbps 250kbps")
		 */
		public $bitrate;

		/**
		 * @type("string")
		 * @title("v_framerate")
		 * @description("Video Framerate 29.97 14.985")
		 */
		public $v_framerate;
		
		/**
		 * @type("string")
		 * @title("a_framerate")
		 * @description("Áudio Framerate 96kbps 64kbps")
		 */
		public $a_framerate;
		
	}

?>
		