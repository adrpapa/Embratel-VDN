<?php

namespace SCITBrandingClient;

use Exception;
use Aps\Proto;
use Aps\Request;
use Aps\ControllerProxy;

/**
 * Class ClientManager provide access to branding data
 * @author  SofCloudIT <info@sofcloudit.com>
 * @copyright Copyright � 2015 Ingram Micro Inc.  Any rights not granted herein
 * are reserved for Ingram Micro Inc. Permission to use, copy and distribute this
 * source code without fee and without a signed license agreement is hereby granted
 * provided that: (i) the above copyright notice and this paragraph appear in all
 * copies and distributions; and (ii) the source code is only used, copied or
 * distributed for the purpose of using it with the APS package for which Ingram Micro Inc.
 * or its affiliates integrated it into.  Ingram Micro Inc. may revoke the limited license
 * granted herein at any time at its sole discretion.  THIS SOURCE CODE IS PROVIDED
 * �AS IS�.  INGRAM MICRO INC. MAKES NO REPRESENTATIONS OR WARRANTIES AND DISCLAIMS
 * ALL IMPLIED WARRANTIES OF MERCHANTABILITY OR FITNESS FOR ANY PARTICULAR PURPOSE.
 * Copyright � 2015 Ingram Micro Inc.  Any rights not granted herein are reserved for
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
 * @package SCITLogger
 */
class SCITBrandingClient {

    /**
     * The APSController instance
     * @var object
     */
    private $APSController;

    /**
     * The resource type of the target
     * @var string
     */
    private $targetGlobalsResourceType;

    /**
     * The resource type of the branding discover aps
     * @var string
     */
    private $brandingDiscoverGlobalsResourceType = 'http://www.sofcloudit.com/branding-discover/globals/2.0';

    /**
     * Construct the class.
     *
     * @param string $targetGlobalsResourceType
     * @param null   $APSController
     */
    public function __construct($targetGlobalsResourceType, $APSController = null) {
        $this->targetGlobalsResourceType = $targetGlobalsResourceType;

        if ($APSController == null) {
            foreach (ControllerProxy::listInstances() as $instanceId) {
                $APSController = clone Request::getController($instanceId);
                break;
            }
        }

        $ResourceList = $APSController->getResources('implementing(' . $this->targetGlobalsResourceType . ')');
        $this->APSController = $APSController->impersonate($ResourceList[0]->aps->id);
    }

    /**
     * Get all the vendor data
     *
     * @param string $accountID
     *
     * @return mixed|null
     * @throws Exception
     */
    public function GetVendorData($accountID) {

        $brandingDiscoverGlobalList = $this->APSController->getResources('implementing(' . $this->brandingDiscoverGlobalsResourceType . '),limit(0,1)');
        if (count($brandingDiscoverGlobalList) > 0) {
            // Original
             $vendor = json_decode($this->APSController->getIo()->sendRequest("GET", Proto::resourcePath($brandingDiscoverGlobalList[0]->aps->id, "/getVendorDataByGUID?guid=" . $accountID)));
         //   $vendor = json_decode($this->APSController->getIo()->sendRequest("GET", 'aps/2/resources/' . $brandingDiscoverGlobalList[0]->aps->id, "/getVendorDataByGUID?guid=" . $accountID));
            if (json_last_error() != 0) {
                throw new Exception("Error: JSON validation failed");
            }
            return $vendor;
        }

        return false;
    }

    /**
     * Get the vendor ID
     *
     * @param string $accountID
     *
     * @return mixed|null
     */
    public function GetVendorId($accountID) {
        // Original
        $brandingDiscoverGlobalList = $this->APSController->getResources('implementing(' . $this->brandingDiscoverGlobalsResourceType . '),limit(0,1)');
        if (count($brandingDiscoverGlobalList) > 0) {
            // Original
            return $this->APSController->getIo()->sendRequest("GET", Proto::resourcePath($brandingDiscoverGlobalList[0]->aps->id, "/getVendorIdByGUID?guid=" . $accountID));
        }
        return false;
    }

}

?>