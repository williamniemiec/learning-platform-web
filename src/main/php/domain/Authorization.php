<?php
declare (strict_types=1);

namespace domain;


/**
 * Responsible for representing an authorization.
 */
class Authorization
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $name;
    private $level;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a admin-type user.
     *
     * @param       string $name Authorization's name
     * @param       int $level Authorization's level
     */
    public function __construct(string $name, int $level)
    {
        $this->name = $name;
        $this->level = $level;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets authorization name.
     *
     * @return      string Authorization name
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    /**
     * Gets authorization level.
     *
     * @return      int Authorization level
     */
    public function getLevel() : int
    {
        return $this->level;
    }
}