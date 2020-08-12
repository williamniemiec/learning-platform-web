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
        if (empty($id_student) || $id_student <= 0)
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
        
        return (int)$sql->fetch()['watchedClasses'];
    }

    /**
     * Removes all student history.
     * 
     * @return      bool If historic has been successfully removed
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
     * 
     * Gets all classes classes watched by the student.
     * 
     * @param       int $id_course Course id
     * 
     * @return      array Classes watched by the student. It will return an 
     * array in the following format:
     * <code>$returnedArray[id_module][class_order] = true</code>
     * 
     * @throws      \InvalidArgumentException If course id is empty or less 
     * than or equal to zero
     */
    public function getWatchedClassesFromCourse(int $id_course)
    {
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Course id cannot be empty or ".
                "less than or equal to zero");
        
        $response = array();
            
        // Query construction
        $sql = $this->db->prepare("
            SELECT  id_module, class_order
            FROM    student_historic
            WHERE   id_student = ? AND
                    id_module IN (SELECT    id_module
                                  FROM      course_modules
                                  WHERE     id_course = ?)
        ");
        
        // Executes query
        $sql->execute(array($this->id_student, $id_course));
        
        
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $class) {
                $response[$class['id_module']][$class['class_order']] = true;                
            }
        }
            
        return $response;
    }
    
    /**
     * Gets total classes that a student watched in the last 7 days.
     * 
     * @return      array Total classes that a student watched in the last 7
     * days. Each key of the returned array has the following format: 
     *  <ul>
     *      <li><b>date</b>: Date in the following format: YYYY/MM/DD</li>
     *      <li><b>total_classes_watched</b>: Total classes watched by the 
     *      student on this date</li>
     *  </ul>
     */
    public function getWeeklyHistory() : array
    {
        // Query construction
        $sql = $this->db->prepare("
            SELECT      date, COUNT(*) AS total_classes_watched
            FROM        student_historic
            WHERE       id_student = ? AND 
                        date >= DATE_ADD(CURDATE(), INTERVAL -7 DAY)
            GROUP BY    date
            ORDER BY    date DESC
            LIMIT 7
        ");
        
        $response = array();
        
        // Executes query
        $sql->execute(array($this->id_student));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $i = 0;
            
            foreach ($sql->fetchAll() as $date) {
                //$date = str_replace("-", "", $date['date']);
                $response[$i]['date'] = str_replace("-", "/", $date['date']);
                $response[$i]['total_classes_watched'] = $date['total_classes_watched'];
                $i++;
            }
        }
        
        return $response;;
    }
}