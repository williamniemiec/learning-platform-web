<?php
namespace models\obj;


/**
 * Responsible for representing an authorization.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Authorization extends User
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
     * @param       int $id_authorization Administrator id
     * @param       string $name Authorization name
     * @param       int $level Authorization level
     */
    public function __construct($name, $level)
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
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Gets authorization level.
     *
     * @return      int Authorization level
     */
    public function getLevel()
    {
        return $this->level;
    }
}