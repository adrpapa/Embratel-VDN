<?php
class Preset {
	public $width;
	public $height;
	public $bitrate;
	public $framerate_num;
	public $framerate_denom;
	public $audio_bitrate;
}

class Presets {
	protected $array_preset;
	
	public function __construct() {
		$array_preset = array();
	}
	
	public function addPreset(Preset $pr) {
		array_push($this->array_preset, $pr);
	}
	
	public function getPresets() {
		return $this->array_preset;
	}
	
	public function customizePresets( $xml ) {
		$width         = $xml->xpath("/job/stream_assembly/video_description/width");
		$height        = $xml->xpath("/job/stream_assembly/video_description/height");
		$frm_denom     = $xml->xpath("/job/stream_assembly/video_description/h264_settings/framerate_denominator");
		$frm_num       = $xml->xpath("/job/stream_assembly/video_description/h264_settings/framerate_numerator");
		$audio_bitrate = $xml->xpath("/job/stream_assembly/audio_description/aac_settings/bitrate");
		$bitrate       = $xml->xpath("/job/stream_assembly/video_description/h264_settings/bitrate");
		$counter = 0;
		
		foreach ($this->array_preset as $k => $v) {
			$bitrate[$counter][0]       = $v->bitrate;
			$width[$counter][0]         = $v->width;
			$height[$counter][0]        = $v->height;
			$frm_denom[$counter][0]     = $v->framerate_denom;
			$frm_num[$counter][0]       = $v->framerate_num;
			$audio_bitrate[$counter][0] = $v->audio_bitrate;
			$counter++;
			if ( $counter >= count($bitrate) ) break;
		}
		
		return $xml->asXML();
	}
}