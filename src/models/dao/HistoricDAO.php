<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;


/**
 * Responsible for managing 'student_historic' table.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class HistoricDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $db;
    private $id_student;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'student_historic' table manager.
     *
     * @param       Database $db Database
     * 
     * @throws      \InvalidArgumentException If student id is empty or less 
     * than or equal to zero
     */
    public function __construct(Database $db, int $id_student)
    {
        if (empty($this->id_student) || $this->id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty or ". 
                "less than or equal to zero");
        
        $this->id_student = $id_student;
        $this->db = $db->getConnection();
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets total watched classes by a student in a course.
     * 
     * @param       int $id_course Course id
     * 
     * @return      int Watched classes
     * 
     * @throws      \InvalidArgumentException If course id is empty or less 
     * than or equal to zero
     */
    public function countWatchedClasses(int $id_course) : int
    {
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Course id cannot be empty or ". 
                "less than or equal to zero");
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  COUNT(*) AS watchedClasses
            FROM    student_historic NATURAL JOIN course_modules
            WHERE   id_student = ? AND id_course = ?
        ");
        
        // Executes query
        $sql->execute(array($this->id_student, $id_course));
        
        return $sql->fetch()['watchedClasses'];
    }

    /**
     * Removes all student history.
     * 
     * @return      bool If historic was sucessfully removed
     */
    public function clear() : bool
    {
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM student_historic
            WHERE id_student = ?
        ");

        // Executes query
        $sql->execute(array($this->id_student))->rowCount() > 0;
    }
    
    /**
     * Gets total classes that a student watched in the last 7 days.
     * 
     * @return      array Total classes that a student watched in the last 7
     * days. The returned array has the following keys:
     * <ul>
     *  <li><b>date</b>: Date in the following format: YYYY-MM-DD</li>
     *  <li><b>total_classes_watched</b>: Total classes watched by the student
     *  on this date</li>
     * </ul>
     */
    public function getWeeklyHistory() : array
    {
        // Query construction
        $sql = $this->db->prepare("
            SELECT      date, SUM(*) AS total_classes_watched
            FROM        student_historic
            WHERE       id_student = ?
            GROUP BY    date
            ORDER BY    date DESC
            LIMIT 7
        ");
        
        // Executes query
        $sql->execute(array($this->id_student));
        
        return ($sql && $sql->rowCount() > 0) ?
            $sql->fetchAll(\PDO::FETCH_ASSOC) : array();
    }
}