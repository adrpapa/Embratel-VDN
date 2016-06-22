<?php namespace SCITLogger;

use stdClass;
use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use SCITLogger\Psr\Log\LoggerInterface;
use SCITDataHandler\SCITDataHandler;

/**
 * Create logs and save them in many places
 * @author  SofCloudIT <info@sofcloudit.com>
 * @copyright Copyright (c) 2015 Ingram Micro Inc.  Any rights not granted herein
 * are reserved for Ingram Micro Inc. Permission to use, copy and distribute this
 * source code without fee and without a signed license agreement is hereby granted
 * provided that: (i) the above copyright notice and this paragraph appear in all
 * copies and distributions; and (ii) the source code is only used, copied or
 * distributed for the purpose of using it with the APS package for which Ingram Micro Inc.
 * or its affiliates integrated it into.  Ingram Micro Inc. may revoke the limited license
 * granted herein at any time at its sole discretion.  THIS SOURCE CODE IS PROVIDED
 * "AS IS".  INGRAM MICRO INC. MAKES NO REPRESENTATIONS OR WARRANTIES AND DISCLAIMS
 * ALL IMPLIED WARRANTIES OF MERCHANTABILITY OR FITNESS FOR ANY PARTICULAR PURPOSE.
 * Copyright (c) 2015 Ingram Micro Inc.  Any rights not granted herein are reserved for
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
 * @package SCITLogger
 */
class SCITLogger implements LoggerInterface
{
    /**
     * Error levels
     */
    const EMERGENCY = 0;
    const ALERT = 1;
    const CRITICAL = 2;
    const ERROR = 3;
    const WARNING = 4;
    const NOTICE = 5;
    const INFO = 6;
    const DEBUG = 7;

    /**
     * The list of the log handlers
     * @var array
     */
    protected $handler;

    /**
     * APS package identifier
     * @var string
     */
    protected $aps;

    /**
     * The ID account of the log
     * @var int
     */
    protected $account;

    /**
     * The ID subscription of the log
     * @var int
     */
    protected $subscription;

    /**
     * The main configuration of the log
     * @var object
     */
    protected $configuration;

    /**
     * Create a SCITLogger instance
     *
     * @param int $subscription
     * @param int $account
     * @param string $aps
     */
    public function __construct($subscription = 0, $account = 0, $aps = "APS-REF-NAME")
    {
        $configuration = CONFIG_PATH . "/logger.ini";

        if (file_exists($configuration)) {

            $configuration = (object)parse_ini_file($configuration, true);

        } else {

            $configuration = new stdClass();
            $configuration->logger = array(
                "level" => 4,
                "timezone" => "Europe/Madrid"
            );
            $configuration->handlers = array(
                "File" => true,
            );
            $configuration->File = array(
                "path" => "logs",
                "file" => "{date}-{account}-{subscription}.log",
                "template" => "[{datetime}] [{label}] [{code}] {message} {data}"
            );
        }

        $this->configuration = (object)$configuration->logger;

        $this->subscription = $subscription;
        $this->account = $account;
        $this->aps = $aps;

        if ($this->configuration->level > 0) {
            $handlers = array();
            foreach ($configuration->handlers as $name => $state) {
                if (isset($state) && $state) {
                    $handlers[$name] = $configuration->$name;
                }
            }
            $this->handler = new SCITDataHandler($handlers);
        }
    }

    /**
     * Configure the log class
     *
     * @param int $subscription
     * @param int $account
     * @param string $aps
     */
    public function configure($subscription = 0, $account = 0, $aps = "APS-REF-NAME")
    {
        if ($subscription != 0) {
            $this->subscription = $subscription;
        }
        if ($account != 0) {
            $this->account = $account;
        }
        if ($aps != "APS-REF-NAME") {
            $this->aps = $aps;
        }
    }

    /**
     * Create a entry object of the log.
     *
     * @param string $message
     * @param string $label
     * @param int $level
     * @param mixed $context
     */
    private function _createEntry($message, $label, $level, $context)
    {
        $timezone = new DateTimeZone($this->configuration->timezone);
        $time = new DateTime('now', $timezone);

        $entry = new SCITLogEntry();
        $entry->time = $time;
        $entry->message = $message;
        $entry->label = $label;
        $entry->level = $level;
        if (count($context) > 0) {
            $entry->context = print_r($context, true);
        }

        $entry->account = $this->account;
        $entry->subscription = $this->subscription;
        $entry->aps = $this->aps;

        $this->handler->dispatch($entry);
    }

    /**
     * Added a log record at the EMERGENCY level.
     *
     * @param string $message
     * @param mixed $context
     * @return null
     */
    public function emergency($message, Array $context = array())
    {
        if ($this->configuration->level >= $this::EMERGENCY) {
            $this->_createEntry($message, 'EMERGENCY', $this::EMERGENCY, $context);
        }
    }

    /**
     * Added a log record at the ALERT level.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, Array $context = array())
    {
        if ($this->configuration->level >= $this::ALERT) {
            $this->_createEntry($message, 'ALERT', $this::ALERT, $context);
        }
    }

    /**
     * Added a log record at the CRITICAL level.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, Array $context = array())
    {
        if ($this->configuration->level >= $this::CRITICAL) {
            $this->_createEntry($message, 'CRITICAL', $this::CRITICAL, $context);
        }
    }

    /**
     * Added a log record at the ERROR level.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, Array $context = array())
    {
        if ($this->configuration->level >= $this::ERROR) {
            $this->_createEntry($message, 'ERROR', $this::ERROR, $context);
        }
    }

    /**
     * Added a log record at the WARNING level.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, Array $context = array())
    {
        if ($this->configuration->level >= $this::WARNING) {
            $this->_createEntry($message, 'WARNING', $this::WARNING, $context);
        }
    }

    /**
     * Added a log record at the NOTICE level.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, Array $context = array())
    {
        if ($this->configuration->level >= $this::NOTICE) {
            $this->_createEntry($message, 'NOTICE', $this::NOTICE, $context);
        }
    }

    /**
     * Added a log record at the INFO level.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, Array $context = array())
    {
        if ($this->configuration->level >= $this::INFO) {
            $this->_createEntry($message, 'INFO', $this::INFO, $context);
        }
    }

    /**
     * Added a log record at the DEBUG level.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, Array $context = array())
    {
        if ($this->configuration->level >= $this::DEBUG) {
            $this->_createEntry($message, 'DEBUG', $this::DEBUG, $context);
        }
    }

    /**
     * Added a log record at an arbitrary level.
     * This method allows for compatibility with common interfaces.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @throws InvalidArgumentException
     * @return null
     */
    public function log($level, $message, Array $context = array())
    {
        if (is_string($level)) {
            if (defined(__CLASS__ . '::' . strtoupper($level))) {
                $this->_createEntry($message, strtoupper($level), constant(__CLASS__ . '::' . strtoupper($level)), $context);
                return null;
            }
            throw new InvalidArgumentException('Level "' . $level . '" is not defined');
        }
    }
}