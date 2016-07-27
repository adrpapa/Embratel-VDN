<?php
/*
** Constantes de uso geral para as chamadas de API do Elemental
*/
    class ConfigConsts {
        
        const debug = false;
        
        const TEMPLATE_PATH = 'templates';
//         const BILLING_LOG_PATH = 'logs';
        const BILLING_LOG_PATH = 'logs';

        const API_VERSION = 'v1';

        const LIVE_CONDUCTOR_HOST = '201.31.12.40';
        const LIVE_NODE_URL = 'rtmp://localhost:1935/';

        const VOD_CONDUCTOR_HOST  = '201.31.12.26';
        const VOD_WF_OUTRGOING_URI='/mnt/delta/drive/watchfolders/';
        
        const DELTA_HOST = '201.31.12.36';
        const DELTA_PORT = '8080';
/*        
        const DELTA_UDP_INPUT_FILTER_TEMPLATE = 274;
        const DELTA_WF_INPUT_FILTER_TEMPLATE = 76;
*/
        const DELTA_LIVE_STORAGE_LOCATION = '/data/server/drive/live';
        const DELTA_VOD_STORAGE_LOCATION = '/data/server/drive/vod';
        const DELTA_WF_INCOMMING_URI = '/data/server/drive/watchfolders';
        const DELTA_WEBDAV_STORAGE_LOCATION = '/data/server/drive/';
        
        const DELTA_STD_EVENT_OUTPUT_TEMPLATE = 7;
        const DELTA_PREMIUM_EVENT_OUTPUT_TEMPLATE = 8;
        const DELTA_STD_VOD_OUTPUT_TEMPLATE = 1;
        const DELTA_PREMIUM_VOD_OUTPUT_TEMPLATE = 2;

        //********** CDN ***********
        const CDMS_ADDRESS = "192.118.77.183";
        const CDMS_PORT = "8443";
        const CDMS_USER = "admin";
        const CDMS_PWD = "C1sc0CDN!";
        const CDMS_DOMAIN = "csi.cds.cisco.com";
        
        //********* Splunk *********
        const SPLUNK_ADDRESS = '192.118.76.206';
        const SPLUNK_ENDPOINT = '/splunkApp/en-US/custom/CDN_Usage_Reporting/cdnusage/metric_data';
        const SPLUNK_QUERY = '?metric=cdn_ds_bytes_delivered&time_range=%s&span=%s&delivery_service=%s&time_format';
	}
?>
