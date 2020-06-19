<?php
namespace models;

use core\Model;


/**
 * Responsible for managing classes.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class Classes extends Model
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates classes manager.
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
     * Gets total of classes from a course.
     *
     * @param       int $id_course Course id
     *
     * @return      int Total course classes
     */
    public function countClasses($id_course)
    {
        if (empty($id_course) || $id_course <= 0) { return 0; }
        
        $sql = $this->db->query("
            SELECT COUNT(*) as count 
            FROM classes 
            WHERE id_course = $id_course
        ");
        
        return $sql->fetch()['count'];
    }
    
    /**
     * Gets informations about all classes from a module.
     *
     * @param       int $id_module Module id
     *
     * @return      array Informations about all classes from the module
     */
    public function getClassesFromModule($id_module)
    {
        if (empty($id_module) || $id_module <= 0) { return array(); }
        
        $response = array();
        $id_student = $_SESSION['s_login'];
        $sql = $this->db->prepare("
            SELECT 
                *,
                (   
                    select count(*) 
                    from historic 
                    where historic.id_class = classes.id and historic.id_student = $id_student
                ) as watched 
            FROM classes 
            WHERE id_module = ? 
            ORDER BY classes.order
        ");
        $sql->execute(array($id_module));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetchAll();
            
            foreach ($response as $key => $value) {
                if ($value['type'] == 'video') {
                    $videos = new Videos();
                    $response[$key]['video'] = $videos->getVideoFromClass($value['id']); 
                } else if ($value['type'] == 'quest') {
                    $quests = new Questionnaires();
                    $response[$key]['quest'] = $quests->getQuestFromClass($value['id']);
                }
            }
        }
        
        return $response;
    }
    
    /**
     * Gets course id from a class.
     *
     * @param       int $id_class
     *
     * @return      number Course id of -1 if class id is invalid
     */
    public function getCourseId($id_class)
    {
        if (empty($id_class) || $id_class <= 0) { return -1; }

        $sql = $this->db->prepare("
            SELECT id_course 
            FROM classes 
            WHERE id = ?
        ");
        $sql->execute(array($id_class));
        
        return $sql->fetch()['id_course'];
    }
    
    /**
     * Gets information about the first class from the first module from a
     * course.
     *
     * @param       int $id_course Course id
     *
     * @return      array Informations about the first class from the first
     * module from a course
     */
    public function getFirstClassFromFirstModule($id_course)
    {
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT * 
            FROM classes 
            WHERE classes.order = 1 AND id_course = ? 
            ORDER BY id_module ASC 
            LIMIT 1
        ");
        $sql->execute(array($id_course));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch();
            
            if ($response['type'] == 'video') {
                $videos = new Videos();
                $response['video'] = $videos->getVideoFromClass($response['id']);
            } else {
                $quests = new Questionnaires();
                $response['quest'] = $quests->getQuestFromClass($response['id']);
            }
        }
        
        return $response;
    }
    
    /**
     * Gets informations about a class and if it was watched by a student.
     * 
     * @param       int $id_class Class id
     * @param       int $id_student Student id
     * @return      array Class information
     */
    public function getClass($id_class, $id_student)
    {
        if (empty($id_class) || $id_class <= 0) { return -1; }
        
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT 
                *,
                (
                    select count(*)
                    from historic 
                    where historic.id_class = classes.id and historic.id_student = $id_student
                ) as watched
            FROM classes 
            WHERE id = ?
        ");
        $sql->execute(array($id_class));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch();
            
            if ($response['type'] == 'video') {
                $videos = new Videos();
                $response['video'] = $videos->getVideoFromClass($id_class);
            } else {
                $quests = new Questionnaires();
                $response['quest'] = $quests->getQuestFromClass($id_class);
            }
        }
        
        return $response; 
        
    }
    
    /**
     * Checks whether there is a class with the given id.
     *
     * @param       int $id_class Class id
     *
     * @return      boolean If there is a class with the given id
     */
    public function exist($id_class)
    {
        if (empty($id_class) || $id_class <= 0) { return false; }
        
        $sql = $this->db->prepare("SELECT COUNT(*) AS count FROM classes WHERE id = ?");
        $sql->execute(array($id_class));
        
        return $sql->fetch()['count'] > 0;
    }
    
    /**
     * Marks a class as watched by a student.
     * 
     * @param       int $id_student Student id
     * @param       int $id_class Class id
     */
    public function markAsWatched($id_student, $id_class)
    {
        if (empty($id_class) || $id_class <= 0)                     { return; }
        if ($this->alreadyMarkedAsWatched($id_student, $id_class))   { return; }
        
        $sql = $this->db->prepare("
            INSERT INTO historic 
            (id_student,id_class,date_watched) 
            VALUES (?,?,NOW())
        ");
        $sql->execute(array($id_student,$id_class));   
    }
    
    /**
     * Removes watched class markup from a class.
     * 
     * @param       int $id_student Student id
     * @param       int $id_class Marked Class id
     */
    public function removeWatched($id_student,$id_class)
    {
        if (empty($id_class) || $id_class <= 0) { return; }
        
        $sql = $this->db->prepare("
            DELETE FROM historic 
            WHERE id_student = ? AND id_class = ?
        ");
        $sql->execute(array($id_student,$id_class));
    }
    
    /**
     * Gets all classes from a course.
     *
     * @param       int $id_course Course id
     *
     * @return      array Informations about all classes from a course
     */
    public function getClassesInCourse($id_course)
    {
        if (empty($id_course) || $id_course <= 0) { return; }
        
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT id 
            FROM classes 
            WHERE id_course = ?
        ");
        $sql->execute(array($id_course));
        
        if ($sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $class) {
                $response[] = $class['id'];
            }
        }
        
        return $response;
    }
    
    /**
     * Checks whether a class is marked as watched.
     * 
     * @param       int $id_class Class id
     * @param       int $id_student Student id
     * @return      boolean If class is marked as watched
     */
    private function alreadyMarkedAsWatched($id_class, $id_student)
    {
        if (empty($id_class) || $id_class <= 0)     { return true; }
        if (empty($id_student) || $id_student <= 0) { return true; }
        
        $sql = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM historic 
            WHERE id_student = ? AND id_class = ?
        ");
        $sql->execute(array($id_student,$id_class));
        
        return $sql->fetch()["count"] > 0;
    }
}