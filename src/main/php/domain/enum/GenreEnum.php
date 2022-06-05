<?php
declare (strict_types=1);

namespace domain\enum;


use domain\util\Enumeration;


/**
 * Contains genre types.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class GenreEnum extends Enumeration
{
    //-------------------------------------------------------------------------
    //        Enumerations
    //-------------------------------------------------------------------------
    public const MALE = '0';
    public const FEMALE = '1';
}