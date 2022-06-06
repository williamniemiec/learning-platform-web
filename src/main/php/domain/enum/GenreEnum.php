<?php
declare (strict_types=1);

namespace domain\enum;


use domain\util\Enumeration;


/**
 * Contains genre types.
 */
class GenreEnum extends Enumeration
{
    //-------------------------------------------------------------------------
    //        Enumerations
    //-------------------------------------------------------------------------
    public const MALE = '0';
    public const FEMALE = '1';
}