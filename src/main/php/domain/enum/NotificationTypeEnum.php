<?php
declare (strict_types=1);

namespace domain\enum;


use domain\util\Enumeration;


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
    public const COMMENT = 0;
    public const SUPPORT_TOPIC = 1;
}