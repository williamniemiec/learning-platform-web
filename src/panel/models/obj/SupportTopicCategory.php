<?php
declare (strict_types=1);

namespace models\obj;


use models\Students;
use models\Comments;

/**
 * Responsible for representing a support topic category.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class SupportTopicCategory
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $id_category;
    private $name;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a support topic category.
     *
     * @param       int $id_category Category id
     * @param       string $name Category name
     */
    public function __construct(int $id_category, string $name)
    {
        $this->id_category = $id_category;
        $this->name = $name;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets category id.
     * 
     * @return      int Category id
     */
    public function getId() : int
    {
        return $this->id_category;
    }
    
    /**
     * Gets category name.
     * 
     * @return      string Category name
     */
    public function getName() : string
    { 
        return $this->name;
    }
}