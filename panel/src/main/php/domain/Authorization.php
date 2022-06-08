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
    private $idAuthorization;
    private $name;
    private $level;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a admin-type user.
     *
     * @param       int $idAuthorization Authorization id
     * @param       string $name Authorization's name
     * @param       int $level Authorization's level
     */
    public function __construct(int $idAuthorization, string $name, int $level)
    {
        $this->idAuthorization = $idAuthorization;
        $this->name = $name;
        $this->level = $level;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets authorization id.
     *
     * @return      int Authorization id
     */
    public function getId() : int
    {
        return $this->idAuthorization;
    }
    
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