<?php
namespace models;

use core\Model;
use models\obj\Questionnaire;


/**
 * Responsible for managing question classes.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class Questionnaires extends Model
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates question classes manager.
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
     * Gets all questions from a class.
     * 
     * @param       int $id_class Class id
     * 
     * @return      array questions from this class
     */
    public function get($id_module, $class_order)
    {
        $response = NULL;
        
        $sql = $this->db->prepare("
            SELECT  *
            FROM    questionnaires
            WHERE   id_module = ? AND
                    class_order = ?
        ");
        
        $sql->execute(array($id_module, $class_order));
        
        if ($sql->rowCount() > 0) {
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
     * Gets the answer from a quest.
     * 
     * @param       int $id_question Quest id
     * 
     * @return      int Correct answer
     */
    public function getAnswer($id_module, $class_order)
    {
        $response = -1;
        
        $sql = $this->db->prepare("
            SELECT  answer
            FROM    questionnaires
            WHERE   id_module = ? AND
                    class_order = ?
        ");
        $sql->execute(array($id_module, $class_order));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch()['answer'];
        }
        
        return $response;
    }
    
    /**
     * Creates a new quest class.
     * 
     * @param       int $id_class Class id
     * @param       string $question Quest title
     * @param       string $op1 Option 1
     * @param       string $op2 Option 2
     * @param       string $op3 Option 3
     * @param       string $op4 Option 4
     * @param       int $answer [1;4] Correct option
     * 
     * @return      boolean If the class was successfully added
     */
    public function add($id_class, $question, $op1, $op2, $op3, $op4, $answer)
    {
        if (empty($question) || empty($answer) ||
            empty($op1) || empty($op2) || empty($op3) || empty($op4)) 
            return false;
        
        $sql = $this->db->prepare("
            INSERT INTO questionnaires 
            (id_module, class_order, op1, op2, op3, op4, answer) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $sql->execute(array($id_class, $question, $op1, $op2, $op3, $op4, $answer));
        
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
     */
    public function edit($id_module, $class_order, $question, $op1, $op2, $op3, $op4, $answer)
    {
        if (empty($id_module) || empty($class_order) || empty($question) ||
            empty($op1) || empty($op2) || empty($op3) ||
            empty($op4) || empty($answer)) {
                echo false;
            }
            
            $sql = $this->db->prepare("
                UPDATE questionnaires 
                SET question = ?, op1 = ?, op2 = ?, op3 = ?, op4 = ?, answer = ?
                WHERE id_module = ? AND class_order = ?
            ");
            $sql->execute(array($question, $op1, $op2, $op3, $op4, $answer, $id_module, $class_order));
            
            return $sql->rowCount() > 0;
    }
    
    public function delete($id_module, $class_order)
    {
        $sql = $this->db->prepare("
            DELETE FROM questionnaires
            WHERE id_module = ? AND class_order = ?
        ");
        
        $sql->execute(array($id_module, $class_order));
        
        return $sql->rowCount() > 0;
    }
}