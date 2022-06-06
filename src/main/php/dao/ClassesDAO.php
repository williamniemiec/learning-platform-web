<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\enum\ClassTypeEnum;


/**
 * Responsible for representing classes.
 */
abstract class ClassesDAO extends DAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    protected $db;


    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    protected function __construct(Database $db)
    {
        parent::__construct($db);
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets all classes from a module.
     *
     * @param       int $id_module Module id
     *
     * @return      array Classes that belongs to the module
     *
     * @throws      \InvalidArgumentException If module id or student id is 
     * empty or less than or equal to zero
     */
    public abstract function getAllFromModule(int $id_module) : array;
    
    /**
     * Marks a class as watched by a student.
     * 
     * @param       int idStudent Student id
     * @param       int idModule Module id
     * @param       int classOrder Class order
     * @param       ClassTypeEnum classType Class type
     * 
     * @return      bool If class has been successfully added to student history
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function _MarkAsWatched(int $idStudent, int $idModule, int $classOrder, 
        ClassTypeEnum $classType) : bool
    {
        if (empty($idStudent) || $idStudent <= 0) {
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        }
            
        if (empty($idModule) || $idModule <= 0) {
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
        }
                
        if (empty($classOrder) || $classOrder <= 0) {
            throw new \InvalidArgumentException("Class order cannot be empty ".
                "or less than or equal to zero");
        }
        
        if (empty($classType) || (empty($classType->get()) && $classType->get() != 0)) {
            throw new \InvalidArgumentException("Class type cannot be empty ");
        }
                    
        $classType = $classType->get() == 1 ? "b'1'" : "b'0'";
        
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO student_historic
            (id_student, id_module, class_order, class_type, date)
            VALUES (?, ?, ?, ".$classType.", CURDATE())
        ");

        // Executes query
        $sql->execute(array($idStudent, $idModule, $classOrder));
        
        return !empty($sql) && $sql->rowCount() > 0;
    }
    
    /**
     * Gets total duration (in minutes) of all classes.
     *
     * @return      int Total duration (in minutes)
     */
    public abstract function totalLength() : int;
    
    /**
     * Checks whether a student watched a specific class.
     * 
     * @param       int idStudent Student id
     * @param       int idModule Module id to which the class belongs
     * @param       int classOrder Class order in module
     * 
     * @return      bool If student watched the class or not
     */
    public abstract function wasWatched(int $idStudent, int $idModule, int $classOrder) : bool;
    
    /**
     * Removes watched class markup from a class.
     * 
     * @param       int idStudent Student id
     * @param       int idModule Module id
     * @param       int classOrder Class order
     * 
     * @return      bool If class has been successfully removed from student history
     * 
     * @throws      \InvalidArgumentException If student id, module id or 
     * class order is empty or less than or equal to zero
     */
    public function removeWatched(int $idStudent, int $idModule, int $classOrder) : bool
    {
        if (empty($idStudent) || $idStudent <= 0) {
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        }
            
        if (empty($idModule) || $idModule <= 0) {
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
        }
            
        if (empty($classOrder) || $classOrder <= 0) {
            throw new \InvalidArgumentException("Class order cannot be empty ".
                "or less than or equal to zero");
        }
        
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM student_historic 
            WHERE id_student = ? AND id_module = ? AND class_order = ?
        ");
        
        // Executes query
        $sql->execute(array($idStudent, $idModule, $classOrder));
        
        return !empty($sql) && $sql->rowCount() > 0;
    }
    
    /**
     * Gets total of classes.
     *
     * @param       Database $db Database
     *
     * @return      array Total of classes and length. The returned array has
     * the following keys:
     * <ul>
     *  <li>total_classes</li>
     *  <li>total_length</li>
     * </ul>
     */
    public static function getTotal(Database $db) : array
    {
        return $db->getConnection()->query("
            SELECT  SUM(total) AS total_classes, 
                    SUM(length) AS total_length 
            FROM (
                SELECT  COUNT(*) AS total, SUM(length) as length
                FROM    videos
                UNION
                SELECT  COUNT(*) AS total, SUM(5) AS length
                FROM questionnaires
            ) AS tmp
        ")->fetch();
    }
}