<?php
declare (strict_types=1);

namespace panel\dao;


use panel\repositories\Database;
use panel\domain\Course;
use panel\domain\Admin;
use panel\domain\Action;
use panel\domain\enum\CourseOrderByEnum;
use panel\domain\enum\OrderDirectionEnum;
use panel\util\IllegalAccessException;


/**
 * Responsible for managing 'courses' table.
 */
class CoursesDAO extends DAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $admin;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates courses manager.
     *
     * @param       Database $db Database
     * @param       Admin $admin [Optional] Admin logged in
     */
    public function __construct(Database $db, Admin $admin = null)
    {
        parent::__construct($db);
        $this->admin = $admin;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets total classes from a course along with its duration (in minutes).
     *
     * @param        int $idCourse Course id
     *
     * @return       array Total course classes and total duration. The 
     * returned array has the following keys:
     * <ul>
     *  <li><b>total_classes</b>: Total course classes</li>
     *  <li><b>total_length</b>: Total course duration (in minutes)</li>
     * </ul>
     * 
     * @throws      \InvalidArgumentException If course id is empty, less than
     * or equal to zero
     */
    public function countClasses(int $idCourse) : array
    {
        $this->validateCourseId($idCourse);
        $this->withQuery("
            SELECT  SUM(total) AS total_classes, 
                    SUM(length) as total_length
            FROM    (SELECT      COUNT(*) AS total, 
                                 CASE
								    WHEN id_module > 0 THEN SUM(5) 
								    ELSE 0
								 END AS length
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
            throw new \InvalidArgumentException("Course id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }
    
    /**
     * Gets information about all registered courses.
     * 
     * @param       string $name [Optional] Course name
     * @param       int $limit [Optional] Maximum courses returned
     * @param       int $offset [Optional] Ignores first results from the return    
     * @param       CourseOrderByEnum [Optional] $orderBy Ordering criteria 
     * @param       OrderDirectionEnum [Optional] $orderType Order that the 
     * elements will be returned. Default is ascending.
     * 
     * @return      array Information about all registered courses along with
     * total number of students who have the course or empty array if there are
     * no registered courses. Each position of the returned array has the 
     * following keys:
     * <ul>
     *  <li><b>course</b>: Course information</li>
     *  <li><b>total_students</b>: Total of students who have the course</li>
     * </ul>
     */
    public function getAll(
        string $name = '', 
        int $limit = -1, 
        int $offset = -1,
        CourseOrderByEnum $orderBy = null, 
        OrderDirectionEnum $orderType = null
    ) : array
    {
        $type = $orderType;

        if (empty($type)) {
            $type = new OrderDirectionEnum(OrderDirectionEnum::ASCENDING);
        }
        
        $this->withQuery($this->buildGetAllQuery($name, $orderBy, $type, $limit, $offset));
        $this->runQueryWithArguments($this->buildGetAllQueryArguments($name));

        return $this->parseGetAllResponseQuery();
    }

    private function buildGetAllQuery($name, $orderBy, $orderType, $limit, $offset)
    {
        $query = "
            SELECT      id_course, name, logo, description, 
                        COUNT(id_student) AS sales
            FROM		courses 
                        NATURAL LEFT JOIN bundle_courses 
            			LEFT JOIN purchases USING (id_bundle)
			GROUP BY    id_course, name, logo, description
        ";
        
        if (!empty($name)) {
            $query .= " HAVING name LIKE ?";
        }
        
        if (!empty($orderBy)) {
            $query .= " ORDER BY ".$orderBy->get()." ".$orderType->get();
        }
        
        if ($limit > 0) {
            if ($offset > 0) {
                $query .= " LIMIT ".$offset.",".$limit;
            }
            else {
                $query .= " LIMIT ".$limit;
            }
        }

        return $query;
    }

    private function buildGetAllQueryArguments($name)
    {
        $bindParams = array();

        if (!empty($name)) {
            $bindParams[] = $name.'%';
        }

        return $bindParams;
    }

    private function parseGetAllResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $courses = array();
        $i = 0;
            
        foreach ($this->getAllResponseQuery() as $course) {
            $courses[$i] = new Course(
                (int) $course['id_course'],
                $course['name'],
                $course['logo'],
                $course['description']
            );
            $total = $this->countClasses((int) $course['id_course']);
            $courses[$i]->setTotalClasses((int) $total['total_classes']);
            $courses[$i]->setTotalLength((int) $total['total_length']);
            $courses[$i]->setTotalStudents((int) $course['sales']);
            $i++;
        }

        return $courses;
    }
    
    /**
     * Deletes a course.
     * 
     * @param       int $idCourse Course id to be deleted
     *
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update courses
     * @throws      \InvalidArgumentException If course id is empty, less than
     * or equal to zero or if admin id provided in the constructor is empty
     */
    public function delete(int $idCourse) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateCourseId($idCourse);
        $this->withQuery("
            DELETE FROM courses
            WHERE id_course = ?
        ");
        $this->runQueryWithArguments($idCourse);
        
        return $this->parseDeleteResponseQuery($idCourse);
    }

    private function parseDeleteResponseQuery($courseId)
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->deleteCourse($courseId);
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);
        
        return true;
    }
    
    /**
     * Gets information about a course.
     *
     * @param       int $idCourse Course id
     *
     * @return      Course Information about a course
     * 
     * @throws      \InvalidArgumentException If course id or admin id provided
     * in the constructor is empty, less than or equal to zero
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
     * Adds a new course.
     * 
     * @param       Course $course Course to be added
     * 
     * @return      bool If course has been successfully added
     * 
     * @throws      IllegalAccessException If current admin does not have 
     * authorization to add courses
     * @throws      \InvalidArgumentException If course is empty or admin 
     * provided in the constructor is empty
     */
    public function new(Course $course) : bool
    {
        $this->validateLoggedAdmin();  
        $this->validateAuthorization(0, 1);
        $this->validateCourse($course);
        $this->withQuery($this->buildNewQuery($course));
        $this->runQueryWithArguments($this->buildNewQueryArguments($course));

        return $this->parseNewResponseQuery();
    }

    private function validateCourse($course)
    {
        if (empty($course)) {
            throw new \InvalidArgumentException("Course cannot be empty");
        }
    }

    private function buildNewQuery($course)
    {
        $fields = array("name");
        $values = array("?");
        
        if (!empty($course->getDescription())) {
            $fields[] = "description";
            $values[] = "?";
        }

        if (!empty($course->getLogo())) {
            $values[] = "?";
        }
        
        return "INSERT INTO courses 
                (".implode(",", $fields).") 
                VALUES (".implode(",", $values).")";
    }

    private function buildNewQueryArguments($course)
    {
        $bindParams = array();
        $bindParams[] = $course->getName();
        
        if (!empty($course->getDescription())) {
            $bindParams[] = $course->getDescription();
        }

        if (!empty($course->getLogo())) {
            $bindParams[] = $course->getLogo();
        }

        return $bindParams;
    }

    private function parseNewResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->addCourse($this->db->lastInsertId());
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);
        
        return true;
    }
    
    /**
     * Updates a course.
     *
     * @param       Course $course Course to be updated
     * 
     * @return      bool If course has been successfully edited
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update courses
     * @throws      \InvalidArgumentException If course or admin provided in 
     * the constructor is empty
     */
    public function update(Course $course) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateCourse($course);
        $this->withQuery($this->buildUpdateQuery($course));
        $this->runQueryWithArguments($this->buildUpdateQueryArguments($course));
        
        return $this->parseUpdateResponseQuery($course->getId());
    }

    private function buildUpdateQuery($course)
    {
        $fields = array("name = ?");
        
        if (!empty($course->getDescription())) {
            $fields[] = "description = ?";
        }

        if (!empty($course->getLogo())) {
            $fields[] = "logo = ?";
        };

        return "UPDATE  courses 
                SET     ".implode(",", $fields)."  
                WHERE   id_course = ?";
    }

    private function buildUpdateQueryArguments($course)
    {
        $bindParams = array($course->getName());
        
        if (!empty($course->getDescription())) {
            $bindParams[] = $course->getDescription();
        }
        
        if (!empty($course->getLogo())) {
            $bindParams[] = $course->getLogo();
        }

        $bindParams[] = $course->getId();

        return $bindParams;
    }

    private function parseUpdateResponseQuery($courseId)
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->updateCourse($courseId);
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);
        
        return true;
    }
    
    /**
     * Adds a new module in a course.
     * 
     * @param       int idCourse Course id
     * @param       int idModule Module id
     * @param       int $order Module order in the course
     * 
     * @return      bool If module has been successfully added
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization update courses
     * @throws      \InvalidArgumentException If course id, module id, order is
     * empty, less than or equal to zero or if admin id provided in the 
     * constructor is empty
     */
    public function addModule(int $idCourse, int $idModule, int $order) : bool
    {   
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateCourseId($idCourse);
        $this->validateModuleId($idModule);
        $this->validateOrder($order);
        $this->withQuery("
            INSERT INTO course_modules
            (id_course, id_module, module_order)
            VALUES (?, ?, ?)
        ");
        $this->runQueryWithArguments($idCourse, $idModule, $order);
        
        return $this->parseUpdateResponseQuery($idCourse);
    }

    private function validateModuleId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Module id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }

    private function validateOrder($order)
    {
        if (empty($order) || $order <= 0) {
            throw new \InvalidArgumentException("Order cannot be empty or ".
                                                "less than or equal to zero");
        }
    }
    
    /**
     * Removes a module from a course.
     * 
     * @param       int $idCourse Course id
     * @param       int $idModule Module id
     * 
     * @return      bool If module has been successfully removed from the course
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update courses
     * @throws      \InvalidArgumentException If course id, module id is empty,
     * less than or equal to zero or if admin id provided in the 
     * constructor is empty
     */
    public function deleteModuleFromCourse(int $idCourse, int $idModule) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateCourseId($idCourse);
        $this->validateModuleId($idModule);
        $this->withQuery("
            DELETE FROM course_modules
            WHERE id_course = ? AND id_module = ?
        ");
        $this->runQueryWithArguments($idCourse, $idModule);

        return $this->parseUpdateResponseQuery($idCourse);
    }
    
    /**
     * Removes all modules from a course.
     * 
     * @param       int $idCourse Course id
     * 
     * @return      bool If modules have been successfully removed
     * 
     * @throws      \InvalidArgumentException If course id is empty, less than
     * or equal to zero
     */
    public function deleteAllModules(int $idCourse) : bool
    {
        $this->validateCourseId($idCourse);
        $this->withQuery("
            DELETE FROM course_modules
            WHERE id_course = ".$idCourse
        );
        $this->runQueryWithoutArguments();
        
        return $this->parseUpdateResponseQuery($idCourse);
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
            SELECT      id_course, name, logo, description,
                        COUNT(id_student) AS total_students
            FROM        courses
                        NATURAL LEFT JOIN bundle_courses
                        LEFT JOIN purchases USING (id_bundle)
            WHERE       id_bundle = ?
            GROUP BY    id_course, name, logo, description
        ");
        $this->runQueryWithArguments($idBundle);
        
        return $this->parseGetAllFromBundleResponseQuery();
    }

    private function validateBundleId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Bundle id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }

    private function parseGetAllFromBundleResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $courses = array();
        $i = 0;
            
        foreach ($this->getAllResponseQuery() as $course) {
            $courses[$i] = new Course(
                (int)$course['id_course'],
                $course['name'],
                $course['logo'],
                $course['description']
            );
            $courses[$i]->setTotalStudents((int) $course['total_students']);
            $i++;
        }

        return $courses;
    }
    
    /**
     * Gets course logo.
     *
     * @param       int idCourse Course id
     *
     * @return      string Course logo filename or empty string if course does
     * not have a logo
     *
     * @throws      \InvalidArgumentException If course id is empty, less than
     * or equal to zero
     */
    public function getImage(int $idCourse) : string
    {
        $this->validateCourseId($idCourse);
        $this->withQuery("
            SELECT  logo
            FROM    courses
            WHERE   id_course = ".$idCourse
        );
        
        return $this->parseGetImageResponseQuery();
    }

    private function parseGetImageResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return "";
        }

        $image = $this->getResponseQuery()['logo'];
        
        return empty($image) ? "" : $image;
    }
    
    /**
     * Gets total of courses.
     *
     * @return      int Total of courses
     */
    public function count() : int
    {
        $this->withQuery("
            SELECT  COUNT(*) AS total
            FROM    courses
        ");
        $this->runQueryWithoutArguments();

        return ((int) $this->getResponseQuery()['total']);
    }
}