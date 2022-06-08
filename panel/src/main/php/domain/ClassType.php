<?php
declare (strict_types=1);

namespace panel\domain;


/**
 * Responsible for representing classes. A class can be a Video or a 
 * Questionnaire.
 */
abstract class ClassType implements \JsonSerializable
{
    //-----------------------------------------------------------------------
    //        Attributes
    //-----------------------------------------------------------------------
    protected $idModule;
    protected $classOrder;
    
    
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
        return $this->idModule;
    }
    
    /**
     * Gets class order inside the module to which the class belongs.
     *
     * @return      int Module id
     */
    public function getClassOrder() : int
    {
        return $this->classOrder;
    }
    
    
    //-------------------------------------------------------------------------
    //        Serialization
    //-------------------------------------------------------------------------
    /**
     * {@inheritDoc}
     * @see \JsonSerializable::jsonSerialize()
     *
     * @Override
     */
    public function jsonSerialize()
    {
        return array(
            'module' => $this->idModule,
            'class_order' => $this->classOrder
        );
    }
}