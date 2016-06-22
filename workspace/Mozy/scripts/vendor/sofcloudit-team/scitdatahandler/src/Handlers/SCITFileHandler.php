<?php namespace SCITDataHandler\Handlers;

use SCITDataHandler\Model\SCITDataModel as Model;

/**
 * Dump a Entry into the file system
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
 * @package SCITEntryHandler
 */
class SCITFileHandler extends SCITAbstractHandler
{

    /**
     * Says where the error should go
     * @var int
     */
    private $message_type = 3;

    /**
     * The log file path
     * @var string
     */
    protected $path;

    /**
     * The log file name
     * @var string
     */
    protected $file;

    /**
     * Prepare the object to be dumped into
     * a log file in the selected path
     *
     * @param Model $entry
     */
    public function run(Model $entry)
    {
        $entry->template = $this->template;
        $placeholder = $this->createPlaceholders($entry);

        if(!file_exists($this->path)){
            mkdir($this->path,0777,true);
        }

        $path = $this->path . "/" . str_replace($placeholder['marks'], $placeholder['replaces'], $this->file);
        error_log($entry, $this->message_type, $path);
    }

    /**
     * Create the placeholders of the name entry
     * @param Model $entry
     * @return mixed
     */
    private function createPlaceholders(Model $entry)
    {
        $placeholders['marks'] = array("{date}");
        $placeholders['replaces'] = array($entry->time->format('Y-m-d'));

        $entryParams = $entry->toArray();

        foreach ($entryParams as $key => $value) {
            $placeholders['marks'][] = "{" . $key . "}";
            $placeholders['replaces'][] = $value;
        }
        return $placeholders;
    }
}