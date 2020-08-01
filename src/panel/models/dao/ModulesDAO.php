<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Module;
use models\util\IllegalAccessException;


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
    private $id_admin;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'modules' table manager.
     *
     * @param       Database $db Database
     * @param       int $id_admin [Optional] Admin id logged in
     */
    public function __construct(Database $db, int $id_admin = -1)
    {
        $this->db = $db->getConnection();
        $this->id_admin = $id_admin;
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
     * @throws      \InvalidArgumentException If course id is empty, less than
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
                    $module['id_module'], 
                    $module['name']
                );
            }
        }
        
        return $response;
    }
    
    /**
     * Removes a module from a course.
     * 
     * @param       int $id_admin Admin id logged in
     * @param       int $id_module Module id to be deleted
     * 
     * @return      bool If module has been successfully deleted
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to delete modules
     * @throws      \InvalidArgumentException If course id or admin id provided
     * in the constructor is empty, less than or equal to zero
     */
    public function delete(int $id_admin, int $id_module) : bool
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->getAuthorization()->getLevel() != 0 &&
            $this->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_admin) || $id_admin <= 0)
            throw new \InvalidArgumentException("Admin id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
        
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM modules
            WHERE id_module = ?
        ");
        
        // Executes query
        $sql->execute(array($id_module));
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Creates a module.
     * 
     * @param       int $id_admin Admin id logged in
     * @param       string $name Module name
     * 
     * @return      int Module id added or -1 if the module has not been added
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to add new modules
     * @throws      \InvalidArgumentException If admin id provided in the 
     * constructor is empty, less than or equal to zero or if name is empty
     */
    public function new(int $id_admin, string $name) : int
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->getAuthorization()->getLevel() != 0 &&
            $this->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_admin) || $id_admin <= 0)
            throw new \InvalidArgumentException("Admin id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($name))
            throw new \InvalidArgumentException("Name cannot be empty");
            
        $response = -1;
        
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO modules 
            SET name = ? 
        ");
        
        // Executes query
        $sql->execute(array($name));
        
        // Parses result
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
     * @return      bool If the module has been successfully edited
     * 
     * @throws      IllegalAccessException If current admin does not have 
     * authorization to update modules
     * @throws      \InvalidArgumentException If admin id provided in the 
     * constructor is empty, less than or equal to zero or if name is empty
     */
    public function update(int $id_module, string $name)
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->getAuthorization()->getLevel() != 0 &&
            $this->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($name))
            throw new \InvalidArgumentException("Name cannot be empty");
        
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  modules 
            SET     name = ? 
            WHERE   id_module = ?
        ");
        
        // Executes query
        $sql->execute(array($name, $id_module));
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Gets informations about all classes from a module.
     *
     * @param       int $id_module Module id
     *
     * @return      array Informations about all classes from the module
     * 
     * @throws      \InvalidArgumentException If module id is empty, less than
     * or equal to zero
     */
    public function getClassesFromModule($id_module)
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
        
        $videos = new VideosDAO($this->db);
        $questionnaires = new QuestionnairesDAO($this->db);
        
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