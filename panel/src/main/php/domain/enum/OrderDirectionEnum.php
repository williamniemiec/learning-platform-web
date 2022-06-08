<?php
declare (strict_types=1);

namespace panel\domain\enum;


use panel\util\Enumeration;


/**
 * Contains order by directions.
 */
class OrderDirectionEnum extends Enumeration 
{
    //-------------------------------------------------------------------------
    //        Enumerations
    //-------------------------------------------------------------------------
    public const ASCENDING = 'asc';
    public const DESCENDING = 'desc';
}