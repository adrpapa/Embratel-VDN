<?php

namespace SCITDataHandler\Interfaces;

use SCITDataHandler\Model\SCITDataModel as Model;

/**
 * SCITDataModelInterface
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
interface SCITHandlerInterface
{
    public function run(Model $entry);
}