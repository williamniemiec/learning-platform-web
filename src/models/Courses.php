<?php
declare (strict_types=1);

namespace models;


use core\Model;
use models\obj;
use models\obj\Course;


/**
 * Responsible for managing 'courses' table.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
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
     * Creates 'courses' table manager.
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
     * Gets courses that a student has.
     * 
     * @param       int $id_student Student id
     * @param       string $name [Optional] Course name (searches for this name)
     * 
     * @return      array Courses that a student has along with the progress of
     * each student on these course. The returned array has the following keys:
     * <ul>
     *  <li><b>course</b>: Courses that the student has</li>
     *  <li><b>total_length_watched</b>: Total time of classes watched by the 
     *  student in this course</li>
     *  <li><b>total_classes_watched</b>: Total classes watched by the student in
     *  this course</li>
     * </ul>
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function getMyCourses(int $id_student, string $name = '') : array
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Invalid id_student");
        
        $response = array();
        
        // Query construction
        $query = "
            SELECT      id_course, name, logo, description, 
                        CASE 
                        	WHEN total_length_watched IS NULL THEN 0
                        	ELSE SUM(total_length_watched)
                        END AS total_length_watched, 
                        CASE 
                        	WHEN total_length IS NULL THEN 0
                        	ELSE total_length
                        END AS total_length,
                        CASE
                            WHEN total_classes_watched IS NULL THEN 0
                            ELSE SUM(total_classes_watched) 
                        END AS total_classes_watched
            FROM        courses 
                        NATURAL LEFT JOIN course_modules
                        NATURAL LEFT JOIN courses_total_length
                		NATURAL LEFT JOIN (
                			SELECT       id_module, 
                                         SUM(length) AS total_length_watched,
                                         COUNT(id_module) AS total_classes_watched
                			FROM         student_historic_watched_length
                			WHERE        id_student = ?
                			GROUP BY     id_module
            			) AS tmp
            WHERE       id_course IN (SELECT    id_course
                                      FROM      bundle_courses
                                                NATURAL JOIN purchases
                                      WHERE     id_student = ?)
            GROUP BY    id_course, name, logo, description
        ";
        
        // Filters courses with the given name (if provided)
        if (!empty($name)) {
            $query .= " HAVING      name LIKE ?";
            $sql = $this->db->prepare($query);
            $sql->execute(array($id_student, $name));
        }
        else {
            $sql = $this->db->prepare($query);
            $sql->execute(array($id_student));
        }
        
        // Parses results
        if ($sql->rowCount() > 0) {
            $i = 0;
            
            foreach ($sql->fetchAll(\PDO::FETCH_ASSOC) as $course) {
                $response[$i]['course'] = new Course(
                    $course['id_course'], 
                    $course['name'],
                    $course['logo'],
                    $course['description']
                );
                
                $response[$i]['course']->setTotalLength($course['total_length']);
                $response[$i]['total_classes_watched'] = $course['total_classes_watched'];
                $response[$i]['total_length_watched'] = $course['total_length_watched'];
                
                $i++;
            }
        }
        
        return $response;
    }
    
    /**
     * Gets total classes from a course along with its duration (in minutes).
     *
     * @param        int $id_course Course id
     *
     * @return       array Total course classes and total duration. The 
     * returned array has the following keys:
     * <ul>
     *  <li><b>total_classes</b>: Total course classes</li>
     *  <li><b>total_length</b>: Total course duration (in minutes)</li>
     * </ul>
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function countClasses(int $id_course) : array
    {
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Invalid id_student");
       
        // Query construction
        $sql = $this->db->prepare("
            SELECT  SUM(total) AS total_classes, 
                    SUM(length) as total_length
            FROM    (SELECT      COUNT(*) AS total, 5 AS length
                     FROM        questionnaires
                                 NATURAL JOIN course_modules
                     WHERE       id_course = ?
                     UNION ALL
                     SELECT      COUNT(*) AS total, length
                     FROM        videos
                                 NATURAL JOIN course_modules
                     WHERE       id_course = ?) AS tmp
        ");
        
        // Executes query
        $sql->execute(array($id_course, $id_course));
        
        return $sql->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Gets courses that belongs to a bundle.
     * 
     * @param       int $id_bundle Bundle id
     * 
     * @return      Course[] Courses belonging to this bundle
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function getFromBundle(int $id_bundle) : array
    {
        if (empty($id_bundle) || $id_bundle <= 0)
            throw new \InvalidArgumentException("Invalid bundle id");
        
        $response = array();
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    courses
            WHERE   id_course IN (SELECT    id_course
                                  FROM      bundle_courses
                                  WHERE     id_bundle = ?)        
        ");
        
        // Executes query
        $sql->execute(array($id_bundle));
        
        // Parses results
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
     * Gets informations about a course.
     *
     * @param       int $id_course Course id
     *
     * @return      Course Informations about a course
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function getCourse(int $id_course) : Course
    {
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Invalid course id");
        
        $response = null;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    courses
            WHERE   id_course = ?
        ");
        
        // Executes query
        $sql->execute(array($id_course));
        
        // Parses results
        if ($sql->rowCount() > 0) {
            $course = $sql->fetch();
            
            $response = new Course(
                $course['id_course'],
                $course['name'],
                $course['logo'],
                $course['description']
            );
        }
        
        
        return $response;
    }
    
    /**
     * Checks whether a student is enrolled in a course.
     * 
     * @param       int $id_course Course id
     * @param       int $id_student Student id
     * 
     * @return      bool If current student is enrolled in a course
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function isEnrolled(int $id_course, int $id_student) : bool
    {
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Invalid course id");
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  COUNT(*) AS count
            FROM    courses
            WHERE   id_course = ? AND
                    id_course IN (SELECT    id_course
                                  FROM      bundle_courses
                                            NATURAL JOIN purchases
                                  WHERE     id_student = ?)
        ");
        
        // Executes query
        $sql->execute(array($id_course, $id_student));
        
        return $sql->fetch()['count'] > 0; 
    }
}