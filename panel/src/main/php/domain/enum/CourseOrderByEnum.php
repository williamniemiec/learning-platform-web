<?php
declare (strict_types=1);

namespace panel\domain\enum;


use panel\util\Enumeration;


/**
 * Contains course 'order by' options.
 */
class CourseOrderByEnum extends Enumeration 
{
    //-------------------------------------------------------------------------
    //        Enumerations
    //-------------------------------------------------------------------------
    public const NAME = 'name';
    public const TOTAL_STUDENTS = 'total_students';
}
