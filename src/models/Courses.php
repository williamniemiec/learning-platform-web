<?php
namespace models;

use core\Model;
use models\obj;
use models\obj\Course;


/**
 * Responsible for managing courses table.
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
     * Creates courses table manager.
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
    public function getMyCourses($name = null)
    {
        $response = array();
        
        
//         $sql = $this->db->query("
//             SELECT  *
//             FROM    courses
//             WHERE   id_course IN (SELECT id_course
//                                   FROM bundle_courses
//                                   WHERE id_bundle IN (SELECT  id_bundle
//                                                       FROM    purchases
//                                                       WHERE   id_student = ".$this->id_student."))
//         ");

        $query = "
            SELECT  id_course, name, logo, description, 
                    CASE 
                    	WHEN time_watched IS NULL THEN 0
                    	ELSE SUM(time_watched)
                    END AS time_watched, 
                    CASE 
                    	WHEN total_length IS NULL THEN 0
                    	ELSE total_length
                    END AS time_total, 
                    COUNT(id_module) as total_modules
            FROM    courses NATURAL LEFT JOIN course_modules
            NATURAL LEFT JOIN courses_total_length
            		NATURAL LEFT JOIN (
            			SELECT id_module, SUM(length) AS time_watched
            			FROM student_historic_watched_length
            			WHERE id_student = ?
            			GROUP BY id_module
            			) as tmp
            WHERE   id_course IN (SELECT id_course
                               FROM bundle_courses
                               WHERE id_bundle IN (SELECT  id_bundle
                                                   FROM    purchases
                                                   WHERE   id_student = ?))
            GROUP BY    id_course, name, logo, description
        ";
        
        if (!empty($name)) {
            $query .= " HAVING      name LIKE ?";
            $sql = $this->db->prepare($query);
            $sql->execute(array($name));
        }
        else {
            $sql = $this->db->query($sql);
        }
        
        if ($sql->rowCount() > 0) {
            $courses = $sql->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($courses as $course) {
                // Total classes
//                 $sql = $this->db->query("
//                     SELECT  SUM(total) AS totalClasses FROM (
//                         SELECT  id_module, SUM(total) FROM
//                         (SELECT      id_module, count(*) AS total
//                          FROM        videos
//                          GROUP BY    id_module
//                          UNION
//                          SELECT      id_module, count(*) AS total
//                          FROM        questionnaires
//                          GROUP BY    id_module) AS classes
//                         GROUP BY    id_module
//                         HAVING      id_module IN (SELECT id_module
//                                                   FROM course_modules
//                                                   WHERE id_course = ".$course['id_course'].")
//                         ) AS t
//                 ");
//                 $course['totalClasses'] = $sql->fetch()['totalClasses'];
                $course['totalClasses'] = $this->countClasses($course['id_course']);
//                 Total modules
//                 $sql = $this->db->query("
//                     SELECT  COUNT(*) AS totalModules
//                     FROM    course_modules
//                     WHERE   id_course = ".$course['id_course']."
//                 ");
//                 $course['totalModules'] = $sql->fetch()['totalModules'];
                
                // Gets total watched classes
                $sql = $this->db->query("
                    SELECT  COUNT(*) as totalWatchedClasses
                    FROM    student_historic
                    WHERE   id_student = ".$this->id_user." AND
                            id_module IN (SELECT    id_module
                                          FROM      course_modules
                                          WHERE     id_course = ".$course['id_course'].")
                ");
                
                $result = $sql->fetch();
                $course['totalWatchedClasses'] = $result['totalWatchedClasses'];
            }
        }
        
//         if ($sql->rowCount() > 0) {
//             $historic = new Historic();
            
//             foreach ($sql->fetchAll(\PDO::FETCH_ASSOC) as $key => $course) {
//                 $response[$key] = $course;
//                 $response[$key]['totalWatchedClasses'] = $historic->getWatchedClasses($this->id_user, $course['id_course']);
//             }
//         }
        
        return $response;
    }
    
    /**
     * Gets total of classes FROM a course.
     *
     * @param        int $id_course Course id
     *
     * @return       int Total course classes
     */
    public function countClasses($id_course)
    {
        if (empty($id_course) || $id_course <= 0)
            return 0;
            
            $sql = $this->db->prepare("
                SELECT  SUM(total) AS total
                FROM    (SELECT      COUNT(*) AS total
                         FROM        questionnaires
                         WHERE       id_module  IN (SELECT    id_module
                                                    FROM      course_modules
                                                    WHERE     id_course = ?)
                         UNION ALL
                         SELECT      COUNT(*) AS total
                         FROM        videos
                         WHERE       id_module  IN (SELECT    id_module
                                                    FROM      course_modules
                                                    WHERE     id_course = ?)) AS tmp
            ");
            
            return $sql->fetch()['total'];
    }
    
    public static function getFromBundle($id_bundle)
    {
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT  *
            FROM    courses
            WHERE   id_course IN (SELECT    id_course
                                  FROM      bundle_courses
                                  WHERE     id_bundle = ?)        
        ");
        
        $sql->execute(array($id_bundle));
        
        if ($sql->rowCount() > 0) {
            $courses = $sql->fetchAll();
            
            foreach ($courses as $course) {
                $response[] = new Course(
                    $course['id_course'],
                    $course['name'],
                    $course['logo'],
                    $course['description']
                );
            }
        }
        
        return $response;
    }
    
    
    
    
    
    
    
    /**
     * Gets all courses that current student has.
     * 
     * @return      array Courses that the student is enrolled
     */
//     public function getMyCourses()
//     {
//         $response = array();

//         $sql = $this->db->query("
//             SELECT 
//                 *,
//                 (
//                     select count(*) 
//                     from classes 
//                     where classes.id_course = student_course.id_course
//                 ) as totalClasses,
//                 (
//                     select count(*) 
//                     from modules 
//                     where modules.id_course = student_course.id_course
//                 ) as totalModules
//             FROM student_course 
//             LEFT JOIN courses ON courses.id = student_course.id_course
//             WHERE id_student = $this->id_user
//         ");
        
//         if ($sql->rowCount() > 0) {
//             $historic = new Historic();
            
//             foreach ($sql->fetchAll(\PDO::FETCH_ASSOC) as $key => $course) {
//                 $response[$key] = $course;
//                 $response[$key]['totalWatchedClasses'] = $historic->getWatchedClasses($this->id_user, $course['id_course']);
//             }
//         }
        
//         return $response;
//     }
    
    /**
     * Gets total of courses that a student is enrolled.
     * 
     * @return      int Total of courses that the student is enrolled
     */
//     public function countCourses()
//     {
//         $sql = $this->db->query("
//             SELECT  COUNT(*) AS count
//             FROM    courses
//             WHERE   id_course IN (SELECT    id_course
//                                   FROM      bundle_courses
//                                   WHERE     id_bundle IN (SELECT  id_bundle
//                                                           FROM    purchases
//                                                           WHERE   id_student = ".$this->id_student."))
//         ");
        
//         return $sql->fetch()['count'];
//     }
    
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
            SELECT  *
            FROM    courses
            WHERE   id_course = ?
        ");
        
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
            SELECT  COUNT(*) AS count
            FROM    courses
            WHERE   id_course = ? AND
                    id_course IN (SELECT id_course
                                  FROM bundle_courses
                                  WHERE id_bundle IN (SELECT  id_bundle
                                                      FROM    purchases
                                                      WHERE   id_student = ".$this->id_student."))
        ");
        
        $sql->execute(array($id_course));
        
        return $sql->fetch()['count'] > 0; 
    }
}