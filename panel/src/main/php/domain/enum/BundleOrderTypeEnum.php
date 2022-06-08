<?php
declare (strict_types=1);

namespace panel\domain\enum;


use panel\util\Enumeration;


/**
 * Contains bundle 'order by' options.
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