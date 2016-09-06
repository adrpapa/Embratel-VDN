<?php

//if (!defined('APS_DEVELOPMENT_MODE')) define ('APS_DEVELOPMENT_MODE', 'on');

require_once "aps/2/runtime.php";
require_once "utils/niceSSH.php";
require_once "elemental_api/utils.php";
require_once "elemental_api/deltaContents.php";

/**
 * @type("http://embratel.com.br/app/VDN_Embratel/vod/2.0")
 * @implements("http://aps-standard.org/types/core/resource/1.0")
 */
class vod extends \APS\ResourceBase {

    // Relation with the management context
    /**
        * @link("http://embratel.com.br/app/VDN_Embratel/context/2.0")
        * @required
        */
    public $context;

    /**
        * @type(integer)
        * @title("Content ID")
        * @description("Content ID in Elemental Delta")
        */
    public $content_id;

    /**
        * @type(string)
        * @title("Content Name")
        * @description("Content Name")
        */
    public $content_name;

    /**
        * @type(string)
        * @title("Description")
        * @description("Content Description")
        */
    public $description;

    /**
        * @type(string)
        * @title("Service Routing URI")
        * @description("Service Routing URI")
        * @readonly
        */
    public $cdn_routing_uri;

    /**
        * @type(string)
        * @title("Content Path")
        * @description("Content Path")
        */
    public $path;

    /**
        * @type(number)
        * @title("Content size in MB")
        * @description("Content size in MB")
        */
    public $content_storage_size;

    /**
        * @type(string)
        * @title("content creation timestamp")
        * @description("Content creation timestamp")
        */
    public $content_creation_ts;

    /**
        * @type(number)
        * @title("Content length in minutes")
        * @description("Content length in minutes")
        */
    public $content_time_length;

    /**
        * @type(boolean)
        * @title("Encoding charged")
        * @description("Flag to verify if encoding time has been billed")
        */
    public $content_encoding_charged;

    /**
        * @type(string)
        * @title("Screen Format")
        * @description("4:3 / 16:9 ?")
        */
    public $screen_format;

    /**
        * @type(boolean)
        * @title("Extended Configuration (Premium)")
        * @description("Allow transcoder fine-tuning and multiple transmux packaging")
        */
    public $premium;

    /**
        * @type(boolean)
        * @title("HTTPS")
        * @description("Turn on HTTPS feature for live")
        */
    public $https;

    /**
        * Readonly parameters obtained from Elemental Server
        */
        
    /**
        * @type(integer)
        * @title("Job ID")
        * @description("Job ID in Elemental Server Conductor")
        * @readonly
        */
    public $job_id;

    /**
        * @type(string)
        * @title("Input URI")
        * @description("Job Input URI for video ingestion")
        * @readonly
        */
    public $input_URI;
    /**
        * @type(string)
        * @title("Resolutions")
        * @description("Array of Video Resolutions for the generated streams")
        * @readonly
        */
    public $resolutions;

    /**
        * @type(string[])
        * @title("Frame Rates")
        * @description("Array of Frame Rates for the generated streams")
        * @readonly
        */
    public $framerates;

    /**
        * @type(string[])
        * @title("Video Bitrates")
        * @description("Array of Video Bitrates for the generated streams")
        * @readonly
        */
    public $video_bitrates;

    /**
        * @type(string[])
        * @title("Audio Bitrates")
        * @description("Array of Audio Bitrates for the generated streams")
        * @readonly
        */
    public $audio_bitrates;

#############################################################################################################################################
## Definition of the functions that will respond to the different CRUD operations
#############################################################################################################################################
    public function provision() {
        $logger = $this->getLogger();
        $clientName = formatClientID($this->context);
        $clientId = getClientID($this->context);
        $logger->info(sprintf("Iniciando provisionamento do conteudo %s-%s da subscrição %s",
                              $this->content_id, $this->content_name, $clientName));
        $this->content_encoding_charged = false;
        $proto = $this->https ? "https" : "http";
        $cdnName = sprintf("%s_vod_%s", $clientName, $proto);
        $alias = sprintf("vod%s", $proto);
        $originServer = sprintf("vod%d%s.origemcdn.embratelcloud.com.br",
                                $clientId,$this->https ? "s" : "");
        $originPath = sprintf("out/u/%s/vod/%s/",$clientName, $proto);
        $ds_name       = sprintf("ds-%s-%s", $alias, $clientId);
        $this->path = sprintf("%s/%s",$originServer,$this->content_name);
        $this->cdn_routing_uri = sprintf("%s.%d.csi.cds.cisco.com/%s", $alias, $clientId, $this->content_name);
        $usage = $this->getUsage();
        $this->content_storage_size = $usage["size"];

        // Verifica se já existe delivery service para o tipo de serviço,
        // se não houver, cria
        foreach( $this->context->cdns as $cdn ) {
            if( $cdn->delivery_service_name == $ds_name ) {
                $logger->info("Content $this->content_name will use Delivery service: $ds_name");
                return;
            }
        }
        $logger->info("Creating new CDN for Delivery service: $ds_name content: $this->content_name");

        $apsc = \APS\Request::getController();
        $apsc2 = $apsc->impersonate($this);
        $context = $apsc2->getResource($this->context->aps->id);
        $cdn = \APS\TypeLibrary::newResourceByTypeId("http://embratel.com.br/app/VDN_Embratel/cdn/2.0");
        $cdn->name = $cdnName;
        $cdn->description = $cdnName;
        $cdn->alias = $alias;
        $cdn->origin_server = $originServer;
        $cdn->origin_path = $originPath;
        $cdn->https = $this->https;
        $cdn->live = "false";
        $apsc2->linkResource($context, 'cdns', $cdn);
        $logger->info(sprintf("Finalizando provisionamento do conteudo %s-%s da subscrição %s",
                $this->content_id, $this->content_name, $clientName));
    }

    public function getUsage(){
        $logger = $this->getLogger();
        $clientName = formatClientID($this->context);
        $proto = $this->https ? "https" : "http";
        try{
            $logger->debug("Obtendo usage do delta via ssh");
            return NiceSSH::getUsage($clientName, $proto, $this->premium ? "prm": "std", $this->content_name);
        } catch (Exception $fault ) {
            $logger->error("Erro ao buscar informações via ssh no delta");
            $logger->error($fault->getMessage());
            $data = array("size"=>0,"name"=>"","age"=>0);
            return $data;
        }
    }

    public function configure($new) {
    }

    public function upgrade(){

    }

    public function unprovision(){
        $logger = $this->getLogger();
        $logger->info(sprintf("Iniciando desprovisionamento do conteudo %s-%s",
                $this->content_id, $this->content_name));

        try {
            ElementalRest::$auth = new Auth( 'elemental','elemental' );
            DeltaContents::delete($this->content_id);
        } catch (Exception $fault) {
            $logger->info("Error while deleting content $this->content_name, :\n\t" . $fault->getMessage());
//             throw new Exception($fault->getMessage());
        }    	
        
        $logger->info(sprintf("Fim desprovisionamento do conteudo %s-%s",
                $this->content_id, $this->content_name));
    }

    private function getLogger() {
        $logger = \APS\LoggerRegistry::get();
        $logger->setLogFile("logs/vods.log");
        return $logger;
    }
    
    /**
        * Update traffic usage
        * @verb(GET)
        * @path("/updateVodUsage")
        */
    public function updateVodUsage () {
        $usage = array();
        if( ! $this->content_encoding_charged ){ 
            $usage["VDN_VOD_Encoding_Minutes"] = round($this->content_time_length / 60000);
            $this->content_encoding_charged = true;
            $apsc = \APS\Request::getController();
            $apsc->updateResource($this);
        } else {
            $usage["VDN_VOD_Encoding_Minutes"] = 0;
        }
        $storage = $this->getUsage();
        $usage["size"] = $storage["size"];
        $usage["name"] = $storage["name"];
        $usage["age"] = $storage["age"];

        $this->getLogger()->info(print_r($usage));
        return $usage;
    }
}
?>
