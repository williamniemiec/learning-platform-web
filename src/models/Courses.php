<?php
namespace models;

use core\Model;


/**
 *
 */
class Courses extends Model
{
    //-----------------------------------------------------------------------
    //        Attributes
    //-----------------------------------------------------------------------
    private $id_user;
    
    
    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    public function __construct($id_user)
    {
        parent::__construct();
        $this->id_user = $id_user;
    }
    
    
    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
    public function getMyCourses()
    {
        $response = array();

        $sql = $this->db->query("
            SELECT 
                *,
                (select count(*) from classes where classes.id_course = student_course.id_course) as totalClasses
            FROM student_course 
            LEFT JOIN courses ON courses.id = student_course.id_course
            WHERE id_student = $this->id_user
        ");
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetchAll();
        }
        
        return $response;
    }
    
    public function countCourses()
    {
        $sql = $this->db->query("SELECT COUNT(*) as count FROM student_course WHERE id_student = $this->id_user");
        return $sql->fetch()['count'];
    }
    
    public function getCourse($id_course)
    {
        if (empty($id_course) || $id_course <= 0) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("SELECT * FROM courses WHERE id = ?");
        $sql->execute(array($id_course));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch();
            
            $modules = new Modules();
            $response['modules'] = $modules->getModules($id_course);
        }
        
        return $response;
    }
    
    public function isEnrolled($id_course)
    {
        $sql = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM student_course 
            WHERE 
                id_course = ? AND 
                id_student = $this->id_user
        ");
        $sql->execute(array($id_course));
        
        return $sql->fetch()['count'] > 0;
        
    }
}