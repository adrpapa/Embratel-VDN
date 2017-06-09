<?php
/*
** Constantes de uso geral para as chamadas de API do Elemental
*/
	class ConfigConsts {
		
		public static $debug = true;
		
		public static $TEMPLATE_PATH = 'templates';
		public static $BILLING_LOG_PATH = 'billing';

		public static $SSH_PRIVATE_KEY = "/var/www/.ssh/id_rsa";
		public static $SSH_PUBLIC_KEY = "/var/www/.ssh/id_rsa.pub";

		//********** CDN ***********
		public static $CDMS_ADDRESS = "192.118.77.183";
		public static $CDMS_PORT = "8443";
		public static $CDMS_USER = "admin";
		public static $CDMS_PWD = "C1sc0CDN!";
		public static $CDMS_DOMAIN = "csi.cds.cisco.com";
		public static $CDMS_MAX_BITRATE_PER_SESSION = "12000";
		
		//********* Splunk *********
		public static $SPLUNK_ADDRESS = '192.118.76.206';
		public static $SPLUNK_ENDPOINT = '/splunkApp/en-US/custom/CDN_Usage_Reporting/cdnusage/metric_data';
		public static $SPLUNK_QUERY = '?metric=cdn_ds_bytes_delivered&time_range=%s&span=%s&delivery_service=%s&time_format';
		public static $EMAIL_TEMPLATE_NAME = '';
		public static $PORTAL_ANALYTICS_URL;
		public static $PBA_API = 'http://172.16.130.119:5224/RPC2';
		public static $POA_API = 'http://172.16.130.119:8440/RPC2';

		public static function loadConstants( $resource ) {
			// ConfigConsts::$TEMPLATE_PATH                = $globais.$TEMPLATE_PATH;
			// ConfigConsts::$BILLING_LOG_PATH             = $globais.$BILLING_LOG_PATH;
			// ConfigConsts::$SSH_PRIVATE_KEY              = $globais.$SSH_PRIVATE_KEY;
			// ConfigConsts::$SSH_PUBLIC_KEY               = $globais.$SSH_PUBLIC_KEY;
			self::$CDMS_ADDRESS                 = $resource->global->CDMS_ADDRESS;
			self::$CDMS_PORT                    = $resource->global->CDMS_PORT;
			self::$CDMS_USER                    = $resource->global->CDMS_USER;
			self::$CDMS_PWD                     = $resource->global->CDMS_PWD;
			self::$CDMS_DOMAIN                  = $resource->global->CDMS_DOMAIN;
			if( $resource->global->CDMS_MAX_BITRATE_PER_SESSION != null ) {
				self::$CDMS_MAX_BITRATE_PER_SESSION = $resource->global->CDMS_MAX_BITRATE_PER_SESSION;
			}
			self::$SPLUNK_ADDRESS				= $resource->global->SPLUNK_ADDRESS;
			self::$SPLUNK_ENDPOINT				= $resource->global->SPLUNK_ENDPOINT;
			self::$SPLUNK_QUERY					= $resource->global->SPLUNK_QUERY;
			if( $resource->global->EMAIL_TEMPLATE_NAME != null ) {
				self::$EMAIL_TEMPLATE_NAME		= $resource->global->EMAIL_TEMPLATE_NAME;
			}
			if( $resource->global->PBA_API != null || $resource->global->PBA_API == "" ) {
				self::$PBA_API		= $resource->global->PBA_API;
			}
			if( $resource->global->POA_API != null || $resource->global->POA_API == "" ) {
				self::$POA_API		= $resource->global->POA_API;
			}
			self::$PORTAL_ANALYTICS_URL			= $resource->global->PORTAL_ANALYTICS_URL;
		}
	}
?>