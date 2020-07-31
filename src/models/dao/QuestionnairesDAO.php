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
     * Gets the answer from a quest.
     *
     * @param       int $id_module Module id that the class belongs to
     * @param       int $class_order Class order inside the module that it 
     * belongs to
     *
     * @return      int Correct answer
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function getAnswer(int $id_module, int $class_order) : int
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Invalid module id");
            
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Invalid class order");
        
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
            $response = $sql->fetch()['answer'];
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
     * @throws      \InvalidArgumentException If any argument is invalid
     * 
     * @Override
     */
    public function getAllFromModule(int $id_module) : array
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Invalid module id");
        
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
     * Marks a class as watched by a student.
     *
     * @param       int $id_student Student id
     * @param       int $id_module Module id
     * @param       int $class_order Class order
     *
     * @throws      \InvalidArgumentException If any argument is invalid
     *
     * @Override
     */
    public function markAsWatched(int $id_student, int $id_module, int $class_order) : void
    {
        $this->markAsWatched($id_student, $id_module, $class_order, 
            new ClassTypeEnum(ClassTypeEnum::QUESTIONNAIRE));
    }
}