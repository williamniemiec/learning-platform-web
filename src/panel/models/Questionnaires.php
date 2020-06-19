<?php
namespace models;

use core\Model;


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
    public function getQuestFromClass($id_class)
    {
        if (empty($id_class) || $id_class <= 0) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT * 
            FROM questionnaires 
            WHERE id_class = ?
        ");
        $sql->execute(array($id_class));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch(\PDO::FETCH_ASSOC);
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
    public function getAnswer($id_question)
    {
        if (empty($id_question) || $id_question <= 0) { return -1; }
        
        $response = -1;
        
        $sql = $this->db->prepare("
            SELECT answer 
            FROM questionnaires
            WHERE id = ?
        ");
        $sql->execute(array($id_question));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch(\PDO::FETCH_ASSOC)['answer'];
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
            (id_class, op1, op2, op3, op4, answer) 
            VALUES (?,?,?,?,?,?)
        ");
        $sql->execute(array($id_class, $question, $op1, $op2, $op3, $op4, $answer));
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Gets a quest class.
     * 
     * @param       int $id_quest Class id
     * 
     * @return      array Quest class with the given id
     */
    public function get($id_quest)
    {
        if (empty($id_quest)) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT * 
            FROM questionnaires 
            WHERE id = ?
        ");
        $sql->execute(array($id_quest));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch(\PDO::FETCH_ASSOC);
        }
        
        return $response;
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
    public function edit($id_quest, $question, $op1, $op2, $op3, $op4, $answer)
    {
        if (empty($id_quest) || empty($question) ||
            empty($op1) || empty($op2) || empty($op3) ||
            empty($op4) || empty($answer)) {
                echo false;
            }
            
            $sql = $this->db->prepare("
                UPDATE questionnaires 
                SET question = ?, op1 = ?, op2 = ?, op3 = ?, op4 = ?, answer = ?
                WHERE id = ?
            ");
            $sql->execute(array($question, $op1, $op2, $op3, $op4, $answer, $id_quest));
            
            return $sql->rowCount() > 0;
    }
}