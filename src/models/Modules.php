<?php
declare (strict_types=1);

namespace models;


use core\Model;
use models\obj\Module;
use models\obj\_Class;


/**
 * Responsible for managing 'modules' table.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Modules extends Model
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'modules' table manager.
     *
     * @apiNote     It will connect to the database when it is instantiated
     */
    public function __construct()
    {
        parent::__construct();
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
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function getModules(int $id_course) : array
    {
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Invalid course id");
        
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
            $modules = $sql->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($modules as $module) {
                $response[] = new Module(
                    $module['id_module'], 
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
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function getClassesFromModule(int $id_module) : array
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Invalid module id");
        
        $response = array();
        $videos = new Videos();
        $questionnaires = new Questionnaires();
        
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