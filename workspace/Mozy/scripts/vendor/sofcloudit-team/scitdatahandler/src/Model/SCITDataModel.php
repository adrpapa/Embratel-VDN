<?php namespace SCITDataHandler\Model;

use stdClass;
use DateTime;

/**
 * Data model
 * @author  SofCloudIT <info@sofcloudit.com>
 * @copyright Copyright (C) 2015 Ingram Micro Inc.  Any rights not granted herein
 * are reserved for Ingram Micro Inc. Permission to use, copy and distribute this
 * source code without fee and without a signed license agreement is hereby granted
 * provided that: (i) the above copyright notice and this paragraph appear in all
 * copies and distributions; and (ii) the source code is only used, copied or
 * distributed for the purpose of using it with the APS package for which Ingram Micro Inc.
 * or its affiliates integrated it into.  Ingram Micro Inc. may revoke the limited license
 * granted herein at any time at its sole discretion.  THIS SOURCE CODE IS PROVIDED
 * "AS IS".  INGRAM MICRO INC. MAKES NO REPRESENTATIONS OR WARRANTIES AND DISCLAIMS
 * ALL IMPLIED WARRANTIES OF MERCHANTABILITY OR FITNESS FOR ANY PARTICULAR PURPOSE.
 * Copyright (C) 2015 Ingram Micro Inc.  Any rights not granted herein are reserved for
 * Ingram Micro Inc. Permission to use, copy and distribute this source code without
 * fee and without a signed license agreement is hereby granted provided that: (i) the
 * above copyright notice and this paragraph appear in all copies and distributions; and
 * (ii) the source code is only used, copied or distributed for the purpose of using it with
 * the APS package for which Ingram Micro Inc. or its affiliates integrated it into.
 * Ingram Micro Inc. may revoke the limited license granted herein at any time at its sole
 * discretion.  THIS SOURCE CODE IS PROVIDED "AS IS".  INGRAM MICRO INC. MAKES NO REPRESENTATIONS
 * OR WARRANTIES AND DISCLAIMS ALL IMPLIED WARRANTIES OF MERCHANTABILITY OR FITNESS FOR ANY
 * PARTICULAR PURPOSE.
 * @version 0.1 Beta
 * @package SCITDataHandler
 */
abstract class SCITDataModel
{
	/**
	 * Creation timestamp
	 * @var DateTime
	 */
	public $time;

	/**
	 * The template of the text entry
	 * @var string
	 */
	public $template;

	/**
	 * The list of ignored properties
	 * by the handlers
	 * @var array
	 */
	public $hidden = array();

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		$this->time = new DateTime('now');
	}

	/**
	 * Return all the filled properties
	 * @return array
	 */
	public function toArray()
	{
		$hidden = array_merge(array('hidden','template','time'),$this->hidden);

		$properties = get_object_vars($this);
		foreach($properties as $key => $value){
			if(in_array($key,$hidden)){
				unset($properties[$key]);
			}
		}
		$properties['time'] = $this->time->format('Y-m-d H:i:s');
		return $properties;
	}

    /**
     * Return the object in json format
     * @return string
     */
    public function toJson()
    {
        $json = new stdClass();
        $hidden = array_merge(array('hidden','template','time'),$this->hidden);

        $properties = get_object_vars($this);
        foreach($properties as $key => $value){
            if(!in_array($key,$hidden)){
                $json->$key = $value;
            }
        }
        $json->time = $this->time->format('Y-m-d H:i:s');
        return json_encode($json);
    }

	/**
	 * Transform the object to string
	 * @return string
	 */
	public function __toString()
	{
		$placeholders = array("{datetime}");
		$replaces = array($this->time->format('Y-m-d H:i:s'));

		$properties = $this->toArray();
		foreach($properties as $key => $value){
			$placeholders[] = "{".$key."}";
			$replaces[] = $value;
		}

		return str_replace($placeholders, $replaces, $this->template) . "\n";
	}
}