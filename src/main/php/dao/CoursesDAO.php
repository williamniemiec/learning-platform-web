<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Course;
use domain\ClassType;


/**
 * Responsible for managing 'courses' table.
 */
class CoursesDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $idUser;
    private $db;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'courses' table manager.
     *
     * @param       Database $db Database
     */
    public function __construct(Database $db)
    {
        $this->db = $db->getConnection();
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets courses that a student has.
     * 
     * @param       int idStudent Student id
     * @param       string $name [Optional] Course name (searches for this name)
     * 
     * @return      array Courses that a student has along with the progress of
     * each student on these course. Each position of the returned array has 
     * the following keys:
     * <ul>
     *  <li><b>course</b>: Courses that the student has</li>
     *  <li><b>total_length_watched</b>: Total time of classes watched by the 
     *  student in this course</li>
     *  <li><b>total_classes_watched</b>: Total classes watched by the student
     *  in this course</li>
     * </ul>
     * 
     * @throws      \InvalidArgumentException If student id is empty or less 
     * than or equal to zero
     */
    public function getMyCourses(int $idStudent, string $name = '') : array
    {
        if (empty($idStudent) || $idStudent <= 0) {
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        }
        
        $response = array();
        
        // Query construction
        $query = "
            SELECT      id_course, name, logo, description, 
                        CASE 
                        	WHEN SUM(total_length_watched) IS NULL THEN 0
                        	ELSE SUM(total_length_watched)
                        END AS total_length_watched, 
                        CASE 
                        	WHEN total_length IS NULL THEN 0
                        	ELSE total_length
                        END AS total_length,
                        CASE
                            WHEN SUM(total_classes_watched) IS NULL THEN 0
                            ELSE SUM(total_classes_watched) 
                        END AS total_classes_watched
            FROM        courses 
                        NATURAL LEFT JOIN course_modules
                        NATURAL LEFT JOIN vw_courses_total_length
                		NATURAL LEFT JOIN (
                			SELECT       id_module, 
                                         SUM(length) AS total_length_watched,
                                         COUNT(id_module) AS total_classes_watched
                			FROM         vw_student_historic_watched_length
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
            $sql->execute(array($idStudent, $idStudent, $name."%"));
        }
        else {
            $sql = $this->db->prepare($query);
            $sql->execute(array($idStudent, $idStudent));
        }
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $i = 0;
            
            foreach ($sql->fetchAll() as $course) {
                $response[$i]['course'] = new Course(
                    (int)$course['id_course'], 
                    $course['name'],
                    $course['logo'],
                    $course['description']
                );
                
                $response[$i]['course']->getModules($this->db);
                $response[$i]['course']->setTotalLength((int) $course['total_length']);
                $response[$i]['course']->getTotalClasses($this->db);
                $response[$i]['total_classes_watched'] = (int) $course['total_classes_watched'];
                $response[$i]['total_length_watched'] = (int) $course['total_length_watched'];

                $i++;
            }
        }
        
        return $response;
    }
    
    /**
     * Gets total classes from a course along with its duration (in minutes).
     *
     * @param        int idCourse Course id
     *
     * @return       array Total course classes and total duration. The 
     * returned array has the following keys:
     * <ul>
     *  <li><b>total_classes</b>: Total course classes</li>
     *  <li><b>total_length</b>: Total course duration (in minutes)</li>
     * </ul>
     * 
     * @throws      \InvalidArgumentException If course id is empty or less
     * than or equal to zero 
     */
    public function countClasses(int $idCourse) : array
    {
        if (empty($idCourse) || $idCourse <= 0) {
            throw new \InvalidArgumentException("Course id cannot be empty ".
                "or less than or equal to zero");
        }
       
        // Query construction
        $sql = $this->db->prepare("
            SELECT  SUM(total) AS total_classes, 
                    SUM(length) as total_length
            FROM    (SELECT      COUNT(*) AS total, SUM(5) AS length
                     FROM        questionnaires NATURAL JOIN course_modules
                     WHERE       id_course = ?
                     UNION ALL
                     SELECT      COUNT(*) AS total, SUM(length) AS length
                     FROM        videos NATURAL JOIN course_modules
                     WHERE       id_course = ?) AS tmp
        ");
        
        // Executes query
        $sql->execute(array($idCourse, $idCourse));
        
        return $sql->fetch();
    }
    
    /**
     * Gets courses that belongs to a bundle.
     * 
     * @param       int $id_bundle Bundle id
     * 
     * @return      Course[] Courses belonging to this bundle
     * 
     * @throws      \InvalidArgumentException If bundle id is empty or less 
     * than or equal to zero
     */
    public function getFromBundle(int $id_bundle) : array
    {
        if (empty($id_bundle) || $id_bundle <= 0) {
            throw new \InvalidArgumentException("Bundle id cannot be empty ".
                "or less than or equal to zero");
        }
        
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
        if ($sql && $sql->rowCount() > 0) {
            $courses = $sql->fetchAll();
            $i = 0;
            
            foreach ($courses as $course) {
                $response[$i] = new Course(
                    (int) $course['id_course'],
                    $course['name'],
                    $course['logo'],
                    $course['description']
                );
                
                $totalClasses = $this->countClasses($course['id_course']);
                $response[$i]->setTotalClasses((int)$totalClasses['total_classes']);
                $response[$i]->setTotalLength((int)$totalClasses['total_length']);
                $i++;
            }
        }
        
        return $response;
    }

    /**
     * Gets information about a course.
     *
     * @param       int id_Course Course id
     *
     * @return      Course Information about a course
     * 
     * @throws      \InvalidArgumentException If bundle id is empty or less 
     * than or equal to zero
     */
    public function get(int $idCourse) : Course
    {
        if (empty($idCourse) || $idCourse <= 0) {
            throw new \InvalidArgumentException("Course id cannot be empty ".
                "or less than or equal to zero");
        }
        
        $response = null;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    courses
            WHERE   id_course = ?
        ");
        
        // Executes query
        $sql->execute(array($idCourse));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $course = $sql->fetch();
            
            $response = new Course(
                (int) $course['id_course'],
                $course['name'],
                $course['logo'],
                $course['description']
            );
        }
        
        return $response;
    }
    
    /**
     * Gets the first class from the first module from a course.
     *
     * @param       int idCourse Course id
     *
     * @return      ClassType First class from the first module from a course or
     * null if there are no registered modules - classes in the course with
     * the given id
     *
     * @throws      \InvalidArgumentException If course id or student id is
     * empty or less than or equal to zero
     */
    public function getFirstClassFromFirstModule(int $idCourse) : ?ClassType
    {
        if (empty($idCourse) || $idCourse <= 0) {
            throw new \InvalidArgumentException("Course id cannot be empty ".
                "or less than or equal to zero");
        }
            
        $response = null;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT      id_module, class_order, class_type FROM (
                SELECT    id_module, class_order, 'questionnaire' AS class_type
                FROM        questionnaires NATURAL JOIN course_modules
                WHERE       class_order = 1 AND id_course = ?
                UNION
                SELECT      id_module, class_order, 'video' AS class_type
                FROM        videos NATURAL JOIN course_modules
                WHERE       class_order = 1 AND id_course = ?
            ) AS tmp JOIN course_modules USING (id_module)
            WHERE       id_course = ?
            ORDER BY    module_order
        ");
            
        // Executes query
        $sql->execute(array($idCourse, $idCourse, $idCourse));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $response = $sql->fetch();
            
            if ($response['class_type'] == 'video') {
                $videosDao = new VideosDAO($this->db);
                
                $response = $videosDao->get($response['id_module'], 1);
            } else {
                $questionnairesDao = new QuestionnairesDAO($this->db);
                
                $response = $questionnairesDao->get((int) $response['id_module'], 1);
            }
        }
            
        return $response;
    }
    
    /**
     * Checks whether a student has a course.
     * 
     * @param       int idCourse Course id
     * @param       int idStudent Student id
     * 
     * @return      bool If current student has the course
     * 
     * @throws      \InvalidArgumentException If bundle id or student id is 
     * empty or less than or equal to zero
     */
    public function hasCourse(int $idCourse, int $idStudent) : bool
    {
        if (empty($idCourse) || $idCourse <= 0) {
            throw new \InvalidArgumentException("Course id cannot be empty ".
                "or less than or equal to zero");
        }
        
        if (empty($idStudent) || $idStudent <= 0) {
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        }
            
        // Query construction
        $sql = $this->db->prepare("
            SELECT  COUNT(*) AS count
            FROM    courses
            WHERE   id_course = ? AND
                    id_course IN (SELECT    id_course
                                  FROM      bundle_courses NATURAL JOIN purchases
                                  WHERE     id_student = ?)
        ");
        
        // Executes query
        $sql->execute(array($idCourse, $idStudent));
        
        return $sql->fetch()['count'] > 0; 
    }
    
    /**
     * Gets total of courses.
     * 
     * @return      int Total of courses
     */
    public function getTotal() : int
    {
        return (int) $this->db->query("
            SELECT  COUNT(*) AS total
            FROM    courses
        ")->fetch()['total'];
    }
}