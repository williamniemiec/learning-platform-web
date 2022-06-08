<?php
declare (strict_types=1);

namespace panel\util;


/**
 * Responsible for representing enumerations. The operation consists of setting
 * an enumeration (selecting it) and obtaining it when desired.
 */
class Enumeration
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $selected;
    
    /**
     * Creates an enumeration and sets an enumeration as selected.
     * 
     * @param       mixed $enum [Optional] Enumeration to be selected
     */
    public final function __construct($enum = null)
    {
        if (!empty($enum) || (empty($enum) && $enum == '0')) {
            $this->selected = $enum;
        }
    }
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Selects an enumeration.
     * 
     * @param       mixed $enum Enumeration to be selected
     */
    public final function set($enum)
    {
        $this->selected = $enum;
    }
    
    /**
     * Gets selected enumeration.
     * 
     * @return      mixed Selected enumeration
     */
    public final function get()
    {
        return $this->selected;
    }
}