<?php
declare (strict_types=1);

namespace domain\enum;


use domain\util\Enumeration;


/**
 * Contains order by directions.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class OrderDirectionEnum extends Enumeration 
{
    //-------------------------------------------------------------------------
    //        Enumerations
    //-------------------------------------------------------------------------
    public const ASCENDING = 'asc';
    public const DESCENDING = 'desc';
}