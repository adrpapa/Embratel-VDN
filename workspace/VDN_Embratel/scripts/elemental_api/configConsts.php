<?php
/*
** Constantes de uso geral para as chamadas de API do Elemental
*/
    class ConfigConsts {
        
        public static $debug = false;
        
        public static $TEMPLATE_PATH = 'templates';

        public static $API_VERSION = 'v1';
        public static $LIVE_CONDUCTOR_HOST = '201.31.12.4';
        public static $VOD_CONDUCTOR_HOST  = '201.31.12.7';
        
        public static $LIVE_TEMPLATE_STANDARD_4x3 = 13;
        public static $LIVE_TEMPLATE_STANDARD_16x9 = 13;
        public static $LIVE_TEMPLATE_PREMIUM_4x3 = 13;
        public static $LIVE_TEMPLATE_PREMIUM_16x9 = 13;
        public static $LIVE_NODE_URL = 'rtmp://localhost:1935/';

        public static $VOD_TEMPLATE_STANDARD_4x3 = 356;
        public static $VOD_TEMPLATE_STANDARD_16x9 = 356;
        public static $VOD_TEMPLATE_PREMIUM_4x3 = 356;        
        public static $VOD_TEMPLATE_PREMIUM_16x9 = 356;        
        
        public static $DELTA_HOST = '201.31.12.36';
        public static $DELTA_PORT = '8080';
                
        public static $DELTA_UDP_INPUT_FILTER_TEMPLATE = 274;
        public static $DELTA_WF_INPUT_FILTER_TEMPLATE = 42;

        public static $DELTA_LIVE_STORAGE_LOCATION = '/data/server/drive/live';
        public static $DELTA_VOD_STORAGE_LOCATION = '/data/server/drive/vod';

        public static $DELTA_WF_INCOMMING_URI = '/data/server/drive/vod/watchfolders';
        public static $DELTA_LIVE_INCOMMING_URI = '/data/server/drive/live/watchfolders';
        
        public static $DELTA_STD_EVENT_OUTPUT_TEMPLATE = 10;
        public static $DELTA_PREMIUM_EVENT_OUTPUT_TEMPLATE = 10;
        public static $DELTA_STD_VOD_OUTPUT_TEMPLATE = 10;
        public static $DELTA_PREMIUM_VOD_OUTPUT_TEMPLATE = 10;

        public static function loadGlobals( $clouds ) {
            
        }
    }
?>
