<?php
namespace models\obj;


/**
 * Responsible for representing courses.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Course
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $id_course;
    private $name;
    private $logo;
    private $description;
    
    
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
    public function __construct($id_course, $name, $logo = '', $description = '')
    {
        $this->id_course = $id_course;
        $this->name = $name;
        $this->logo = $logo;
        $this->description = $description;
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
    
    /**
     * Gets the name of the course logo file.
     * 
     * @return      string Name of the course logo file or empty string if
     * course does not have a logo
     */
    public function getLogo()
    {
        return $this->logo;
    }
    
    /**
     * Gets course description.
     *
     * @return      string Course description or empty string if
     * course does not have a description
     */
    public function getDescription()
    {
        return $this->description;
    }
}