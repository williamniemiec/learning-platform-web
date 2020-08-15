<?php
declare (strict_types=1);

namespace models;


use database\Database;
use models\dao\ModulesDAO;
use models\dao\CoursesDAO;


/**
 * Responsible for representing courses.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Course implements \JsonSerializable
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $id_course;
    private $name;
    private $logo;
    private $description;
    private $modules;
    private $total_classes;
    private $total_length;
    
    
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
    public function __construct(int $id_course, string $name, ?string $logo = '', ?string $description = '')
    {
        $this->id_course = $id_course;
        $this->name = $name;
        $this->logo = empty($logo) ? "" : $logo;
        $this->description = empty($description) ? "" : $description;
    }
    
  
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets course id.
     * 
     * @return      int Course id
     */
    public function getId() : int
    {
        return $this->id_course;
    }
    
    /**
     * Gets course name.
     * 
     * @return      string Course name
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    /**
     * Gets the name of the course logo file.
     * 
     * @return      string Name of the course logo file or empty string if
     * course does not have a logo
     */
    public function getLogo() : string
    {
        return $this->logo;
    }
    
    /**
     * Gets course description.
     *
     * @return      string Course description or empty string if
     * course does not have a description
     */
    public function getDescription() : string
    {
        return $this->description;
    }
    
    /**
     * Gets all modules from a course.
     * 
     * @param       Database $db Database
     * @param       bool $provideDatabase [Optional] If true, provide database
     * to all modules. Default is false
     * 
     * @return      Module[] Modules from this course
     * 
     * @implNote    Lazy initialization
     */
    public function getModules(Database $db, bool $provideDatabase = false) : array
    {
        if (empty($this->modules)) {
            $modules = new ModulesDAO($db);
            
            $this->modules = $modules->getFromCourse($this->id_course);
            
            if ($provideDatabase) {
                foreach ($this->modules as $module) {
                    $module->setDatabase($db);
                }
            }
        }
        
        return $this->modules;
    }
    
    /**
     * Gets the total classes of the course.
     * 
     * @param       Database $db [Optional] Database to get total classes
     * 
     * @return      int Total classes of the course
     *
     * @throws      \InvalidArgumentException If total classes has not yet been
     * set and a database is not provided to obtain this information
     *
     * @implNote    Lazy initialization
     */
    public function getTotalClasses(?Database $db = null) : int
    {
        if (empty($this->total_classes) && $this->total_classes != 0) {
            if (empty($db))
                throw new \InvalidArgumentException("Database cannot be empty");
            
            $courses = new CoursesDAO($db);
            $total = $courses->countClasses($this->id_course);
            
            $this->total_classes = (int)$total['total_classes'];
        }
        
        return $this->total_classes;
    }
    
    /**
     * Gets the total length of the course.
     * 
     * @param       Database $db Database
     * 
     * @return      int Total length of the course
     * 
     * @throws      \InvalidArgumentException If total length has not yet been
     * set and a database is not provided to obtain this information
     *
     * @implNote    Lazy initialization
     */
    public function getTotalLength(?Database $db = null) : int
    {
        if (empty($this->total_length) && $this->total_length != 0) {
            if (empty($db))
                throw new \InvalidArgumentException("Database cannot be empty");
            
            $courses = new CoursesDAO($db);
            $total = $courses->countClasses($this->id_course);
            
            $this->total_length = (int)$total['total_length'];
        }
        
        return $this->total_length;
    }
    
    /**
     * Sets total classes of the course.
     * 
     * @param       int $totalClasses Total classes of the course
     * 
     * @throws      \InvalidArgumentException If total classes is empty or less 
     * than zero
     */
    public function setTotalClasses(int $totalClasses) : void
    {
        if ((empty($totalClasses) && $totalClasses != 0) || $totalClasses < 0)
            throw new \InvalidArgumentException("Total classes cannot be less ".
                "than zero");
        
        $this->total_classes = $totalClasses;
    }
    
    /**
     * Sets total length of the course (in minutes).
     *
     * @param       int $totalClasses Total length of the course
     *
     * @throws      \InvalidArgumentException If total length is empty or less 
     * than zero
     */
    public function setTotalLength(int $totalLength) : void
    {
        if ((empty($totalLength) && $totalLength != 0) || $totalLength < 0)
            throw new \InvalidArgumentException("Total length cannot be less ".
                "than zero");
            
        $this->total_length = $totalLength;
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
            'id' => $this->id_course,
            'name' => $this->name,
            'logo' => $this->logo,
            'description' => $this->description,
            'modules' => $this->modules,
            'total_classes' => $this->total_classes,
            'total_length' => $this->total_length
        );
    }
}