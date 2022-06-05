<?php
declare (strict_types=1);

namespace models\enum;


use models\util\Enumeration;


/**
 * Contains class types.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class ClassTypeEnum extends Enumeration 
{
    //-------------------------------------------------------------------------
    //        Enumerations
    //-------------------------------------------------------------------------
    public const VIDEO = '0';
    public const QUESTIONNAIRE = '1';
}