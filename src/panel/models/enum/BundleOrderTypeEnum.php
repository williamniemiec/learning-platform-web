<?php
declare (strict_types=1);

namespace models\enum;


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
    public static const PRICE = 'price';
    public static const COURSES = 'courses';
    public static const SALES = 'sales';
}