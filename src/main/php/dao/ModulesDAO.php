<?php
/**
 * Copyright (c) William Niemiec.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Module;


/**
 * Responsible for managing 'modules' table.
 */
class ModulesDAO extends DAO
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'modules' table manager.
     *
     * @param       Database $db Database
     */
    public function __construct(Database $db)
    {
        parent::__construct($db);
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets all modules from a course.
     *
     * @param       int idCourse Course id
     *
     * @return      Module[] Modules from this course
     * 
     * @throws      \InvalidArgumentException If course id is empty or less than
     * or equal to zero 
     */
    public function getFromCourse(int $idCourse) : array
    {
        $this->validateCourseId($idCourse);
        $this->withQuery("
            SELECT  *
            FROM    modules
            WHERE   id_module IN (SELECT    id_module
                                  FROM      course_modules
                                  WHERE     id_course = ?)
        ");
        $this->runQueryWithArguments($idCourse);

        return $this->parseNotebookResponseQuery();
    }

    private function validateCourseId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Course id cannot be empty or".
                                                "less than or equal to zero");
        }
    }

    private function parseNotebookResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $modules = array();
            
        foreach ($this->getAllResponseQuery() as $module) {
            $modules[] = new Module(
                (int) $module['id_module'], 
                $module['name']
            );
        }

        return $modules;
    }
    
    /**
     * Gets information about all classes from a module.
     *
     * @param       int idModule Module id
     *
     * @return      array Information about all classes from the module. The
     * returned array has the following format:
     * <ul>
     *  <li><b>Key</b>: Class order inside this module</li>
     *  <li><b>Value</b>: {@link _Class}</li>
     * </ul>
     * 
     * @throws      \InvalidArgumentException If module id is empty or less 
     * than or equal to zero
     */
    public function getClassesFromModule(int $idModule) : array
    {
        $this->validateModuleId($idModule);
        
        $classes = array();

        foreach ($this->getAllVideoClassesFromModule($idModule) as $class) {
            $classes[$class->getClassOrder()] = $class;
        }
        
        foreach ($this->getAllQuestionnairesClassesFromModule($idModule) as $class) {
            $classes[$class->getClassOrder()] = $class;
        }
        
        return $classes;
    }

    private function validateModuleId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Module id cannot be empty or".
                                                "less than or equal to zero");
        }
    }

    private function getAllVideoClassesFromModule($id)
    {
        $videosDao = new VideosDAO($this->db);
        
        return $videosDao->getAllFromModule($id);
    }

    private function getAllQuestionnairesClassesFromModule($id)
    {
        $questionnairesDao = new QuestionnairesDAO($this->db);
        
        return $questionnairesDao->getAllFromModule($id);
    }
}