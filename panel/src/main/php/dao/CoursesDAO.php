<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Course;
use domain\Admin;
use domain\Action;
use domain\enum\CourseOrderByEnum;
use domain\enum\OrderDirectionEnum;
use util\IllegalAccessException;


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

        // Query construction
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
        
        // Executes query
        $sql->execute(array($idCourse, $idCourse));
        
        return $sql->fetch();
    }

    private function validateCourseId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Course id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }
    
    /**
     * Gets informations about all registered courses.
     * 
     * @param       string $name [Optional] Course name
     * @param       int $limit [Optional] Maximum courses returned
     * @param       int $offset [Optional] Ignores first results from the return    
     * @param       CourseOrderByEnum [Optional] $orderBy Ordering criteria 
     * @param       OrderDirectionEnum [Optional] $orderType Order that the 
     * elements will be returned. Default is ascending.
     * 
     * @return      array Informations about all registered courses along with
     * total number of students who have the course or empty array if there are
     * no registered courses. Each position of the returned array has the 
     * following keys:
     * <ul>
     *  <li><b>course</b>: Course information</li>
     *  <li><b>total_students</b>: Total of students who have the course</li>
     * </ul>
     */
    public function getAll(string $name = '', int $limit = -1, int $offset = -1,
        CourseOrderByEnum $orderBy = null, OrderDirectionEnum $orderType = null) : array
    {
        $response = array();
        $bindParams = array();
        
        if (empty($orderType))
            $orderType = new OrderDirectionEnum(OrderDirectionEnum::ASCENDING);

        // Query construction
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
            $bindParams[] = $name.'%';
        }
        
        if (!empty($orderBy)) {
            $query .= " ORDER BY ".$orderBy->get()." ".$orderType->get();
        }
        
        // Limits the results (if a limit was given)
        if ($limit > 0) {
            if ($offset > 0)
                $query .= " LIMIT ".$offset.",".$limit;
            else
                $query .= " LIMIT ".$limit;
        }
        
        // Executes query

        $this->withQuery($query);
        $sql->execute($bindParams);
        // Parses results
        if (!empty($sql) && $sql->rowCount() > 0) {
            $i = 0;
            
            foreach($sql->fetchAll() as $course) {
                $response[$i] = new Course(
                    (int)$course['id_course'],
                    $course['name'],
                    $course['logo'],
                    $course['description']
                );
                
                $total = $this->countClasses((int)$course['id_course']);
                $response[$i]->setTotalClasses((int)$total['total_classes']);
                $response[$i]->setTotalLength((int)$total['total_length']);
                $response[$i]->setTotalStudents((int)$course['sales']);
                $i++;
            }
        }

        return $response;
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
        
        $response = false;
            
        // Query construction
        $this->withQuery("
            DELETE FROM courses
            WHERE id_course = ?
        ");
        
        // Executes query
        $sql->execute(array($idCourse));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->deleteCourse($idCourse);
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
    
    /**
     * Gets informations about a course.
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
        
        $response = null;
        
        // Query construction
        $this->withQuery("
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
                (int)$course['id_course'],
                $course['name'],
                $course['logo'],
                $course['description']
            );
        }
        
        return $response;
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
            
        $response = false;
        $data_keys = array();
        $data_values = array();
        $data_sql = array();
        
        // Query construction
        $data_keys[] = "name";
        $data_values[] = $course->getName();
        $data_sql[] = "?";
        
        if (!empty($course->getDescription())) {
            $data_keys[] = "description";
            $data_values[] = $course->getDescription();
            $data_sql[] = "?";
        }
        
        // Parses course logo
        if (!empty($course->getLogo())) {
            // Puts image file name in the query
            $data_values[] = $course->getLogo();
            $data_sql[] = "?";
        }
        
        $sql = "
            INSERT INTO courses 
            (".implode(",",$data_keys).") 
            VALUES (".implode(",", $data_sql).")";
        
        // Executes query
        $this->withQuery($sql);
        $sql->execute($data_values);
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->addCourse($course->getId());
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }

    private function validateCourse($course)
    {
        if (empty($course)) {
            throw new \InvalidArgumentException("Course cannot be empty");
        }
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
    public function edit(Course $course) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateCourse($course);
        
        $response = false;
        $data_values = array();
        $data_sql = array();
        
        // Query construction
        $data_values[] = $course->getName();
        $data_sql[] = "name = ?";
        
        if (!empty($course->getDescription())) {
            $data_values[] = $course->getDescription();
            $data_sql[] = "description = ?";
        }
        
        // Parses course logo
        if (!empty($course->getLogo())) {
            $data_values[] = $course->getLogo();
            $data_sql[] = "logo = ?";
        }

        $data_values[] = $course->getId();
        
        $sql = "
            UPDATE  courses 
            SET     ".implode(",", $data_sql)." 
            WHERE   id_course = ?
        ";
        
        // Executes query
        $this->withQuery($sql);
        $sql->execute($data_values);
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateCourse($course->getId());
            $adminsDAO->newAction($action);
        }
        
        return $response;
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

        $response = false;
        
        // Query construction
        $this->withQuery("
            INSERT INTO course_modules
            (id_course, id_module, module_order)
            VALUES (?, ?, ?)
        ");
        
        // Executes query
        $sql->execute(array($idCourse, $idModule, $order));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateCourse($idCourse);
            $adminsDAO->newAction($action);
        }
        
        return $response;
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

        $response = false;
            
        // Query construction
        $this->withQuery("
            DELETE FROM course_modules
            WHERE id_course = ? AND id_module = ?
        ");
        
        // Executes query
        $sql->execute(array($idCourse, $idModule));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateCourse($idCourse);
            $adminsDAO->newAction($action);
        }
        
        return $response;
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
            
        $response = false;
        
        $sql = $this->db->query("
            DELETE FROM course_modules
            WHERE id_course = ".$idCourse
        );
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateCourse($idCourse);
            $adminsDAO->newAction($action);
        }
        
        return $response;
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
            
        $response = array();

        // Query construction
        $this->withQuery("
            SELECT      id_course, name, logo, description,
                        COUNT(id_student) AS total_students
            FROM        courses
                        NATURAL LEFT JOIN bundle_courses
                        LEFT JOIN purchases USING (id_bundle)
            WHERE       id_bundle = ?
            GROUP BY    id_course, name, logo, description
        ");
            
        // Executes query
        $sql->execute(array($idBundle));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $courses = $sql->fetchAll();
            $i = 0;
            
            foreach ($courses as $course) {
                $response[$i] = new Course(
                    (int)$course['id_course'],
                    $course['name'],
                    $course['logo'],
                    $course['description']
                );
                
                $response[$i]->setTotalStudents((int)$course['total_students']);
                $i++;
            }
        }
        
        return $response;
    }

    private function validateBundleId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Bundle id cannot be empty or ".
                                                "less than or equal to zero");
        }
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
        $response = "";
        
        $this->withQuery("
            SELECT  logo
            FROM    courses
            WHERE   id_course = ".$idCourse
        );

        if ($sql->rowCount() > 0) {
            $response = $sql->fetch()['logo'];
            
            if (empty($response))
                $response = "";
        }
        
        return $response;
    }
    
    /**
     * Gets total of courses.
     *
     * @return      int Total of courses
     */
    public function count() : int
    {
        return (int)$this->db->query("
            SELECT  COUNT(*) AS total
            FROM    courses
        ")->fetch()['total'];
    }
}