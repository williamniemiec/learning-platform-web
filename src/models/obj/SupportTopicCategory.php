<?php
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
    private $id_topic;
    private $student;
    private $category;
    private $date;
    private $message;
    private $closed;
    private $replies;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a support topic category.
     *

     */
    public function __construct($id_category, $name)
    {
        $this->id_category = $id_category;
        $this->name = $name;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    public function getId()
    {
        return $this->id_category;
    }
    
    public function getName()
    {
        return $this->name;
    }
}