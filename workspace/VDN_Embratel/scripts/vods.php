<?php

//if (!defined('APS_DEVELOPMENT_MODE')) define ('APS_DEVELOPMENT_MODE', 'on');

require_once "aps/2/runtime.php";
require_once "elemental_api/deltaContents.php";

/**
 * @type("http://embratel.com.br/app/VDN_Embratel/vod/1.0")
 * @implements("http://aps-standard.org/types/core/resource/1.0")
 */
class vod extends \APS\ResourceBase {

    // Relation with the management context
    /**
        * @link("http://embratel.com.br/app/VDN_Embratel/context/1.0")
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
        * @type(string[])
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
        $this->content_encoding_charged = false;
        $proto = $this->https ? "https" : "http";
        $cdnName = sprintf("Client_%06d_vod_%s",$this->context->account->id,$proto);
        $alias = sprintf("vod%s", $proto);
        $originServer = sprintf("vod%d%s.delta.embratel.com.br",$this->context->account->id,
                            $this->https ? "s" : "");
        $originPath = sprintf("out/u/Client_%06d/vod/%s/",$this->context->account->id, $proto);
        $ds_name       = sprintf("ds-%s-%s", $alias, $this->context->account->id);
        $this->path = sprintf("%s/%s/%s",$originServer,$originPath,$this->content_name);
        // Verifica se já existe delivery service para o tipo de serviço,
        // se não houver, cria
        foreach( $this->context->cdns as $cdn ) {
            if( $cdn->delivery_service_name == $ds_name ) {
                echo "Content $this->content_name will use Delivery service: $ds-name \n";
                return;
            }
        }
        echo "Creating new CDN for Delivery service: $ds_name content: $this->content_name\n";

        $apsc = \APS\Request::getController();
        $apsc2 = $apsc->impersonate($this);
        $context = $apsc2->getResource($this->context->aps->id);
        $cdn = \APS\TypeLibrary::newResourceByTypeId("http://embratel.com.br/app/VDN_Embratel/cdn/1.0");
        $cdn->name = $cdnName;
        $cdn->description = $cdnName;
        $cdn->alias = $alias;
        $cdn->origin_server = $originServer;
        $cdn->origin_path = $originPath;
        $cdn->https = $this->https;
        $cdn->live = "false";
        $apsc2->linkResource($context, 'cdns', $cdn);

    }

    public function configure($new) {
    }

    public function upgrade(){

    }

    public function unprovision(){
        \APS\LoggerRegistry::get()->setLogFile("logs/vods.log");
        
        $clientid = sprintf("Client_%06d",$this->context->account->id);
        
        \APS\LoggerRegistry::get()->info(sprintf("Iniciando desprovisionamento do conteudo %s-%s do cliente %s",
                $this->content_id, $this->content_name, $clientid));

        try {
            ElementalRest::$auth = new Auth( 'elemental','elemental' );
            DeltaContents::delete($this->content_id);
        } catch (Exception $fault) {
            $this->logger->info("Error while deleting content $this->content_name, :\n\t" . $fault->getMessage());
            throw new Exception($fault->getMessage());
        }    	
        
        \APS\LoggerRegistry::get()->info(sprintf("Fim desprovisionamento do conteudo %s-%s do cliente %s",
                $this->content_id, $this->content_name, $clientid));
    }

    /**
        * Update traffic usage
        * @verb(GET)
        * @path("/updateVodUsage")
        */
    public function updateVodUsage () {
        $usage = array();
        $usage["VDN_VOD_Storage_MbH"] = 0;
        if( ! $this->content_encoding_charged ){ 
            $usage["VDN_VOD_Encoding_Minutes"] = $this->content_time_length;
            $this->content_encoding_charged = true;
        } else {
            $usage["VDN_VOD_Encoding_Minutes"] = 0;
        }
        var_dump($usage);
        return $usage;
    }
}
?>
