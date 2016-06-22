<?php namespace SCITDataHandler\Handlers;

use PDO;
use PDOException;
use SCITDataHandler\Model\SCITDataModel;

/**
 * Dump logs in mysql database
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
 * discretion.  THIS SOURCE CODE IS PROVIDED �AS IS�.  INGRAM MICRO INC. MAKES NO REPRESENTATIONS
 * OR WARRANTIES AND DISCLAIMS ALL IMPLIED WARRANTIES OF MERCHANTABILITY OR FITNESS FOR ANY
 * PARTICULAR PURPOSE.
 * @version 0.1 Beta
 * @package SCITDataHandler
 */
class SCITDatabaseHandler extends SCITAbstractHandler
{
    protected $dbdriver;

    protected $dbhost;

    protected $dbname;

    protected $dbuser;

    protected $dbpass;

    protected $dbtable;

    private static $instance;

    /**
     * @param null $params
     */
    public function __construct($params = null)
    {
        parent::__construct($params);
        if (!self::$instance instanceof PDO) {
            try {
                self::$instance = new PDO($this->dbdriver . ':host=' . $this->dbhost . ';dbname=' . $this->dbname, $this->dbuser, $this->dbpass);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                syslog(LOG_ERR, "Error on SCITDataHandler -> DatabaseHandler: " . $e->getMessage());
            }
        }
    }

    /**
     * @param SCITDataModel $entry
     */
    public function run(SCITDataModel $entry)
    {
        $sql = $this->buildQuery($entry);
        $params = $this->buildParams($entry);

        try {

            $stmt = self::$instance->prepare($sql);
            $stmt->execute($params);

            if ($stmt->errorCode() != 00000) {
                throw new PDOException($stmt->errorInfo(), $stmt->errorCode());
            }
        } catch (PDOException $e) {
            syslog(LOG_ERR, "Error on SCITDataHandler -> DatabaseHandler: " . $e->getMessage());
        }
    }

    /**
     * Build the array of params
     * @param SCITDataModel $entry
     * @return array
     */
    private function buildParams(SCITDataModel $entry)
    {
        $entryProperties = $entry->toArray();
        foreach ($entryProperties as $key => $value) {
            $params[":" . $key] = $value;
        }
        $params[":time"] = $entry->time->format('Y-m-d H:i:s');
        return $params;
    }

    /**
     * Build the sql string
     * @param SCITDataModel $entry
     * @return string
     */
    private function buildQuery(SCITDataModel $entry)
    {
        $fields = array_keys($this->buildParams($entry));

        $placeholders = array(
            "{dbtable}",
            "{fields}"
        );
        $replaces = array(
            $this->dbtable,
            implode(",", $fields)
        );
        $entryProperties = array_keys($entry->toArray());
        return str_replace($placeholders, $replaces, "INSERT INTO {dbtable} (" . implode(',',$entryProperties) . ") VALUES ({fields})");
    }
}