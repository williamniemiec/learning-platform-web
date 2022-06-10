<?php
/**
 * Copyright (c) William Niemiec.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Course;
use domain\ClassType;


/**
 * Responsible for managing 'courses' table.
 */
class CoursesDAO extends DAO
{
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
        parent::__construct($db);
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
        $this->validateStudentId($idStudent);
        $this->withQuery($this->buildGetMyCoursesQuery($name));
        
        if (empty($name)) {
            $this->runQueryWithArguments($idStudent, $idStudent);
        }
        else {
            $this->runQueryWithArguments($idStudent, $idStudent, $name."%");
        }

        return $this->parseGetMyCoursesResponseQuery();
    }

    private function validateStudentId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Student id cannot be empty or".
                                                "less than or equal to zero");
        }
    }

    private function parseGetMyCoursesResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $courses = array();
        $i = 0;
            
        foreach ($this->getAllResponseQuery() as $course) {
            $courses[$i]['course'] = new Course(
                (int) $course['id_course'], 
                $course['name'],
                $course['logo'],
                $course['description']
            );
            $courses[$i]['course']->getModules($this->db);
            $courses[$i]['course']->setTotalLength((int) $course['total_length']);
            $courses[$i]['course']->getTotalClasses($this->db);
            $courses[$i]['total_classes_watched'] = (int) $course['total_classes_watched'];
            $courses[$i]['total_length_watched'] = (int) $course['total_length_watched'];
            $i++;
        }

        return $courses;
    }

    private function buildGetMyCoursesQuery($name)
    {
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
        
        if (!empty($name)) {
            $query .= " HAVING      name LIKE ?";
        }

        return $query;
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
        $this->validateCourseId($idCourse);
        $this->withQuery("
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
        $this->runQueryWithArguments($idCourse, $idCourse);
        
        return $this->getResponseQuery();
    }

    private function validateCourseId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Course id cannot be empty or".
                                                "less than or equal to zero");
        }
    }
    
    /**
     * Gets courses that belongs to a bundle.
     * 
     * @param       int idBundle Bundle id
     * 
     * @return      Course[] Courses belonging to this bundle
     * 
     * @throws      \InvalidArgumentException If bundle id is empty or less 
     * than or equal to zero
     */
    public function getFromBundle(int $idBundle) : array
    {
        $this->validateBundleId($idBundle);
        $this->withQuery("
            SELECT  *
            FROM    courses
            WHERE   id_course IN (SELECT    id_course
                                  FROM      bundle_courses
                                  WHERE     id_bundle = ?)        
        ");
        $this->runQueryWithArguments($idBundle);
        
        return $this->parseGetFromBundleResponseQuery();
    }

    private function validateBundleId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Bundle id cannot be empty or".
                                                "less than or equal to zero");
        }
    }

    private function parseGetFromBundleResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $courses = array();
        $i = 0;
            
        foreach ($this->getAllResponseQuery() as $course) {
            $totalClasses = $this->countClasses($course['id_course']);
            $courses[$i] = new Course(
                (int) $course['id_course'], 
                $course['name'],
                $course['logo'],
                $course['description']
            );
            $courses[$i]->setTotalClasses((int) $totalClasses['total_classes']);
            $courses[$i]->setTotalLength((int) $totalClasses['total_length']);
            $i++;
        }

        return $courses;
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
        $this->validateCourseId($idCourse);
        $this->withQuery("
            SELECT  *
            FROM    courses
            WHERE   id_course = ?
        ");
        $this->runQueryWithArguments($idCourse);
        
        return $this->parseGetResponseQuery();
    }

    private function parseGetResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return null;
        }

        $courseRaw = $this->getResponseQuery();
            
        return new Course(
            (int) $courseRaw['id_course'],
            $courseRaw['name'],
            $courseRaw['logo'],
            $courseRaw['description']
        );
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
        $this->validateCourseId($idCourse);
        $this->withQuery("
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
        $this->runQueryWithArguments($idCourse, $idCourse, $idCourse);
        
        return $this->parseGetClassResponseQuery();
    }

    private function parseGetClassResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return null;
        }

        $class = null;
        $classRaw = $this->getResponseQuery();
            
        if ($classRaw['class_type'] == 'video') {
            $videosDao = new VideosDAO($this->db);
            $class = $videosDao->get($classRaw['id_module'], 1);
        } 
        else {
            $questionnairesDao = new QuestionnairesDAO($this->db);
            $class = $questionnairesDao->get((int) $classRaw['id_module'], 1);
        }

        return $class;
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
        $this->validateCourseId($idCourse);
        $this->validateStudentId($idStudent);
        $this->withQuery("
            SELECT  COUNT(*) AS count
            FROM    courses
            WHERE   id_course = ? AND
                    id_course IN (SELECT    id_course
                                  FROM      bundle_courses NATURAL JOIN purchases
                                  WHERE     id_student = ?)
        ");
        $this->runQueryWithArguments($idCourse, $idStudent);
        
        return ($this->getResponseQuery()['count'] > 0); 
    }
    
    /**
     * Gets total of courses.
     * 
     * @return      int Total of courses
     */
    public function getTotal() : int
    {
        $this->withQuery("
            SELECT  COUNT(*) AS total
            FROM    courses
        ");
        $this->runQueryWithoutArguments();
        
        return ((int) $this->getResponseQuery()['total']);
    }
}