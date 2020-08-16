<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Admin;
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
    private $admin;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'modules' table manager.
     *
     * @param       Database $db Database
     * @param       Admin $admin [Optional] Admin logged in
     */
    public function __construct(Database $db, Admin $admin = null)
    {
        $this->db = $db->getConnection();
        $this->admin = $admin;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets all modules from a course.
     *
     * @param       int $id_course Course id
     *
     * @return      Module[] Modules from this course or empty array if course
     * does not have any module
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
            SELECT  *,
                    (SELECT COUNT(*) 
                     FROM videos 
                     WHERE videos.id_module = modules.id_module) AS total_videos,
                    (SELECT COUNT(*) 
                     FROM questionnaires 
                     WHERE questionnaires.id_module = modules.id_module) AS total_questionnaires
            FROM    modules  NATURAL JOIN course_modules
            WHERE   id_course = ?
        ");
        
        // Executes query
        $sql->execute(array($id_course));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $modules = $sql->fetchAll();
            $i = 0;
            
            foreach ($modules as $module) {
                $response[$i] = new Module(
                    (int)$module['id_module'], 
                    $module['name']
                );
                
                $response[$i]->setTotalClasses((int)$module['total_videos'] + (int)$module['total_questionnaires']);
                $response[$i]->setOrder((int)$module['module_order']);
                $i++;
            }
        }
        
        return $response;
    }
    
    /**
     * Gets information about a module.
     * 
     * @param       int $id_module Module id
     *
     * @return      Module Module with the specified id or null if there is no
     * module with this id
     * 
     * @throws      \InvalidArgumentException If module id is empty, less than
     * or equal to zero
     */
    public function get(int $id_module) : Module
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
            
        $response = null;
        
        $sql = $this->db->query("
            SELECT  *
            FROM    modules
            WHERE   id_module = ".$id_module
        );
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $module = $sql->fetch();
            
            $response = new Module(
                (int)$module['id_module'], 
                $module['name']
            );
        }
        
        return $response;
    }
    
    /**
     * Removes a module from a course.
     * 
     * @param       int $id_module Module id to be deleted
     * 
     * @return      bool If module has been successfully deleted
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to delete modules
     * @throws      \InvalidArgumentException If course id is empty, less than
     * or equal to zero or if admin provided in the constructor is empty
     */
    public function delete(int $id_module) : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
        
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
        
        return !empty($sql) && $sql->rowCount() > 0;
    }
    
    /**
     * Creates a module.
     * 
     * @param       string $name Module name
     * 
     * @return      int Module id added or -1 if the module has not been added
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to add new modules
     * @throws      \InvalidArgumentException If admin provided in the 
     * constructor or name is empty
     */
    public function new(string $name) : int
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
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
            $response = (int)$this->db->lastInsertId();
        }
        
        return $response;
    }
    
    /**
     * Add classes in a module.
     * 
     * @param       int $id_module Module id
     * @param       array $classes Classes to be added. Each position has the 
     * following keys:
     * <ul>
     *  <li><b>id_module:</b> Module to which the class belongs</li>
     *  <li><b>type:</b> 'video' or 'questionnaire'</li>
     *  <li><b>order_old:</b> Current class order in module</li>
     *  <li><b>order_new:</b> New class order in module</li>
     * </ul>
     * 
     * @return      bool If classes have been successfully added
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to add update modules
     * @throws      \InvalidArgumentException If admin provided in the 
     * constructor or classes is empty
     */
    public function addClasses(int $id_module, array $classes) : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
                
        if (empty($classes)) {
            throw new \InvalidArgumentException("Classes cannot be empty");
        }

        $id_module_tmp = -1;
        $conflictClasses = array();
        $bindParams = array();
        
        try {
            $this->db->beginTransaction();
    
            foreach ($classes as $class) {
                if ($class['order_new'] == $class['order_old'])
                    continue;
                
                $key = $id_module . "-" . $class['order_new'];
                $tableName = $class['type'] == 'video' ? "videos" : "questionnaires";
                
                // If there is already a class on the module with the same order,
                // moves it to a temporary module
                if ($this->alreadyExists($id_module, (int)$class['order_new'])) {
                    $id_module_tmp = $this->moveToTempModule($id_module, (int)$class['order_new'], $class['type']);
                    
                    $conflictClasses[$key] = array(
                        'id_module_tmp' => $id_module_tmp,
                        'class_order' => $class['order_new'],
                        'type' => $class['type']
                    );
                }
                // If class is on the conflictClasses list, takes it from there
                else if (array_key_exists($key, $conflictClasses)) {
                    $class['id_module'] = $conflictClasses[$key]['id_module_tmp'];
                    unset($conflictClasses[$key]);
                }
                    
                if ($class['id_module'] == $id_module) {
                    $query = "
                        UPDATE  ".$tableName."
                        SET     class_order = ?
                        WHERE   id_module = ? AND class_order = ?
                    ";
                    
                    $bindParams = array(
                        $class['order_new'],
                        $class['id_module'],
                        $class['order_old']
                    );
                }
                else {
                    // class_order = 0 temporary to avoid constraint error
                    $this->db->prepare("
                        UPDATE  ".$tableName."
                        SET     class_order = 0
                        WHERE   id_module = ? AND class_order = ?
                    ")->execute(array($class['id_module'], $class['order_old']));
                    
                    // Moves class to new module
                    $this->db->prepare("
                        UPDATE  ".$tableName."
                        SET     id_module = ?
                        WHERE   id_module = ? AND class_order = 0
                    ")->execute(array($id_module, $class['id_module']));
                    
                    // Sets class order
                    $query = "
                        UPDATE  ".$tableName."
                        SET     class_order = ?
                        WHERE   id_module = ? AND class_order = 0
                    ";

                    $bindParams = array(
                        $class['order_new'],
                        $id_module
                    );
                    
//                     FALHOU AO FAZER DA FORMA ABAIXO - MUDAVA class_order = 0 E NÃO ALTERAVA O id_module
//                     $query = "
//                         UPDATE  ".$tableName."
//                         SET     id_module = ? AND class_order = ?
//                         WHERE   id_module = ? AND class_order = ?
//                     ";
                    
//                     $bindParams = array(
//                         $id_module,
//                         $class['order_new'],
//                         $class['id_module'],
//                         $class['order_old']
//                     );
                }

                $sql = $this->db->prepare($query);
                $sql->execute($bindParams);
            }
            
            // Updates remaining conflicting classes
            if (!empty($conflictClasses)) {
                foreach ($conflictClasses as $class) {
                    if ($class['type'] == 'video') {
                        $sql = $this->db->prepare("
                                UPDATE  videos
                                SET     id_module = ?
                                WHERE   id_module = ? AND class_order = ?
                        ");
                    }
                    else {
                        $sql = $this->db->prepare("
                                UPDATE  questionnaire
                                SET     id_module = ?
                                WHERE   id_module = ? AND class_order = ?
                        ");
                    }
                
                    $sql->execute(array(
                        $id_module,
                        $class['id_module_tmp'],
                        $class['class_order']
                    ));
                }
            }
            
            $this->db->commit();
        }
        catch (\Exception $e) {
            echo $e->getMessage();
            
            $this->db->rollback();
            
            throw $e;
        }
        
        return true;
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
     * @throws      \InvalidArgumentException If module id is less than or 
     * equal to zero or if name is empty or if admin provided in the 
     * constructor is empty 
     */
    public function update(int $id_module, string $name)
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 1)
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
     * Gets all registered modules.
     * 
     * @return      Module[] Modules
     */
    public function getAll()
    {
        $response = array();
        
        $sql = $this->db->query("
            SELECT  *,
                    (SELECT COUNT(*) 
                     FROM videos 
                     WHERE videos.id_module = modules.id_module) AS total_videos,
                    (SELECT COUNT(*) 
                     FROM questionnaires 
                     WHERE questionnaires.id_module = modules.id_module) AS total_questionnaires
            FROM    modules
        ");
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $i = 0;
            
            foreach ($sql->fetchAll() as $module) {
                $response[$i] = new Module(
                    (int)$module['id_module'],
                    $module['name']
                );
                
                $response[$i]->setTotalClasses((int)$module['total_videos'] + (int)$module['total_questionnaires']);
                $i++;
            }
        }
        
        return $response;
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
        
        $class_video = $videos->getAllFromModule($id_module);
        $class_questionnaire = $questionnaires->getAllFromModule($id_module);
        
        foreach ($class_video as $class) {
            $response[$class->getClassOrder()] = $class;
        }
        
        foreach ($class_questionnaire as $class) {
            $response[$class->getClassOrder()] = $class;
        }
        
        return $response;
    }
    
    /**
     * Moves classes to a temporary module.
     * 
     * @param       int $id_module Module to which the class belongs
     * @param       int $class_order Class order in module
     * @param       string $class_type Class type ('video' or 'questionnaire')
     * @return      int Module id to which the classes have been moved
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to add update modules
     * @throws      \InvalidArgumentException If class type or admin provided
     * in the constructor or classes is empty or id module id or class order is
     * empty or less than or equal to zero
     */
    private function moveToTempModule(int $id_module, int $class_order, string $class_type)
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Class order cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($class_type))
            throw new \InvalidArgumentException("Class type cannot be empty");
            
        $tmp_name = md5(rand(1,9999).time().rand(1,9999));
        
        $this->db->query("
            INSERT INTO modules 
            SET name = '.$tmp_name.' 
        ");
        
        $id_module_tmp = $this->db->lastInsertId();
        
        if ($id_module_tmp != -1) {
            if ($class_type == 'video') {
                
                
                $sql = $this->db->prepare("
                    UPDATE  videos
                    SET     id_module = ?
                    WHERE   id_module = ? AND class_order = ?
                ");
            }
            else {
                $sql = $this->db->prepare("
                    UPDATE  questionnaires
                    SET     id_module = ?
                    WHERE   id_module = ? AND class_order = ?
                ");
            }
            
            $sql->execute(array(
                $id_module_tmp, 
                $id_module, 
                $class_order
            ));
        }
        
        return $id_module_tmp;
    }
    
    /**
     * Checks if there is already a class in a module with a certain order.
     * 
     * @param       int $id_module Module id
     * @param       int $class_order Class order
     * 
     * @return      bool If there is already a class in the module with the 
     * given order
     * 
     * @throws      \InvalidArgumentException If module id or class order is 
     * empty, less than or equal to zero
     */
    private function alreadyExists(int $id_module, int $class_order) : bool
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Class order cannot be empty ".
                "or less than or equal to zero");
        
        $sql = $this->db->query("
            SELECT  COUNT(*) AS existClass
            FROM    (
                SELECT  id_module, class_order
                FROM    videos
                WHERE   id_module = ".$id_module." AND class_order = ".$class_order."
                UNION
                SELECT  id_module, class_order
                FROM    questionnaires
                WHERE   id_module = ".$id_module." AND class_order = ".$class_order."
            ) AS tmp
        ");
        
        return $sql->fetch()['existClass'] > 0;
    }
    
//     /**
//      * Gets highest module order in use.
//      * 
//      * @param       int $id_course Course id
//      * @param       int $id_module Module id
//      * 
//      * @return      int Highest module order or -1 of module does not belongs 
//      * to the course
//      * 
//      * @throws      \InvalidArgumentException If course id or module id is 
//      * empty, less than or equal to zero
//      */
//     public function getHighestOrder(int $id_course, int $id_module) : int
//     {
//         if (empty($id_course) || $id_course <= 0)
//             throw new \InvalidArgumentException("Course id cannot be empty ".
//                 "or less than or equal to zero");
            
//         if (empty($id_module) || $id_module <= 0)
//             throw new \InvalidArgumentException("Module id cannot be empty ".
//                 "or less than or equal to zero");
            
//         $response = -1;
        
//         $sql = $this->db->query("
//             SELECT      module_order
//             FROM        course_modules
//             WHERE       id_course = ".$id_course." AND id_module = ".$id_module."
//             ORDER BY    module_order DESC
//         ");
        
//         if (!empty($sql) && $sql->rowCount() > 0) {
//             $response = (int)$sql->fetch()['module_order'];
//         }
        
//         return $response;
//     }
}