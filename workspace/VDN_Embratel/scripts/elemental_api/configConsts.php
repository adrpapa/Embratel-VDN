<?php
/*
** Constantes de uso geral para as chamadas de API do Elemental
*/
    class ConfigConsts {
        
        const debug = false;
        
        const TEMPLATE_PATH = 'templates';

        const API_VERSION = 'v1';
        const LIVE_CONDUCTOR_HOST = '201.31.12.4';
        const VOD_CONDUCTOR_HOST  = '201.31.12.7';
        
        const LIVE_TEMPLATE_STANDARD = 13;
        const LIVE_TEMPLATE_PREMIUM = 13;
        const LIVE_NODE_URL = 'rtmp://localhost:1935/';

        const VOD_TEMPLATE_STANDARD = 75;
        const VOD_TEMPLATE_PREMIUM = 75;        
        
        const DELTA_HOST = '201.31.12.36';
        const DELTA_PORT = '8080';
                
        const DELTA_UDP_INPUT_FILTER_TEMPLATE = 274;
        const DELTA_WF_INPUT_FILTER_TEMPLATE = 42;

        const DELTA_LIVE_STORAGE_LOCATION = '/data/server/drive/live';
        const DELTA_VOD_STORAGE_LOCATION = '/data/server/drive/vod';
        const DELTA_WF_INCOMMING_URI = '/data/server/drive/watchfolders';
        
        const DELTA_STD_EVENT_OUTPUT_TEMPLATE = 10;
        const DELTA_PREMIUM_EVENT_OUTPUT_TEMPLATE = 10;
        const DELTA_STD_VOD_OUTPUT_TEMPLATE = 10;
        const DELTA_PREMIUM_VOD_OUTPUT_TEMPLATE = 10;

        //********** CDN ***********
        const CDMS_ADDRESS = "192.118.77.183";
        const CDMS_PORT = "8443";
        const CDMS_USER = "admin";
        const CDMS_PWD = "C1sc0CDN!x";
    }
?>
