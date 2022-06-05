<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Questionnaire;
use models\enum\ClassTypeEnum;


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
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'questionnaires' table manager.
     *
     * @param       Database $db Database
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
     * @return      Questionnaire Questionnaire class or null if class does not
     * exist
     * 
     * @throws      \InvalidArgumentException If module id or class order is 
     * empty or less than or equal to zero
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
                (int)$class['id_module'],
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
     * Gets the answer from a quest.
     *
     * @param       int $id_module Module id that the class belongs to
     * @param       int $class_order Class order inside the module that it 
     * belongs to
     *
     * @return      int Correct answer
     * 
     * @throws      \InvalidArgumentException If module id or class order is 
     * empty or less than or equal to zero
     */
    public function getAnswer(int $id_module, int $class_order) : int
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Class order cannot be empty ".
                "or less than or equal to zero");
        
        $response = -1;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  answer 
            FROM    questionnaires 
            WHERE   id_module = ? AND class_order = ?
        ");
        
        // Executes query
        $sql->execute(array($id_module, $class_order));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $response = (int)$sql->fetch()['answer'];
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
            FROM    questionnaires
            WHERE   id_module = ?   
        ");
        
        // Executes query
        $sql->execute(array($id_module));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $classes = $sql->fetchAll();
            
            foreach ($classes as $class) {
                $response[] = new Questionnaire(
                    (int)$class['id_module'], 
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
     * {@inheritdoc}
     * @Override
     */
    public function totalLength() : int
    {
        return $this->db->query("
            SELECT  SUM(length) AS total_length
            FROM    (SELECT 5 AS length
                     FROM   questionnaires) AS tmp
        ")->fetch()['total_length'];
    }
    
    /**
     * {@inheritdoc}
     * @Override
     */
    public function wasWatched(int $id_student, int $id_module, int $class_order) : bool
    {
        $sql = $this->db->prepare("
            SELECT  COUNT(*) AS was_watched
            FROM    student_historic
            WHERE   class_type = 1 AND
                    id_student = ? AND
                    id_module = ? AND
                    class_order = ?
        ");
        
        $sql->execute(array($id_student, $id_module, $class_order));
        
        return $sql->fetch()['was_watched'] > 0;
    }
    
    /**
     * Marks a class as watched by a student.
     *
     * @param       int $id_student Student id
     * @param       int $id_module Module id
     * @param       int $class_order Class order
     *
     * @return      bool If class has been successfully added to student history
     *
     * @throws      \InvalidArgumentException If any argument is invalid
     *
     * @Override
     */
    public function markAsWatched(int $id_student, int $id_module, int $class_order) : bool
    {
        return $this->_markAsWatched($id_student, $id_module, $class_order, 
            new ClassTypeEnum(ClassTypeEnum::QUESTIONNAIRE));
    }
}