<?php
namespace models;

use core\Model;


/**
 * Responsible for managing courses.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class Courses extends Model
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $id_user;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates courses manager.
     *
     * @apiNote     It will connect to the database when it is instantiated
     */
    public function __construct($id_user)
    {
        parent::__construct();
        $this->id_user = $id_user;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets all courses that current student has.
     * 
     * @return      array Courses that the student is enrolled
     */
    public function getMyCourses()
    {
        $response = array();

        $sql = $this->db->query("
            SELECT 
                *,
                (
                    select count(*) 
                    from classes 
                    where classes.id_course = student_course.id_course
                ) as totalClasses,
                (
                    select count(*) 
                    from modules 
                    where modules.id_course = student_course.id_course
                ) as totalModules
            FROM student_course 
            LEFT JOIN courses ON courses.id = student_course.id_course
            WHERE id_student = $this->id_user
        ");
        
        if ($sql->rowCount() > 0) {
            $historic = new Historic();
            
            foreach ($sql->fetchAll(\PDO::FETCH_ASSOC) as $key => $course) {
                $response[$key] = $course;
                $response[$key]['totalWatchedClasses'] = $historic->getWatchedClasses($this->id_user, $course['id_course']);
            }
        }
        
        return $response;
    }
    
    /**
     * Gets total of courses that a student is enrolled.
     * 
     * @return      int Total of courses that the student is enrolled
     */
    public function countCourses()
    {
        $sql = $this->db->query("
            SELECT COUNT(*) as count
            FROM student_course 
            WHERE id_student = $this->id_user
        ");
        
        return $sql->fetch()['count'];
    }
    
    /**
     * Gets informations about a course.
     *
     * @param       int $id_course Course id
     *
     * @return      array Informations about a course
     */
    public function getCourse($id_course)
    {
        if (empty($id_course) || $id_course <= 0) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT * 
            FROM courses 
            WHERE id = ?
        ");
        $sql->execute(array($id_course));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch();
            
            $modules = new Modules();
            $response['modules'] = $modules->getModules($id_course);
        }
        
        return $response;
    }
    
    /**
     * Checks if current student is enrolled in a course.
     * 
     * @param       int $id_course Course id
     * 
     * @return      boolean If current student is enrolled in a course
     */
    public function isEnrolled($id_course)
    {
        $sql = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM student_course 
            WHERE id_course = ? AND id_student = $this->id_user
        ");
        $sql->execute(array($id_course));
        
        return $sql->fetch()['count'] > 0;
        
    }
}