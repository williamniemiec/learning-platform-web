<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Questionnaire;
use models\util\IllegalAccessException;
use models\Module;
use models\Admin;
use models\Action;


/**
 * Responsible for managing 'questionnaires' table.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class QuestionnairesDAO extends ClassesDAO
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
     * Creates 'questionnaires' table manager.
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
     * Gets questionnaire class
     *
     * @param       int $id_module Module id that the class belongs to
     * @param       int $class_order Class order inside the module that it 
     * belongs to
     *
     * @return      Questionnaire Questionnaire class or null if class does not
     * exist
     * 
     * @throws      \InvalidArgumentException If module id or class order is 
     * empty, less than or equal to zero
     */
    public function get(int $id_module, int $class_order) : ?Questionnaire
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Class order cannot be empty ".
                "or less than or equal to zero");
        
        $response = null;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  * 
            FROM    questionnaires NATURAL JOIN modules
            WHERE   id_module = ? AND class_order = ?
        ");
        
        // Executes query
        $sql->execute(array($id_module, $class_order));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $class = $sql->fetch();
            
            $response = new Questionnaire(
                new Module((int)$class['id_module'], $class['name']),
                (int)$class['class_order'],
                $class['question'],
                $class['q1'],
                $class['q2'],
                $class['q3'],
                $class['q4'],
                (int)$class['answer']
            ); 
        }
        
        return $response;
    }
    
    /**
     * Gets all registered questionnaire classes.
     *
     * @param       int $limit [Optional] Maximum classes returned
     * @param       int $offset [Optional] Ignores first results from the return
     *
     * @return      Questionnaire[] Registered questionnaire classes or empty
     * array if there are no registered questionnaire classes
     */
    public function getAll(int $limit = -1, int $offset = -1) : array
    {
        $response = array();
        
        $query = "
            SELECT      *
            FROM        questionnaires NATURAL JOIN modules
            ORDER BY    question
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
            foreach ($sql->fetchAll() as $class) {
                $response[] = new Questionnaire(
                    new Module((int)$class['id_module'], $class['name']),
                    (int)$class['class_order'],
                    $class['question'],
                    $class['q1'],
                    $class['q2'],
                    $class['q3'],
                    $class['q4'],
                    (int)$class['answer']
                );
            }
        }
        
        return $response;
    }
    
    /**
     * Gets all questionnaire classes from a module.
     *
     * @param       int $id_module Module id
     *
     * @return      Questionnaire[] Classes that belongs to the module
     *
     * @throws      \InvalidArgumentException If module id is empty or less
     * than or equal to zero
     *
     * @Override
     */
    public function getAllFromModule(int $id_module) : array
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
            
        $response = array();
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    questionnaires NATURAL JOIN modules
            WHERE   id_module = ?
        ");
            
        // Executes query
        $sql->execute(array($id_module));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $classes = $sql->fetchAll();
            
            foreach ($classes as $class) {
                $response[] = new Questionnaire(
                    new Module((int)$class['id_module'], $class['name']),
                    (int)$class['class_order'],
                    $class['question'],
                    $class['q1'],
                    $class['q2'],
                    $class['q3'],
                    $class['q4'],
                    (int)$class['answer']
                );
            }
        }
        
        return $response;
    }
    
    /**
     * Creates a new questionnaire class.
     * 
     * @param       Questionnaire $questionnaire Class to be added
     * 
     * @return      bool If the class has been successfully added
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to create new classes
     * @throws      \InvalidArgumentException If questionnaire or admin 
     * provided in the constructor is empty
     */
    public function add(Questionnaire $questionnaire) : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($questionnaire))
            throw new \InvalidArgumentException("Questionnaire cannot be empty");
        
        $response = false;
            
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO questionnaires 
            (id_module, class_order, op1, op2, op3, op4, answer) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Executes query
        $sql->execute(array(
            $questionnaire->getModule()->getId(), 
            $questionnaire->getClassOrder(), 
            $questionnaire->getQuestion(), 
            $questionnaire->getQ1(), 
            $questionnaire->getQ2(),
            $questionnaire->getQ3(),
            $questionnaire->getQ4(),
            $questionnaire->getAnswer()
        ));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->addClass($questionnaire->getModuleId(), $questionnaire->getClassOrder());
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
    
    /**
     * Updates a questionnaire class.
     * 
     * @param       int $id_quest Class id
     * @param       string $question New question title
     * @param       string $op1 New option 1
     * @param       string $op2 New option 2
     * @param       string $op3 New option 3
     * @param       string $op4 New option 4
     * @param       string $answer New answer
     * 
     * @return      bool If class has been successfully edited
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update classes
     * @throws      \InvalidArgumentException If questionnaire or admin 
     * provided in the constructor is empty
     */
    public function update(Questionnaire $questionnaire) : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($questionnaire))
            throw new \InvalidArgumentException("Questionnaire cannot be empty");
        
        $response = false;
        
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  questionnaires
            SET     question = ?,
                    q1 = ?,
                    q2 = ?,
                    q3 = ?,
                    q4 = ?,
                    answer = ?
            WHERE id_module = ? AND class_order = ?
        ");
        
        // Executes query
        $sql->execute(array(
            $questionnaire->getQuestion(),
            $questionnaire->getQ1(),
            $questionnaire->getQ2(),
            $questionnaire->getQ3(),
            $questionnaire->getQ4(),
            pack('n', $questionnaire->getAnswer()),
            $questionnaire->getModule()->getId(),
            $questionnaire->getClassOrder(),
        ));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateClass($questionnaire->getModuleId(), $questionnaire->getClassOrder());
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
    
    /**
     * Removes a questionnaire class.
     * 
     * @param       int $id_module Module id to which the class belongs
     * @param       int $class_order Class order in the module
     * 
     * @return      bool If class has been successfully removed 
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to delete classes
     * @throws      \InvalidArgumentException If module id or class order is
     * empty, less than or equal to zero or if admin provided in the 
     * constructor is empty
     */
    public function delete(int $id_module, int $class_order) : bool
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
            
        $response = false;
            
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM questionnaires
            WHERE id_module = ? AND class_order = ?
        ");
        
        // Executes query
        $sql->execute(array($id_module, $class_order));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->deleteClass($id_module, $class_order);
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
    
    /**
     * Changes module ans class order of a class.
     *
     * @param       Questionnaire $questionnaire Class to be updated
     * @param       int $newIdModule New module id
     * @param       int $newClassOrder New class order
     *
     * @return      bool If chass has been successfully updated
     *
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update classes
     * @throws      \InvalidArgumentException If video or admin provided in the
     * constructor is empty or if module id or class order is empty or less
     * than or equal to zero
     */
    public function updateModule(Questionnaire $questionnaire, int $newIdModule, int $newClassOrder) : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($newIdModule) || $newIdModule <= 0)
            throw new \InvalidArgumentException("New module id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($newClassOrder) || $newClassOrder <= 0)
            throw new \InvalidArgumentException("Class order cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($questionnaire))
            throw new \InvalidArgumentException("Questionnaire cannot be empty");
            
        $response = false;
            
        // class_order = 0 temporary to avoid constraint error
        $this->db->prepare("
            UPDATE  questionnaires
            SET     class_order = 0
            WHERE   id_module = ? AND class_order = ?
        ")->execute(array($questionnaire->getModule()->getId(), $questionnaire->getClassOrder()));
                            
        // Moves class to new module
        $this->db->prepare("
            UPDATE  questionnaires
            SET     id_module = ?
            WHERE   id_module = ? AND class_order = 0
        ")->execute(array($newIdModule, $questionnaire->getModule()->getId()));
                            
        // Sets class order
        $sql = $this->db->prepare("
            UPDATE  questionnaires
            SET     class_order = ?
            WHERE   id_module = ? AND class_order = 0
        ");
                            
        $sql->execute(array(
            $newClassOrder,
            $newIdModule
        ));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateClass($questionnaire->getModuleId(), $questionnaire->getClassOrder());
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
}