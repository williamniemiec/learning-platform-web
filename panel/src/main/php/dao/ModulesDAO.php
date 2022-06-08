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
        $this->runQueryWithArguments($idCourse);

        return $this->parseGetAllResponseQuery();
    }

    private function parseGetAllResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $modules = array();
        $i = 0;
        
        foreach ($this->getAllResponseQuery() as $module) {
            $modules[$i] = new Module(
                (int) $module['id_module'], 
                $module['name']
            );
            $modules[$i]->setTotalClasses(
                (int) $module['total_videos'] 
                + (int) $module['total_questionnaires']
            );
            $modules[$i]->setOrder((int) $module['module_order']);
            $i++;
        }

        return $modules;
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
        $this->withQuery("
            SELECT  *
            FROM    modules
            WHERE   id_module = ".$idModule
        );
        $this->runQueryWithoutArguments();

        return $this->parseGetResponseQuery();
    }

    private function validateModuleId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Module id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }

    private function parseGetResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return null;
        }
        
        $moduleRaw = $this->getResponseQuery();
        
        return new Module(
            (int) $moduleRaw['id_module'], 
            $moduleRaw['name']
        );
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
        $this->withQuery("
            DELETE FROM modules
            WHERE id_module = ?
        ");
        $this->runQueryWithArguments($idModule);
        
        return $this->parseDeleteResponseQuery($idModule);
    }

    private function parseDeleteResponseQuery($moduleId)
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->deleteModule($moduleId);
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);
        
        return true;
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
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateName($name);
        $this->withQuery("
            INSERT INTO modules 
            SET name = ? 
        ");
        $this->runQueryWithArguments($name);
        
        return $this->parseNewResponseQuery();
    }

    private function parseNewResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return -1;
        }

        $newId = ((int) $this->db->lastInsertId());
        $action = new Action();
        $action->addModule($newId);
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);
        
        return $newId;
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
     * @param       int $idModule Module id
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
    public function addClasses(int $idModule, array $classes) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateClasses($classes);

        try {
            $this->db->beginTransaction();
            $conflictedClasses = $this->addClassesWithoutConflict($idModule, $classes);
            $this->solveConflictedClasses($conflictedClasses, $idModule);
            $this->db->commit();
        }
        catch (\Exception $e) {
            echo $e->getMessage();
            $this->db->rollback();
            
            throw $e;
        }

        return $this->parseUpdateResponseQuery($idModule);
    }

    private function validateClasses($classes)
    {
        if (empty($classes)) {
            throw new \InvalidArgumentException("Classes cannot be empty");
        }
    }

    function addClassesWithoutConflict($idModule, $classes)
    {
        $conflictedClasses = array();
        
        foreach ($classes as $class) {
            if ($class['order_new'] == $class['order_old']) {
                continue;
            }
            
            $key = $idModule . "-" . $class['order_new'];
            if ($this->hasSomeClassWithOrder((int) $class['order_new'], $idModule)) {
                $idTmpModule = $this->moveToTempModule(
                    $idModule, 
                    (int) $class['order_new'], 
                    $class['type']
                );
                $conflictedClasses[$key] = array(
                    'id_module_tmp' => $idTmpModule,
                    'class_order' => $class['order_new'],
                    'type' => $class['type']
                );
            }
            else if (array_key_exists($key, $conflictedClasses)) {
                $class['id_module'] = $conflictedClasses[$key]['id_module_tmp'];
                unset($conflictedClasses[$key]);
            }

            if ($class['id_module'] != $idModule) {
                $this->changeClassModule($class, $idModule);
            }

            $this->withQuery($this->buildUpdateClassOrderQuery($class, $idModule));
            $this->runQueryWithArguments($this->buildUpdateClassOrderQueryArguments($class, $idModule));
        }
        
        return $conflictedClasses;
    }

    function solveConflictedClasses($conflictedClasses, $idModule)
    {
        if (empty($conflictedClasses)) {
            return;
        }

        foreach ($conflictedClasses as $class) {
            $table = $this->extractTableNameFromClass($class);

            $this->withQuery("
                UPDATE  ".$table."
                SET     id_module = ?
                WHERE   id_module = ? AND class_order = ?
            ");
            $this->runQueryWithArguments(
                $idModule,
                $class['id_module_tmp'],
                $class['class_order']
            );
        }
    }

    /**
     * Moves classes to a temporary module.
     * 
     * @param       int $idModule Module to which the class belongs
     * @param       int $classOrder Class order in module
     * @param       string $classType Class type ('video' or 'questionnaire')
     * 
     * @return      int Module id to which the classes have been moved
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to add update modules
     * @throws      \InvalidArgumentException If class type or admin provided
     * in the constructor or classes is empty or id module id or class order is
     * empty or less than or equal to zero
     */
    private function moveToTempModule(int $idModule, int $order, string $type)
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateModuleId($idModule);
        $this->validateClassOrder($order);
        $this->validateClassType($type);
        $this->withQuery("
            INSERT INTO modules 
            SET name = '".$this->generateTempModuleName()."' 
        ");
        $this->runQueryWithoutArguments();
        
        return $this->parseMoveToTempModuleResponseQuery($idModule, $order, $type);
    }

    private function generateTempModuleName()
    {
        return md5(rand(1,9999).time().rand(1,9999));
    }

    private function parseMoveToTempModuleResponseQuery($moduleId, $order, $type)
    {
        if ($this->db->lastInsertId() == -1) {
            return -1;
        }

        $newId = $this->db->lastInsertId();

        if ($type == 'video') {
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
        
        $this->runQueryWithArguments($newId, $moduleId, $order);

        return $newId;
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
     * @param       int $order Class order
     * @param       int $idModule Module id
     * 
     * @return      bool If there is already a class in the module with the 
     * given order
     * 
     * @throws      \InvalidArgumentException If module id or class order is 
     * empty, less than or equal to zero
     */
    private function hasSomeClassWithOrder(int $order, int $idModule) : bool
    {
        $this->validateModuleId($idModule);
        $this->validateClassOrder($order);
        
        $this->withQuery("
            SELECT  COUNT(*) AS existClass
            FROM    (
                SELECT  id_module, class_order
                FROM    videos
                WHERE   id_module = ".$idModule." AND class_order = ".$order."
                UNION
                SELECT  id_module, class_order
                FROM    questionnaires
                WHERE   id_module = ".$idModule." AND class_order = ".$order."
            ) AS tmp
        ");
        $this->runQueryWithoutArguments();
        
        return ($this->getResponseQuery()['existClass'] > 0);
    }

    private function buildUpdateClassOrderQuery($class, $moduleId)
    {
        $tableName = $this->extractTableNameFromClass($class);
        $query = "
            UPDATE  ".$tableName."
            SET     class_order = ?
            WHERE   id_module = ? AND 
        ";

        if ($class['id_module'] == $moduleId) {
            $query .= "class_order = ?";
        }
        else {
            $query .= "class_order = 0";
        }

        return $query;
    }

    private function extractTableNameFromClass($class)
    {
        return ($class['type'] == 'video') ? "videos" : "questionnaires";
    }

    private function changeClassModule($class, $moduleId)
    {
        $tableName = $this->extractTableNameFromClass($class);

        // Sets class_order = 0 temporary to avoid constraint error
        $this->withQuery("
            UPDATE  ".$tableName."
            SET     class_order = 0
            WHERE   id_module = ? AND class_order = ?
        ");
        $this->runQueryWithArguments($class['id_module'], $class['order_old']);
        
        // Moves class to new module
        $this->withQuery("
            UPDATE  ".$tableName."
            SET     id_module = ?
            WHERE   id_module = ? AND class_order = 0
        ");
        $this->runQueryWithArguments($moduleId, $class['id_module']);
    }

    private function buildUpdateClassOrderQueryArguments($class, $moduleId)
    {
        $bindParams = array(
            $class['order_new']
        );

        if ($class['id_module'] == $moduleId) {
            $bindParams[] = $class['id_module'];
            $bindParams[] = $class['order_old'];
        }
        else {
            $bindParams[] = $moduleId;
        }

        return $bindParams;
    }

    private function parseUpdateResponseQuery($moduleId)
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->updateModule($moduleId);
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);
        
        return true;
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
        $this->withQuery("
            UPDATE  modules 
            SET     name = ? 
            WHERE   id_module = ?
        ");
        $this->runQueryWithArguments($name, $idModule);
        
        return $this->parseUpdateResponseQuery($idModule);
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
        $this->withQuery($this->buildGetAllQuery($limit, $offset));
        $this->runQueryWithoutArguments();
        
        return $this->parseGetAllResponseQuery();
    }

    private function buildGetAllQuery($limit, $offset)
    {
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
        
        if ($limit > 0) {
            if ($offset > 0) {
                $query .= " LIMIT ".$offset.",".$limit;
            }
            else {
                $query .= " LIMIT ".$limit;
            }
        }

        return $query;
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
        $this->withQuery("
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
        $this->runQueryWithoutArguments();
        
        return $this->parseGetHighestOrderResponseQuery();
    }

    private function parseGetHighestOrderResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return -1;
        }

        return ((int) $this->getResponseQuery()['max_class_order']);
    }
    
    /**
     * Gets total of modules.
     *
     * @return      int Total of modules
     */
    public function count() : int
    {
        $this->withQuery("
            SELECT  COUNT(*) AS total
            FROM    modules
        ");
        $this->runQueryWithoutArguments();

        return ((int) $this->getResponseQuery()['total']);
    }
}
