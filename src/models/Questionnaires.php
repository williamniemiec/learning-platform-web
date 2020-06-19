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
            $response = $sql->fetch()['answer'];
        }
        
        return $response;
    }
}