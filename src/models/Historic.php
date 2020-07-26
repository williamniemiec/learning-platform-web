<?php
namespace models;

use core\Model;


/**
 * Responsible for managing 'student_historic' table.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Historic extends Model
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'student_historic' table manager.
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
     * Gets total watched classes by a student in a course.
     * 
     * @param       int $id_student Student id
     * @param       int $id_course Course id
     * 
     * @return      int Watched classes
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function countWatchedClasses(int $id_student, int $id_course) : int
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Invalid student id");
        
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Invalid course id");
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  COUNT(*) AS watchedClasses
            FROM    student_historic NATURAL JOIN course_modules
            WHERE   id_student = ? AND id_course = ?
        ");
        
        // Executes query
        $sql->execute(array($id_student, $id_course));
        
        return $sql->fetch()['watchedClasses'];
    }

    /**
     * Removes all student history.
     * 
     * @param       int $id_student Student id
     * 
     * @return      bool If historic was sucessfully removed
     * 
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function clear(int $id_student) : bool
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Invalid student id");
        
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM student_historic
            WHERE id_student = ?
        ");

        // Executes query
        $sql->execute(array($id_student))->rowCount() > 0;
    }
    
    /**
     * Gets total classes that a student watched in the last 7 days.
     * 
     * @param       int $id_student
     * 
     * @return      array Total classes that a student watched in the last 7
     * days. The returned array has the following keys:
     * <ul>
     *  <li><b>date</b>: Date in the following format: YYYY-MM-DD</li>
     *  <li><b>total_classes_watched</b>: Total classes watched by the student
     *  on this date</li>
     * </ul>
     * 
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function getWeeklyHistory(int $id_student) : array
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Invalid student id");
        
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
        $sql->execute(array($id_student));
        
        return ($sql && $sql->rowCount() > 0) ?
            $sql->fetchAll(\PDO::FETCH_ASSOC) : array();
    }
}