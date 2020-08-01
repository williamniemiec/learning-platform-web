<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Questionnaire;
use models\util\IllegalAccessException;


/**
 * Responsible for managing 'questionnaires' table.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class QuestionnairesDAO
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
     * Creates 'questionnaires' table manager.
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
    public function get(int $id_module, int $class_order) : Questionnaire
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
            FROM    questionnaires 
            WHERE   id_module = ? AND class_order = ?
        ");
        
        // Executes query
        $sql->execute(array($id_module, $class_order));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $class = $sql->fetch();
            
            $response = new Questionnaire(
                $class['id_module'],
                $class['class_order'],
                $class['question'],
                $class['q1'],
                $class['q2'],
                $class['q3'],
                $class['q4'],
                $class['answer']
            ); 
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
     * @throws      \InvalidArgumentException If questionnaire is empty or if 
     * admin id provided in the constructor is empty, less than or equal to 
     * zero
     */
    public function add(Questionnaire $questionnaire) : bool
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->getAuthorization()->getLevel() != 0 &&
            $this->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($questionnaire))
            throw new \InvalidArgumentException("Questionnaire cannot be empty");
        
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO questionnaires 
            (id_module, class_order, op1, op2, op3, op4, answer) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Executes query
        $sql->execute(array(
            $questionnaire->getModuleId(), 
            $questionnaire->getClassOrder(), 
            $questionnaire->getQuestion(), 
            $questionnaire->getQ1(), 
            $questionnaire->getQ2(),
            $questionnaire->getQ3(),
            $questionnaire->getQ4(),
            $questionnaire->getAnswer()
        ));
        
        return $sql->rowCount() > 0;
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
     * @return      boolean If class has been successfully edited
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update classes
     * @throws      \InvalidArgumentException If questionnaire is empty or if 
     * admin id provided in the constructor is empty, less than or equal to 
     * zero
     */
    public function update(Questionnaire $questionnaire)
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->getAuthorization()->getLevel() != 0 &&
            $this->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($questionnaire))
            throw new \InvalidArgumentException("Questionnaire cannot be empty");
        
        // Query construction
        $sql = $this->db->prepare("
            UPDATE questionnaires 
            (question, op1, op2, op3, op4, answer)
            VALUES (?, ?, ?, ?, ?, ?)
            WHERE id_module = ? AND class_order = ?
        ");
        
        // Executes query
        $sql->execute(array(
            $questionnaire->getModuleId(),
            $questionnaire->getClassOrder(),
            $questionnaire->getQuestion(),
            $questionnaire->getQ1(),
            $questionnaire->getQ2(),
            $questionnaire->getQ3(),
            $questionnaire->getQ4(),
            $questionnaire->getAnswer()
        ));
            
        return $sql->rowCount() > 0;
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
     * @throws      \InvalidArgumentException If module id, class order or admin
     * id provided in the constructor is empty, less than or equal to zero
     */
    public function delete(int $id_module, int $class_order) : bool
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
            
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Class order cannot be empty ".
                "or less than or equal to zero");
            
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM questionnaires
            WHERE id_module = ? AND class_order = ?
        ");
        
        // Executes query
        $sql->execute(array($id_module, $class_order));
        
        return $sql->rowCount() > 0;
    }
}