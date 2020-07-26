<?php
declare (strict_types=1);

namespace models\enum;

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
    public static const ASCENDING = 'asc';
    public static const DESCENDING = 'desc';
}