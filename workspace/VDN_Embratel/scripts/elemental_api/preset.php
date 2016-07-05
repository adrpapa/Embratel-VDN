<?php
class Preset {
	public $width;
	public $height;
	public $bitrate;
	public $framerate_num;
	public $framerate_denom;
	public $audio_bitrate;
	
	public function __construct($resol,$bitrate,$framerate,$abitrate) {
		$wh = explode('x', $resol);
		$this->width = $wh[0];
		$this->height = $wh[1];
		$this->bitrate = $bitrate;
		if( $framerate == 'FS' ) {
			$this->framerate_denom = null;
			$this->framerate_num = null;
		} else {
			$numdenom= explode('/', $framerate);
			$this->framerate_num = $numdenom[0];
			$this->framerate_denom = $numdenom[1];
		}
		$this->audio_bitrate = $abitrate;
	}
}

// Encapsula um conjunto de Presets
// Permite customizar um XML de submit à partir de um conjunto de Presets
//
// $presets = new Presets();
// $presets->addPreset(new Preset("1024","768","5000"),3);

class Presets {
	protected $array_preset;
	
	public function __construct() {
		$this->array_preset = array();
	}
	
	public function addPreset(Preset $pr,$stream_nb) {
		if ( $stream_nb == 0 ) $stream_nb = 1;
		$this->array_preset[ $stream_nb-1 ] = $pr;
	}
	
	public function getPresets() {
		return $this->array_preset;
	}
	
	public function customizePresets( $name, SimpleXMLElement $xml ) {	
		$width         = $xml->xpath("/*/stream_assembly/video_description/width");
		$height        = $xml->xpath("/*/stream_assembly/video_description/height");
		$frm_denom     = $xml->xpath("/*/stream_assembly/video_description/h264_settings/framerate_denominator");
		$frm_denom_nil = $xml->xpath("/*/stream_assembly/video_description/h264_settings/framerate_denominator/@nil");		
		$frm_num       = $xml->xpath("/*/stream_assembly/video_description/h264_settings/framerate_numerator");
		$frm_num_nil   = $xml->xpath("/*/stream_assembly/video_description/h264_settings/framerate_numerator/@nil");
		$frm_source    = $xml->xpath("/*/stream_assembly/video_description/h264_settings/framerate_follow_source");
		$gop_size      = $xml->xpath("/*/stream_assembly/video_description/h264_settings/gop_size");
		$audio_bitrate = $xml->xpath("/*/stream_assembly/audio_description/aac_settings/bitrate");
		$bitrate       = $xml->xpath("/*/stream_assembly/video_description/h264_settings/bitrate");

		$name_modifier = $xml->xpath("/*/output_group/output/name_modifier");
		
		foreach ($this->array_preset as $k => $preset_obj) {					
			if ( is_null($preset_obj) ) {
				continue;
			}
			
			// No framerate conversion from source ?
			if(is_null($preset_obj->framerate_denom) && is_null($preset_obj->framerate_num)) {
				$follow_source = true;
			}
			else {
				$gop_size[$k][0]    = round($preset_obj->framerate_num/$preset_obj->framerate_denom) * 3;
			}
			
			$bitrate[$k][0]       = $preset_obj->bitrate;
			$width[$k][0]         = $preset_obj->width;
			$height[$k][0]        = $preset_obj->height;
			$frm_source[$k][0]    = $follow_source ? "true":"false";
			$frm_num[$k][0]       = $preset_obj->framerate_num;
			$frm_denom[$k][0]     = $preset_obj->framerate_denom;
			$frm_denom_nil[$k][0] = $follow_source ? "true":"false";
			$frm_num_nil[$k][0]   = $follow_source ? "true":"false";
			$audio_bitrate[$k][0] = $preset_obj->audio_bitrate;

			$name_modifier[$k][0] = $name.'_'.$k;
			if ( $k >= count($bitrate) ) break;
		}
		
		return $xml;
	}
}
