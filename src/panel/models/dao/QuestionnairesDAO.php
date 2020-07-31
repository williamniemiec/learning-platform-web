<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Questionnaire;


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
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'questionnaires' table manager.
     *
     * @param       mixed $db Database
     */
    public function __construct(Database $db)
    {
        $this->db = $db->getConnection();
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
     * @return      Questionnaire Questionnaire class or null if class does not exist
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function get(int $id_module, int $class_order) : Questionnaire
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Invalid module id");
        
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Invalid class order");
        
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
            $class = $sql->fetch(\PDO::FETCH_ASSOC);
            
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
     * Creates a new quest class.
     * 
     * @param       Questionnaire $questionnaire Class to be added
     * 
     * @return      bool If the class was successfully added
     * 
     * @throws      \InvalidArgumentException If questionnaire is empty
     */
    public function add(Questionnaire $questionnaire) : bool
    {
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
     * Edits a quest class.
     * 
     * @param       int $id_quest Class id
     * @param       string $question New question title
     * @param       string $op1 New option 1
     * @param       string $op2 New option 2
     * @param       string $op3 New option 3
     * @param       string $op4 New option 4
     * @param       string $answer New answer
     * 
     * @return      boolean If class was sucessfully edited
     * 
     * @throws      \InvalidArgumentException If questionnaire is empty
     */
    public function edit(Questionnaire $questionnaire)
    {
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
     * @return      bool If class was successfully removed 
     * 
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function delete(int $id_module, int $class_order) : bool
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Invalid module id");
            
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Invalid class order");
            
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