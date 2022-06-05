<?php
declare (strict_types=1);

namespace domain\enum;


use domain\util\Enumeration;


/**
 * Contains bundle 'order by' options.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class BundleOrderTypeEnum extends Enumeration
{
    //-------------------------------------------------------------------------
    //        Enumerations
    //-------------------------------------------------------------------------
    public const PRICE = 'price';
    public const COURSES = 'courses';
    public const SALES = 'sales';
}