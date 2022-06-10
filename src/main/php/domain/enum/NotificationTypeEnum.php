<?php
/**
 * Copyright (c) William Niemiec.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

declare (strict_types=1);

namespace domain\enum;


use util\Enumeration;


/**
 * Contains reference types of notifications.
 */
class NotificationTypeEnum extends Enumeration 
{
    //-------------------------------------------------------------------------
    //        Enumerations
    //-------------------------------------------------------------------------
    public const COMMENT = 0;
    public const SUPPORT_TOPIC = 1;
}