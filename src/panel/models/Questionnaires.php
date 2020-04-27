<?php
namespace models;

use core\Model;


/**
 *
 */
class Questionnaires extends Model
{
    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    public function __construct()
    {
        parent::__construct();
    }
    
    
    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
    public function getQuestFromClass($id_class)
    {
        if (empty($id_class) || $id_class <= 0) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("SELECT * FROM questionnaires WHERE id_class = ?");
        $sql->execute(array($id_class));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch(\PDO::FETCH_ASSOC);
        }
        
        return $response;
    }
    
    public function getAnswer($id_question)
    {
        if (empty($id_question) || $id_question <= 0) { return -1; }
        
        $response = -1;
        
        $sql = $this->db->prepare("SELECT answer FROM questionnaires WHERE id = ?");
        $sql->execute(array($id_question));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch(\PDO::FETCH_ASSOC)['answer'];
        }
        
        return $response;
    }
    
    public function add($id_class, $question, $op1, $op2, $op3, $op4, $answer)
    {
        if (empty($question) || empty($op1) || empty($op2) || empty($op3) || empty($op4) || empty($answer)) {
            return false;
        }
        
        $sql = $this->db->prepare("INSERT INTO questionnaires (id_class, op1, op2, op3, op4, answer) VALUES (?,?,?,?,?,?)");
        $sql->execute(array($id_class, $question, $op1, $op2, $op3, $op4, $answer));
        
        return $sql->rowCount() > 0;
    }
}