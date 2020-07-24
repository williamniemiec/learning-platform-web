<?php
namespace models;

use core\Model;


/**
 * Responsible for managing student_historic table.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class Historic extends Model
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates student_historic table manager.
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
     * Gets watched classes by a student in a course.
     * 
     * @param       int $id_student Student id
     * @param       int $id_course Course id
     * 
     * @return      array Watched classes
     */
    public function getWatchedClasses($id_student, $id_course)
    {
        $sql = $this->db->prepare("
            SELECT  COUNT(*) AS watchedClasses
            FROM    student_historic
            WHERE   id_student = ? AND 
                    id_module IN (SELECT    id_module
                                  FROM      course_modules
                                  WHERE     id_course = ?)
        ");
        
        $sql->execute(array($id_student, $id_course));
        
        return $sql->fetch()['watchedClasses'];
    }

    public function clear($id_student)
    {
        $sql = $this->db->prepare("
            DELETE FROM student_historic
            WHERE id_student = ?
        ");

        $sql->execute(array($id_student, $id_course))->rowCount() > 0;
    }
}