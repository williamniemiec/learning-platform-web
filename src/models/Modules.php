<?php
namespace models;

use core\Model;
use models\obj\Module;


/**
 * Responsible for managing modules from a course.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class Modules extends Model
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates modules manager.
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
     * Gets modules from a course.
     *
     * @param       int $id_course Course id
     *
     * @return      array Modules from this course
     */
    public function getModules($id_course)
    {
        if (empty($id_course) || $id_course <= 0) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT  *
            FROM    modules
            WHERE   id_module IN (SELECT    id_module
                                  FROM      course_modules
                                  WHERE     id_course = ?)
        ");
        
        $sql->execute(array($id_course));
        
        if ($sql->rowCount() > 0) {
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
     * Gets  IN formations about all classes FROM a module.
     *
     * @param        int $id_module Module id
     *
     * @return      array Informations about all classes FROM the module
     */
    public function getClassesFromModule($id_module)
    {
        if (empty($id_module) || $id_module <= 0) { return array(); }
        
        $response = array();
        $videos = new Videos();
        $questionnaires = new Questionnaires();
        
        $class_video = $videos->getFromModule($id_module);
        $class_questionnaire = $questionnaires->getFromModule($id_module);
        
        foreach ($class_video as $class) {
            $response[$class->getClassOrder()] = $class;
        }
        
        foreach ($class_questionnaire as $class) {
            $response[$class->getClassOrder()] = $class;
        }
        
        return $response;
    }
}