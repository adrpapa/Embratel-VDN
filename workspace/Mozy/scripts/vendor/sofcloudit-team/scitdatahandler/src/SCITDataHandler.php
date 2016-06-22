<?php namespace SCITDataHandler;

use SCITDataHandler\Model\SCITDataModel as Model;

/**
 * AbstractHandler
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
class SCITDataHandler
{
    /**
     * The list of the log handlers
     * @var array
     */
    protected $handlers;

    /**
     * The main configuration of the error handler
     * @var object
     */
    protected $configuration;

    /**
     * Create a SCITEntryHandler instance
     * @param object $configuration
     */
    public function __construct($configuration = null)
    {
        foreach ($configuration as $name => $parameters) {

            $parameters = (object)$parameters;

            if(isset($parameters->namespace)){
                $namespace = $parameters->namespace;
            } else {
                $namespace = $name;
            }

            $this->registerHandler($name,$namespace,$parameters);
        }
    }

    /**
     * Register a new handler
     * @param string $name The handler name
     * @param string $namespace The handler namespace or internal name
     * @param object $parameters array of params
     */
    public function registerHandler($name, $namespace, $parameters = null)
    {
        if (class_exists($namespace)) {
            $handler = new $namespace($parameters);
        } else {
            $namespace = '\\SCITDataHandler\Handlers\SCIT' . $name . 'Handler';
            $handler = new $namespace($parameters);
        }

        $this->handlers[$name] = $handler;
    }

    /**
     * Unregister a handler
     * @param string $name The handler name
     * @return bool
     */
    public function unregisterHandler($name)
    {
        if (array_key_exists($name, $this->handlers)) {
            unset($this->handlers[$name]);
            return true;
        }
    }

    /**
     * Configure al dispatch all the error handlers.
     *
     * The handlers allow to the SCITErrorHandler class save
     * the information of each entry in several forms,
     * for example, the FileHandler allow to create
     * file log, the SysLogHandler allow to dump the
     * entries into the syslog log.
     *
     * @param Model $entry
     * @return array
     */
    public function dispatch(Model $entry)
    {
        $result = array();
        foreach ($this->handlers as $handler) {
            $result[] = $handler->run($entry);
        }
        return $result;
    }
}