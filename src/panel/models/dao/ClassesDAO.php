<?php
declare (strict_types=1);

namespace models\dao;


use models\enum\ClassTypeEnum;
use models\_Class;


/**
 * Responsible for representing classes.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
abstract class ClassesDAO
{
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets all classes from a module.
     *
     * @param       int $id_module Module id
     *
     * @return      array Classes that belongs to the module
     *
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public abstract function getAllFromModule(int $id_module) : array;
    
    /**
     * Gets total duration (in minutes) of all classes.
     *
     * @return      int Total duration (in minutes)
     */
    public abstract function totalLength() : int;
}