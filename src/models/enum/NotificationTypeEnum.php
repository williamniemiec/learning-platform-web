<?php
declare (strict_types=1);

namespace models\enum;

/**
 * Contains reference types of notifications.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class NotificationTypeEnum extends Enumeration 
{
    //-------------------------------------------------------------------------
    //        Enumerations
    //-------------------------------------------------------------------------
    public static const COMMENT = 0;
    public static const SUPPORT_TOPIC = 1;
}