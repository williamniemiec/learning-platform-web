<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Questionnaire;
use domain\Module;
use domain\Admin;
use domain\Action;
use util\IllegalAccessException;


/**
 * Responsible for managing 'questionnaires' table.
 */
class QuestionnairesDAO extends ClassesDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
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
        parent::__construct($db);
        $this->admin = $admin;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets questionnaire class
     *
     * @param       int idModule Module id that the class belongs to
     * @param       int classOrder Class order inside the module that it 
     * belongs to
     *
     * @return      Questionnaire Questionnaire class or null if class does not
     * exist
     * 
     * @throws      \InvalidArgumentException If module id or class order is 
     * empty, less than or equal to zero
     */
    public function get(int $idModule, int $classOrder) : ?Questionnaire
    {
        $this->validateModuleId($idModule);
        $this->validateClassOrder($classOrder);
        $this->withQuery("
            SELECT  * 
            FROM    questionnaires NATURAL JOIN modules
            WHERE   id_module = ? AND class_order = ?
        ");
        $this->runQueryWithArguments($idModule, $classOrder);
        
        return $this->parseGetResponseQuery();
    }

    private function parseGetResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return null;
        }
        
        $class = $this->getResponseQuery();
        
        return new Questionnaire(
            new Module((int) $class['id_module'], $class['name']),
            (int) $class['class_order'],
            $class['question'],
            $class['q1'],
            $class['q2'],
            $class['q3'],
            $class['q4'],
            (int) $class['answer']
        ); 
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
        $this->withQuery($this->buildGetAllQuery($limit, $offset));
        $this->runQueryWithoutArguments();

        return $this->parseGetAllResponseQuery();
    }

    private function buildGetAllQuery($limit, $offset)
    {
        $query = "
            SELECT      *
            FROM        questionnaires NATURAL JOIN modules
            ORDER BY    question
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

    private function parseGetAllResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $classes = array();
        
        foreach ($this->getAllResponseQuery() as $class) {
            $classes[] = new Questionnaire(
                new Module((int) $class['id_module'], $class['name']),
                (int) $class['class_order'],
                $class['question'],
                $class['q1'],
                $class['q2'],
                $class['q3'],
                $class['q4'],
                (int) $class['answer']
            );
        }

        return $classes;
    }
    
    /**
     * Gets all questionnaire classes from a module.
     *
     * @param       int idModule Module id
     *
     * @return      Questionnaire[] Classes that belongs to the module
     *
     * @throws      \InvalidArgumentException If module id is empty or less
     * than or equal to zero
     *
     * @Override
     */
    public function getAllFromModule(int $idModule) : array
    {
        $this->validateModuleId($idModule);
        $this->withQuery("
            SELECT  *
            FROM    questionnaires NATURAL JOIN modules
            WHERE   id_module = ?
        ");
        $this->runQueryWithArguments($idModule);
        
        return $this->parseGetAllResponseQuery();
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
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateQuestionnaire($questionnaire);
        $this->withQuery("
            INSERT INTO questionnaires 
            (id_module, class_order, op1, op2, op3, op4, answer) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $this->runQueryWithArguments(
            $questionnaire->getModule()->getId(), 
            $questionnaire->getClassOrder(), 
            $questionnaire->getQuestion(), 
            $questionnaire->getQ1(), 
            $questionnaire->getQ2(),
            $questionnaire->getQ3(),
            $questionnaire->getQ4(),
            $questionnaire->getAnswer()
        );
               
        return $this->parseAddResponseQuery($questionnaire);
    }

    private function validateQuestionnaire($questionnaire)
    {
        if (empty($questionnaire)) {
            throw new \InvalidArgumentException("Questionnaire cannot be empty");
        }
    }

    private function parseAddResponseQuery($questionnaire)
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->addClass($questionnaire->getModuleId(), $questionnaire->getClassOrder());
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);
        
        return true;
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
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateQuestionnaire($questionnaire);
        $this->withQuery("
            UPDATE  questionnaires
            SET     question = ?,
                    q1 = ?,
                    q2 = ?,
                    q3 = ?,
                    q4 = ?,
                    answer = ?
            WHERE id_module = ? AND class_order = ?
        ");
        $this->runQueryWithArguments(
            $questionnaire->getQuestion(),
            $questionnaire->getQ1(),
            $questionnaire->getQ2(),
            $questionnaire->getQ3(),
            $questionnaire->getQ4(),
            pack('n', $questionnaire->getAnswer()),
            $questionnaire->getModule()->getId(),
            $questionnaire->getClassOrder(),
        );
        
        return $this->parseUpdateResponseQuery($questionnaire);
    }

    private function parseUpdateResponseQuery($questionnaire)
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->updateClass($questionnaire->getModuleId(), $questionnaire->getClassOrder());
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);
        
        return true;
    }
    
    /**
     * Removes a questionnaire class.
     * 
     * @param       int $idModule Module id to which the class belongs
     * @param       int $classOrder Class order in the module
     * 
     * @return      bool If class has been successfully removed 
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to delete classes
     * @throws      \InvalidArgumentException If module id or class order is
     * empty, less than or equal to zero or if admin provided in the 
     * constructor is empty
     */
    public function delete(int $idModule, int $classOrder) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateModuleId($idModule);
        $this->validateClassOrder($classOrder);
        $this->withQuery("
            DELETE FROM questionnaires
            WHERE id_module = ? AND class_order = ?
        ");
        $this->runQueryWithArguments($idModule, $classOrder);
        
        return $this->parseDeleteResponseQuery($idModule, $classOrder);
    }

    private function parseDeleteResponseQuery($idModule, $classOrder)
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->deleteClass($idModule, $classOrder);
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);
        
        return true;
    }
    
    /**
     * Changes module ans class order of a class.
     *
     * @param       Questionnaire $questionnaire Class to be updated
     * @param       int $newIdModule New module id
     * @param       int $newClassOrder New class order
     *
     * @return      bool If class has been successfully updated
     *
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update classes
     * @throws      \InvalidArgumentException If video or admin provided in the
     * constructor is empty or if module id or class order is empty or less
     * than or equal to zero
     */
    public function updateModule(Questionnaire $questionnaire, int $newIdModule, int $newClassOrder) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateModuleId($newIdModule);
        $this->validateClassOrder($newClassOrder);
        $this->validateQuestionnaire($questionnaire);
        
        // Sets class_order = 0 temporary to avoid constraint error
        $this->withQuery("
            UPDATE  questionnaires
            SET     class_order = 0
            WHERE   id_module = ? AND class_order = ?
        ");
        $this->runQueryWithArguments(
            $questionnaire->getModuleId(), 
            $questionnaire->getClassOrder()
        );
                            
        // Moves class to new module
        $this->withQuery("
            UPDATE  questionnaires
            SET     id_module = ?
            WHERE   id_module = ? AND class_order = 0
        ");
        $this->runQueryWithArguments(
            $newIdModule, 
            $questionnaire->getModule()->getId()
        );
                            
        // Sets class order
        $this->withQuery("
            UPDATE  questionnaires
            SET     class_order = ?
            WHERE   id_module = ? AND class_order = 0
        ");
        $this->runQueryWithArguments(
            $newClassOrder,
            $newIdModule
        );
        
        return $this->parseUpdateResponseQuery($questionnaire);
    }
}