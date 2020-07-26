<?php
declare (strict_types=1);

namespace models\enum;


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
    public static const VIDEO = '0';
    public static const QUESTIONNAIRE = '1';
}