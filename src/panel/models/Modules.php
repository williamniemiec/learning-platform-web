<?php
namespace models;

use core\Model;


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
            SELECT * 
            FROM modules 
            WHERE id_course = ?
        ");
        $sql->execute(array($id_course));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetchAll(\PDO::FETCH_ASSOC);
            
            $classes = new Classes();
            
            for ($i=0; $i<count($response); $i++) {
                $response[$i]['classes'] = $classes->getClassesFromModule($response[$i]['id']);
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
        if (empty($id_module) || $id_module <= 0) { return; }
        
        // Get all classes from this module
        $classIds = $this->getAllClasses($id_module);
        
        // Delete classes from course
        $this->db->query("
            DELETE FROM classes 
            WHERE id IN (".implode(",", $classIds).")
        ");
        
        // Delete module from course
        $sql = $this->db->prepare("
            DELETE FROM modules 
            WHERE id = ?
        ");
        $sql->execute(array($id_module));
        
        // Delete historic from course
        if (count($classIds) > 0) {
            $this->db->query("
                DELETE FROM historic 
                WHERE id_class IN (".implode(",",$classIds).")
            ");
        }
        
        // Delete videos from course
        $this->db->query("
            DELETE FROM videos 
            WHERE id_class IN (".implode(",",$classIds).")
        ");
        
        // Delete questionnaires from course
        $this->db->query("
            DELETE FROM questionnaries 
            WHERE id_class IN (".implode(",",$classIds).")
        ");
    }
    
    /**
     * Adds a module to a course.
     * 
     * @param       int $id_course Course id
     * @param       string $name Module name
     * 
     * @return      int Module id added or -1 if the module has not been added
     */
    public function add($id_course, $name)
    {
        if (empty($name)) { return -1; }
        if ($this->alreadyExist($id_course, $name)) { return -1; }
        
        $response = -1;
        
        $sql = $this->db->prepare("
            INSERT INTO modules 
            (id_course, name) 
            VALUES (?,?)
        ");
        $sql->execute(array($id_course, $name));
        
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
    public function edit($id_module, $name)
    {
        if (empty($id_module) || $id_module <= 0) { return false; }
        if (empty($name)) { return false; }
        
        $sql = $this->db->prepare("
            UPDATE modules 
            SET name = ? 
            WHERE id = ?
        ");
        $sql->execute(array($name, $id_module));
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Checks if there is a module in a course.
     * 
     * @param       int $id_course Course id
     * @param       string $name Module name
     * 
     * @return      boolean If the module exists in the specified course
     */
    private function alreadyExist($id_course, $name)
    {
        if (empty($name) || empty($id_course) || $id_course <= 0)
            return false;
        
        $sql = $this->db->prepare("
            SELECT COUNT(*) AS count 
            FROM modules 
            WHERE id_course = ? AND name = ?
        ");
        $sql->execute(array($id_course, $name));
        
        return $sql->fetch()['count'] > 0;
    }
    
    /**
     * Gets all classes from a module.
     * 
     * @param       int $id_module Module id
     * 
     * @return      array Classes from this module
     */
    private function getAllClasses($id_module)
    {
        if (empty($id_module) || $id_module <= 0) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT id 
            FROM classes 
            WHERE id_module = ?
        ");
        $sql->execute(array($id_module));
        
        if ($sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $class) {
                $response[] = $class['id'];
            }
        }
        
        return $response;
    }
}