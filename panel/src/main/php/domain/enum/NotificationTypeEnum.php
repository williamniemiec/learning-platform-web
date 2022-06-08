<?php
declare (strict_types=1);

namespace panel\domain\enum;


use panel\util\Enumeration;


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