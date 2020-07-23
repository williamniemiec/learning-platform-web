<?php
namespace models;

use core\Model;
use models\obj\Questionnaire;


/**
 * Responsible for manag IN g classes.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @s IN ce		1.0
 */
abstract class Classes extends Model
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates classes manager.
     *
     * @apiNote     It will connect to the database when it is  IN stantiated
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    
    
    
    
//     /**
//      * Gets course id FROM a class.
//      *
//      * @param        IN t $id_class
//      *
//      * @return      number Course id of -1 if class id is  IN valid
//      */
//     public function getCourseId($id_class)
//     {
//         if (empty($id_class) || $id_class <= 0) { return -1; }

//         $sql = $this->db->prepare("
//             SELECT id_course 
//             FROM classes 
//             WHERE id = ?
//         ");
//         $sql->execute(array($id_class));
        
//         return $sql->fetch()['id_course'];
//     }
    
    /**
     * Gets  IN formation about the first class FROM the first module FROM a
     * course.
     *
     * @param        IN t $id_course Course id
     *
     * @return      array Informations about the first class FROM the first
     * module FROM a course
     */
    public function getFirstClassFromFirstModule($id_course)
    {
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT      id_module, class_order, class_type FROM (
                SELECT    id_module, class_order, 'questionnaire' AS class_type
                FROM      questionnaires
                WHERE     questionnaires.class_order = 1 AND questionnaires.id_module  IN  (SELECT  id_module 
                                                                                            FROM    course_modules 
                                                                                            WHERE   id_course = ?)
                union
                SELECT    id_module, class_order, 'video' AS class_type
                FROM      videos
                WHERE     videos.class_order = 1 AND videos.id_module  IN (SELECT  id_module 
                                                                           FROM    course_modules 
                                                                           WHERE   id_course = ?)
            ) AS tmp join course_modules USING (id_module)
            WHERE       id_course = ?
            ORDER BY    module_order
        ");
        $sql->execute(array($id_course));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch();
            
            if ($response['class_type'] == 'video') {
                $videos = new Videos();
                $response['video'] = $videos->get($response['id_module'], 1);
            } else {
                $quests = new Questionnaires();
                $response['questionnaire'] = $quests->get($response['id_module'], 1);
            }
        }
        
        return $response;
    }
    
    /**
     * Gets  IN formations about a class AND if it was watched by a student.
     * 
     * @param        IN t $id_class Class id
     * @param        IN t $id_student Student id
     * @return      array Class  IN formation
     */
//     public function getClass($id_class, $id_student)
//     {
//         if (empty($id_class) || $id_class <= 0)
//             return -1;
        
//         $response = array();
        
//         $sql = $this->db->prepare("
//             SELECT 
//                 *,
//                 (
//                     SELECT COUNT(*)
//                     FROM historic 
//                     WHERE historic.id_class = classes.id AND historic.id_student = $id_student
//                 ) AS watched
//             FROM classes 
//             WHERE id = ?
//         ");
//         $sql->execute(array($id_class));
        
//         if ($sql->rowCount() > 0) {
//             $response = $sql->fetch();
            
//             if ($response['type'] == 'video') {
//                 $videos = new Videos();
//                 $response['video'] = $videos->getVideoFromClass($id_class);
//             } else {
//                 $quests = new Questionnaires();
//                 $response['quest'] = $quests->getQuestFromClass($id_class);
//             }
//         }
        
//         return $response; 
        
//     }
    
    /**
     * Checks whether there is a class with the given id.
     *
     * @param        IN t $id_class Class id
     *
     * @return      boolean If there is a class with the given id
     */
//     public function exist($id_class)
//     {
//         if (empty($id_class) || $id_class <= 0) { return false; }
        
//         $sql = $this->db->prepare("SELECT COUNT(*) AS count FROM classes WHERE id = ?");
//         $sql->execute(array($id_class));
        
//         return $sql->fetch()['count'] > 0;
//     }
    
    /**
     * Marks a class AS watched by a student.
     * 
     */
    public abstract function markAsWatched($id_student, $id_module, $class_order);
    
    /**
     * Removes watched class markup FROM a class.
     * 
     */
    public function removeWatched($id_student, $id_module, $class_order)
    {
        if (empty($id_student) || $id_student <= 0)
            return;
            
        if (empty($id_module) || $id_module <= 0)
            return;
            
        if ($class_order <= 0)
            return;
        
        $sql = $this->db->prepare("
            DELETE FROM student_historic 
            WHERE id_student = ? AND id_module = ? AND class_order = ?
        ");
        $sql->execute(array($id_student, $id_module, $class_order));
    }
    
    /**
     * Gets all classes FROM a course.
     *
     * @param        IN t $id_course Course id
     *
     * @return      array Informations about all classes from a course
     */
//     public function getClassesInCourse($id_course)
//     {
//         if (empty($id_course) || $id_course <= 0) { return; }
        
//         $response = array();
        
//         $sql = $this->db->prepare("
//             SELECT id 
//             FROM classes 
//             WHERE id_course = ?
//         ");
//         $sql->execute(array($id_course));
        
//         if ($sql->rowCount() > 0) {
//             foreach ($sql->fetchAll() AS $class) {
//                 $response[] = $class['id'];
//             }
//         }
        
//         return $response;
//     }
    
    /**
     * Checks whether a class is marked AS watched.
     * 
     * @param        int t $id_class Class id
     * @param        IN t $id_student Student id
     * @return      boolean If class is marked AS watched
     */
//     private function alreadyMarkedAsWatched($id_class, $id_student)
//     {
//         if (empty($id_class) || $id_class <= 0)     { return true; }
//         if (empty($id_student) || $id_student <= 0) { return true; }
        
//         $sql = $this->db->prepare("
//             SELECT COUNT(*) AS count 
//             FROM historic 
//             WHERE id_student = ? AND id_class = ?
//         ");
//         $sql->execute(array($id_student,$id_class));
        
//         return $sql->fetch()["count"] > 0;
//     }
}