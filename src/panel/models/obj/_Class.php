<?php
declare (strict_types=1);

namespace models\obj;


/**
 * Responsible for representing classes. A class can be a Video or a 
 * Questionnaire.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
abstract class _Class
{
    //-----------------------------------------------------------------------
    //        Attributes
    //-----------------------------------------------------------------------
    protected $id_module;
    protected $class_order;
    
    
    //-----------------------------------------------------------------------
    //        Getters
    //-----------------------------------------------------------------------
    /**
     * Gets module id to which the class belongs.
     * 
     * @return      int Module id
     */
    public function getModuleId() : int
    {
        return $this->id_module;
    }
    
    /**
     * Gets class order inside the module to which the class belongs.
     *
     * @return      int Module id
     */
    public function getClassOrder() : int
    {
        return $this->class_order;
    }
}