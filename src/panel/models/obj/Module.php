<?php
namespace models\obj;


use models\Modules;
use models\Classes;

/**
 * Responsible for representing courses.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Module
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $id_course;
    private $name;
    private $classes;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a course.
     *
     * @param       int $id_course Course id
     * @param       string $name Course name
     * @param       string $logo [Optional] Name of the course logo file
     * @param       string $description [Optional] Course description
     */
    public function __construct($id_module, $name)
    {
        $this->id_course = $id_course;
        $this->name = $name;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets course id.
     *
     * @return      int Course id
     */
    public function getCourseId()
    {
        return $this->id_course;
    }
    
    /**
     * Gets course name.
     *
     * @return      string Course name
     */
    public function getName()
    {
        return $this->name;
    }
    
    public function getClasses()
    {
        if (empty($this->classes)) {
            $this->classes = Classes::getClassesFromModule($this->id_module);
        }
        
        return $this->classes;
    }
}