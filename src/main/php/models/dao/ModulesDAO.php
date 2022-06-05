<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Module;
use models\_Class;


/**
 * Responsible for managing 'modules' table.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class ModulesDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $db;
    
    
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
        $this->db = $db->getConnection();
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets all modules from a course.
     *
     * @param       int $id_course Course id
     *
     * @return      Module[] Modules from this course
     * 
     * @throws      \InvalidArgumentException If course id is empty or less than
     * or equal to zero 
     */
    public function getFromCourse(int $id_course) : array
    {
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Course id cannot be empty ".
                "or less than or equal to zero");
        
        $response = array();
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    modules
            WHERE   id_module IN (SELECT    id_module
                                  FROM      course_modules
                                  WHERE     id_course = ?)
        ");
        
        // Executes query
        $sql->execute(array($id_course));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $modules = $sql->fetchAll();
            
            foreach ($modules as $module) {
                $response[] = new Module(
                    (int)$module['id_module'], 
                    $module['name']
                );
            }
        }
        
        return $response;
    }
    
    /**
     * Gets informations about all classes from a module.
     *
     * @param       int $id_module Module id
     *
     * @return      array Informations about all classes from the module. The
     * returned array has the following format:
     * <ul>
     *  <li><b>Key</b>: Class order inside this module</li>
     *  <li><b>Value</b>: {@link _Class}</li>
     * </ul>
     * 
     * @throws      \InvalidArgumentException If module id is empty or less 
     * than or equal to zero
     */
    public function getClassesFromModule(int $id_module) : array
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
        
        $response = array();
        $videos = new VideosDAO($this->db);
        $questionnaires = new QuestionnairesDAO($this->db);
        
        // Gets video classes inside the module 
        $class_video = $videos->getFromModule($id_module);
        
        // Gets questionnaire classes inside the module
        $class_questionnaire = $questionnaires->getFromModule($id_module);
        
        // Creates response array
        foreach ($class_video as $class) {
            $response[$class->getClassOrder()] = $class;
        }
        
        foreach ($class_questionnaire as $class) {
            $response[$class->getClassOrder()] = $class;
        }
        
        return $response;
    }
}