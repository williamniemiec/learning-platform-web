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
     * Deletes a module from a course.
     * 
     * @param       int $id_module Module id to be deleted
     */
    public function delete($id_module)
    {
//         if (empty($id_module) || $id_module <= 0) { return; }
        
//         // Get all classes from this module
//         $classIds = $this->getAllClasses($id_module);
        
//         // Delete classes from course
//         $this->db->query("
//             DELETE FROM classes 
//             WHERE id IN (".implode(",", $classIds).")
//         ");
        
//         // Delete module from course
//         $sql = $this->db->prepare("
//             DELETE FROM modules 
//             WHERE id = ?
//         ");
//         $sql->execute(array($id_module));
        
//         // Delete historic from course
//         if (count($classIds) > 0) {
//             $this->db->query("
//                 DELETE FROM historic 
//                 WHERE id_class IN (".implode(",",$classIds).")
//             ");
//         }
        
//         // Delete videos from course
//         $this->db->query("
//             DELETE FROM videos 
//             WHERE id_class IN (".implode(",",$classIds).")
//         ");
        
//         // Delete questionnaires from course
//         $this->db->query("
//             DELETE FROM questionnaries 
//             WHERE id_class IN (".implode(",",$classIds).")
//         ");

        $sql = $this->db->prepare("
            DELETE FROM modules
            WHERE id_module = ?
        ");
        
        $sql->execute(array($id_module));
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Adds a module to a course.
     * 
     * @param       int $id_course Course id
     * @param       string $name Module name
     * 
     * @return      int Module id added or -1 if the module has not been added
     */
    public function add($name)
    {
        $sql = $this->db->prepare("
            INSERT INTO modules 
            SET name = ? 
        ");
        
        $response = -1;
        
        $sql->execute(array($name));
        
        if ($sql->rowCount() > 0) {
            $response = $this->db->lastInsertId();
        }
        
        return $response;
    }
    
    /**
     * Edits a module from a course.
     * 
     * @param       int $id_module Module id
     * @param       string $name New module name
     * 
     * @return      boolean If the module was successfully edited
     */
    public function edit($id_module, $newName)
    {
        if (empty($id_module) || $id_module <= 0) { return false; }
        if (empty($newName)) { return false; }
        
        $sql = $this->db->prepare("
            UPDATE  modules 
            SET     name = ? 
            WHERE   id_module = ?
        ");
        $sql->execute(array($newName, $id_module));
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Gets informations about all classes from a module.
     *
     * @param       int $id_module Module id
     *
     * @return      array Informations about all classes from the module
     */
    public function getClassesFromModule($id_module)
    {
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
    
    /**
     * Checks if there is a module in a course.
     * 
     * @param       int $id_course Course id
     * @param       string $name Module name
     * 
     * @return      boolean If the module exists in the specified course
     */
//     private function alreadyExist($id_course, $name)
//     {
//         if (empty($name) || empty($id_course) || $id_course <= 0)
//             return false;
        
//         $sql = $this->db->prepare("
//             SELECT COUNT(*) AS count 
//             FROM modules 
//             WHERE id_course = ? AND name = ?
//         ");
//         $sql->execute(array($id_course, $name));
        
//         return $sql->fetch()['count'] > 0;
//     }
    
    /**
     * Gets all classes from a module.
     * 
     * @param       int $id_module Module id
     * 
     * @return      array Classes from this module
     */
//     private function getAllClasses($id_module)
//     {
//         if (empty($id_module) || $id_module <= 0) { return array(); }
        
//         $response = array();
        
//         $sql = $this->db->prepare("
//             SELECT id 
//             FROM classes 
//             WHERE id_module = ?
//         ");
//         $sql->execute(array($id_module));
        
//         if ($sql->rowCount() > 0) {
//             foreach ($sql->fetchAll() as $class) {
//                 $response[] = $class['id'];
//             }
//         }
        
//         return $response;
//     }
}