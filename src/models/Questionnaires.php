<?php
namespace models;

use core\Model;
use models\obj\Questionnaire;


/**
 * Responsible for managing questionnaires table.
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
     * Creates questionnaires table manager.
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
     * Gets questionnaire class
     *
     * @param       int $id_class Class id
     *
     * @return      array questions from this class
     */
    public function get($id_module, $class_order)
    {
//         if (empty($id_module) || $id_module <= 0) { return array(); }
        
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
//         if (empty($id_question) || $id_question <= 0) { return -1; }
        
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
    
    public function getFromModule($id_module)
    {
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT  *
            FROM    questionnaires
            WHERE   id_module = ?   
        ");
        
        $sql->execute(array($id_module));
        
        if ($sql->rowCount() > 0) {
            $classes = $sql->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($classes as $class) {
                $response[] = new Questionnaire(
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
        }
        
        return $response;
    }
    
    /**
     * Marks a class as watched by a student.
     *
     */
    public function markAsWatched($id_student, $id_module, $class_order)
    {
        if (empty($id_student) || $id_student <= 0)
            return;
            
        if (empty($id_module) || $id_module <= 0)
            return;
                
        if ($class_order <= 0)
            return;
                    
        $sql = $this->db->prepare("
            INSERT INTO student_historic
            (id_student, id_module, class_order, 1, date)
            VALUES (?, ?, ?, NOW())
        ");
                    
        $sql->execute(array($id_student, $id_module, $class_order));
    }
}