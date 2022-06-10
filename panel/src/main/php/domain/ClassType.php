<?php
/**
 * Copyright (c) William Niemiec.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

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
    protected $module;
    protected $classOrder;
    
    
    //-----------------------------------------------------------------------
    //        Getters
    //-----------------------------------------------------------------------
    /**
     * Gets module to which the class belongs.
     * 
     * @return      Module Module to which the class belongs
     */
    public function getModuleId() : Module
    {
        return $this->module;
    }
    
    /**
     * Gets class order inside the module to which the class belongs.
     *
     * @return      int Order
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
    public function jsonSerialize(): array
    {
        return array(
            'module' => $this->module,
            'class_order' => $this->classOrder
        );
    }
}