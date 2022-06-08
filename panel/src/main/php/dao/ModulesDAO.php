<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Admin;
use domain\Module;
use domain\Action;
use util\IllegalAccessException;


/**
 * Responsible for managing 'modules' table.
 */
class ModulesDAO extends DAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
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
        parent::__construct($db);
        $this->admin = $admin;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets all modules from a course.
     *
     * @param       int $idCourse Course id
     *
     * @return      Module[] Modules from this course or empty array if course
     * does not have any module
     * 
     * @throws      \InvalidArgumentException If course id is empty, less than
     * or equal to zero
     */
    public function getFromCourse(int $idCourse) : array
    {
        $this->validateCourseId($idCourse);
        
        $response = array();
        
        // Query construction
        $this->withQuery("
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
        $sql->execute(array($idCourse));
        
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

    private function validateCourseId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Course id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }
    
    /**
     * Gets information about a module.
     * 
     * @param       int $idModule Module id
     *
     * @return      Module Module with the specified id or null if there is no
     * module with this id
     * 
     * @throws      \InvalidArgumentException If module id is empty, less than
     * or equal to zero
     */
    public function get(int $idModule) : Module
    {
        $this->validateCourseId($idModule);
        $response = null;
        
        $sql = $this->db->query("
            SELECT  *
            FROM    modules
            WHERE   id_module = ".$idModule
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

    private function validateModuleId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Module id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }
    
    /**
     * Removes a module from a course.
     * 
     * @param       int idModule Module id to be deleted
     * 
     * @return      bool If module has been successfully deleted
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to delete modules
     * @throws      \InvalidArgumentException If course id is empty, less than
     * or equal to zero or if admin provided in the constructor is empty
     */
    public function delete(int $idModule) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateModuleId($idModule);
        
        $response = false;
            
        // Query construction
        $this->withQuery("
            DELETE FROM modules
            WHERE id_module = ?
        ");
        
        // Executes query
        $sql->execute(array($idModule));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->deleteModule($idModule);
            $adminsDAO->newAction($action);
        }
        
        return $response;
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
            
        $this->validateAuthorization(0, 1);
        $this->validateName($name);
            
        $response = -1;
        
        // Query construction
        $this->withQuery("
            INSERT INTO modules 
            SET name = ? 
        ");
        
        // Executes query
        $sql->execute(array($name));
        
        // Parses result
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = (int)$this->db->lastInsertId();
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->addModule($response);
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }

    private function validateName($name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException("Name cannot be empty");
        }
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
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateClasses($classes);

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
                }

                $this->withQuery($query);
                $sql->execute($bindParams);
            }
            
            // Updates remaining conflicting classes
            if (!empty($conflictClasses)) {
                foreach ($conflictClasses as $class) {
                    if ($class['type'] == 'video') {
                        $this->withQuery("
                                UPDATE  videos
                                SET     id_module = ?
                                WHERE   id_module = ? AND class_order = ?
                        ");
                    }
                    else {
                        $this->withQuery("
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
        
        $action = new Action();
        $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $action->updateModule($id_module);
        $adminsDAO->newAction($action);

        return true;
    }

    private function validateClasses($classes)
    {
        if (empty($classes)) {
            throw new \InvalidArgumentException("Classes cannot be empty");
        }
    }
    
    /**
     * Edits a module from a course.
     * 
     * @param       int $idModule Module id
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
    public function update(int $idModule, string $name)
    {
        $this->validateLoggedAdmin(); 
        $this->validateAuthorization(0, 1);
        $this->validateModuleId($idModule);
        $this->validateName($name);
        
        $response = false;
            
        // Query construction
        $this->withQuery("
            UPDATE  modules 
            SET     name = ? 
            WHERE   id_module = ?
        ");
        
        // Executes query
        $sql->execute(array($name, $idModule));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateModule($idModule);
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
    
    /**
     * Gets all registered modules.
     * 
     * @param       int $limit [Optional] Maximum modules returned
     * @param       int $offset [Optional] Ignores first results from the return    
     * 
     * @return      Module[] Modules
     */
    public function getAll(int $limit = -1, int $offset = -1)
    {
        $response = array();
        
        $query = "
            SELECT  *,
                    (SELECT COUNT(*) 
                     FROM videos 
                     WHERE videos.id_module = modules.id_module) AS total_videos,
                    (SELECT COUNT(*) 
                     FROM questionnaires 
                     WHERE questionnaires.id_module = modules.id_module) AS total_questionnaires
            FROM    modules
        ";
        
        // Limits the results (if a limit was given)
        if ($limit > 0) {
            if ($offset > 0)
                $query .= " LIMIT ".$offset.",".$limit;
            else
                $query .= " LIMIT ".$limit;
        }
        
        $sql = $this->db->query($query);
        
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
     * @param       int idModule Module id
     *
     * @return      array Informations about all classes from the module
     * 
     * @throws      \InvalidArgumentException If module id is empty, less than
     * or equal to zero
     */
    public function getClassesFromModule($idModule)
    {
        $this->validateModuleId($idModule);
        
        $videos = new VideosDAO($this->db);
        $questionnaires = new QuestionnairesDAO($this->db);
        
        $class_video = $videos->getAllFromModule($idModule);
        $class_questionnaire = $questionnaires->getAllFromModule($idModule);
        
        foreach ($class_video as $class) {
            $response[$class->getClassOrder()] = $class;
        }
        
        foreach ($class_questionnaire as $class) {
            $response[$class->getClassOrder()] = $class;
        }
        
        return $response;
    }
    
    /**
     * Gets highest class order in use from a module.
     *
     * @param       int idModule Module id
     *
     * @return      int Highest module order or -1 of module does not belongs
     * to the course
     *
     * @throws      \InvalidArgumentException if module id is empty, less than
     * or equal to zero
     */
    public function getHighestOrderInModule(int $idModule) : int
    {
        $this->validateModuleId($idModule);
        $response = -1;
            
        $sql = $this->db->query("
            SELECT      MAX(class_order) AS max_class_order
            FROM (
                SELECT      class_order
                FROM        videos
                WHERE       id_module = ".$idModule."
                UNION
                SELECT      class_order
                FROM        questionnaires
                WHERE       id_module = ".$idModule."
            ) AS tmp
        ");
            
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = (int)$sql->fetch()['max_class_order'];
        }
        
        return $response;
    }
    
    /**
     * Gets total of modules.
     *
     * @return      int Total of modules
     */
    public function count() : int
    {
        return (int)$this->db->query("
            SELECT  COUNT(*) AS total
            FROM    modules
        ")->fetch()['total'];
    }
    
    /**
     * Moves classes to a temporary module.
     * 
     * @param       int idModule Module to which the class belongs
     * @param       int classOrder Class order in module
     * @param       string classType Class type ('video' or 'questionnaire')
     * @return      int Module id to which the classes have been moved
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to add update modules
     * @throws      \InvalidArgumentException If class type or admin provided
     * in the constructor or classes is empty or id module id or class order is
     * empty or less than or equal to zero
     */
    private function moveToTempModule(int $idModule, int $classOrder, string $classType)
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateModuleId($idModule);
        $this->validateClassOrder($classOrder);
        $this->validateClassType($classType);
            
        $tmp_name = md5(rand(1,9999).time().rand(1,9999));
        
        $this->db->query("
            INSERT INTO modules 
            SET name = '.$tmp_name.' 
        ");
        
        $id_module_tmp = $this->db->lastInsertId();
        
        if ($id_module_tmp != -1) {
            if ($classType == 'video') {
                
                
                $this->withQuery("
                    UPDATE  videos
                    SET     id_module = ?
                    WHERE   id_module = ? AND class_order = ?
                ");
            }
            else {
                $this->withQuery("
                    UPDATE  questionnaires
                    SET     id_module = ?
                    WHERE   id_module = ? AND class_order = ?
                ");
            }
            
            $sql->execute(array(
                $id_module_tmp, 
                $idModule, 
                $classOrder
            ));
        }
        
        return $id_module_tmp;
    }

    private function validateClassOrder($order)
    {
        if (empty($order) || $order <= 0) {
            throw new \InvalidArgumentException("Class order cannot be empty ".
                                                "or less than or equal to zero");
        }
    }

    private function validateClassType($type)
    {
        if (empty($type)) {
            throw new \InvalidArgumentException("Class type cannot be empty");
        }
    }
    
    /**
     * Checks if there is already a class in a module with a certain order.
     * 
     * @param       int $idModule Module id
     * @param       int $classOrder Class order
     * 
     * @return      bool If there is already a class in the module with the 
     * given order
     * 
     * @throws      \InvalidArgumentException If module id or class order is 
     * empty, less than or equal to zero
     */
    private function alreadyExists(int $idModule, int $classOrder) : bool
    {
        $this->validateModuleId($idModule);
        $this->validateClassOrder($classOrder);
        
        $sql = $this->db->query("
            SELECT  COUNT(*) AS existClass
            FROM    (
                SELECT  id_module, class_order
                FROM    videos
                WHERE   id_module = ".$idModule." AND class_order = ".$classOrder."
                UNION
                SELECT  id_module, class_order
                FROM    questionnaires
                WHERE   id_module = ".$idModule." AND class_order = ".$classOrder."
            ) AS tmp
        ");
        
        return $sql->fetch()['existClass'] > 0;
    }
}
