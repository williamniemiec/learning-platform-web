<?php
/**
 * Copyright (c) William Niemiec.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

declare (strict_types=1);

namespace domain;


use repositories\Database;
use dao\ModulesDAO;
use dao\CoursesDAO;


/**
 * Responsible for representing courses.
 */
class Course implements \JsonSerializable
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $idCourse;
    private $name;
    private $logo;
    private $description;
    private $modules;
    private $totalClasses;
    private $totalLength;
    
    
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
    public function __construct(
        int $id_course, 
        string $name, 
        ?string $logo = '', 
        ?string $description = ''
    )
    {
        $this->idCourse = $id_course;
        $this->name = $name;
        $this->logo = empty($logo) ? "" : $logo;
        $this->description = empty($description) ? "" : $description;
    }


    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    private function fetchModules($db, $provideDatabase)
    {
        $modulesDao = new ModulesDAO($db);   
        $fetchedModules = $modulesDao->getFromCourse($this->idCourse);
        
        if ($provideDatabase) {
            foreach ($fetchedModules as $module) {
                $module->setDatabase($db);
            }
        }

        return $fetchedModules;
    }

    private function validateDatabase($db) {
        if (empty($db)) {
            throw new \InvalidArgumentException("Database cannot be empty");
        }
    }

    private function fetchTotalClasses($db) 
    {
        $coursesDao = new CoursesDAO($db);
        $total = $coursesDao->countClasses($this->idCourse);
        
        return ((int) $total['total_classes']);
    }

    private function fetchTotalLength($db) 
    {
        $coursesDao = new CoursesDAO($db);
        $total = $coursesDao->countClasses($this->idCourse);
        
        return ((int) $total['total_length']);
    }

    private function validateTotalClasses($totalClasses) {
        if (!$this->isNonNegativeInteger($totalClasses)) {
            throw new \InvalidArgumentException("Total classes cannot be less ".
                                                "than zero");
        }
    }

    private function isNonNegativeInteger($value)
    {
        return  $value >= 0 
                && (!empty($value) || $value == 0);
    }

    private function validateTotalLength($totalLength) {
        if (!$this->isNonNegativeInteger($totalLength)) {
            throw new \InvalidArgumentException("Total length cannot be less ".
                                                "than zero");
        }
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
        return $this->idCourse;
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
     * @param       bool provide_database [Optional] If true, provide database
     * to all modules. Default is false
     * 
     * @return      Module[] Modules from this course
     * 
     * @implNote    Lazy initialization
     */
    public function getModules(Database $db, bool $provideDatabase = false) : array
    {
        if (empty($this->modules)) {
            $this->modules = $this->fetchModules($db, $provideDatabase);
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
        if (empty($this->totalClasses)) {
            $this->validateDatabase($db);
            $this->totalClasses = $this->fetchTotalClasses($db);
        }
        
        return $this->totalClasses;
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
        if (empty($this->totalLength) && $this->totalLength != 0) {
            $this->validateDatabase($db);
            $this->totalClasses = $this->fetchTotalLength($db);
        }
        
        return $this->totalLength;
    }
    
    /**
     * Sets total classes of the course.
     * 
     * @param       int totalClasses Total classes of the course
     * 
     * @throws      \InvalidArgumentException If total classes is empty or less 
     * than zero
     */
    public function setTotalClasses(int $totalClasses) : void
    {
        $this->validateTotalClasses($totalClasses);
        $this->totalClasses = $totalClasses;
    }
    
    /**
     * Sets total length of the course (in minutes).
     *
     * @param       int $total_classes Total length of the course
     *
     * @throws      \InvalidArgumentException If total length is empty or less 
     * than zero
     */
    public function setTotalLength(int $totalLength) : void
    {
        $this->validateTotalLength($totalLength);
        $this->totalLength = $totalLength;
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
            'id' => $this->idCourse,
            'name' => $this->name,
            'logo' => $this->logo,
            'description' => $this->description,
            'modules' => $this->modules,
            'total_classes' => $this->totalClasses,
            'total_length' => $this->totalLength
        );
    }
}