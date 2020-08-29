<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Course;
use models\enum\CourseOrderByEnum;
use models\enum\OrderDirectionEnum;
use models\util\IllegalAccessException;
use models\Admin;


/**
 * Responsible for managing 'courses' table.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class CoursesDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $db;
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
        $this->db = $db->getConnection();
        $this->admin = $admin;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
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
     * @throws      \InvalidArgumentException If course id is empty, less than
     * or equal to zero
     */
    public function countClasses(int $id_course) : array
    {
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Course id cannot be empty ".
                "or less than or equal to zero");
       
        // Query construction
        $sql = $this->db->prepare("
            SELECT  SUM(total) AS total_classes, 
                    SUM(length) as total_length
            FROM    (SELECT      COUNT(*) AS total, 
                                 CASE
								    WHEN id_module > 0 THEN 5 
								    ELSE 0
								 END AS length
                     FROM        questionnaires NATURAL JOIN course_modules
                     WHERE       id_course = ?
                     UNION ALL
                     SELECT      COUNT(*) AS total, length
                     FROM        videos NATURAL JOIN course_modules
                     WHERE       id_course = ?) AS tmp
        ");
        
        // Executes query
        $sql->execute(array($id_course, $id_course));
        
        return $sql->fetch();
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

        $sql = $this->db->prepare($query);
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
     * @param       int $id_course Course id to be deleted
     *
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update courses
     * @throws      \InvalidArgumentException If course id is empty, less than
     * or equal to zero or if admin id provided in the constructor is empty
     */
    public function delete(int $id_course) : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Course id cannot be empty ".
                "or less than or equal to zero");
            
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM courses
            WHERE id_course = ?
        ");
        
        // Executes query
        $sql->execute(array($id_course));
        
        return !empty($sql) && $sql->rowCount() > 0;
    }
    
    /**
     * Gets informations about a course.
     *
     * @param       int $id_course Course id
     *
     * @return      Course Informations about a course
     * 
     * @throws      \InvalidArgumentException If course id or admin id provided
     * in the constructor is empty, less than or equal to zero
     */
    public function get(int $id_course) : Course
    {
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Course id cannot be empty ".
                "or less than or equal to zero");
        
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
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
        
        if (empty($course))
            throw new \InvalidArgumentException("Course cannot be empty");
            
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
        $sql = $this->db->prepare($sql);
        $sql->execute($data_values);
        
        return !empty($sql) && $sql->rowCount() > 0;
    }
    
    /**
     * Edits a course.
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
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");

        if (empty($course))
            throw new \InvalidArgumentException("Course cannot be empty");
        
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
        $sql = $this->db->prepare($sql);
        $sql->execute($data_values);
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Adds a new module in a course.
     * 
     * @param       int $id_course Course id
     * @param       int $id_module Module id
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
    public function addModule(int $id_course, int $id_module, int $order) : bool
    {   
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Course id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($order) || $order <= 0)
            throw new \InvalidArgumentException("Order cannot be empty or less ".
                "than or equal to zero");
  
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO course_modules
            (id_course, id_module, module_order)
            VALUES (?, ?, ?)
        ");
        
        // Executes query
        $sql->execute(array($id_course, $id_module, $order));
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Removes a module from a course.
     * 
     * @param       int $id_course Course id
     * @param       int $id_module Module id
     * 
     * @return      bool If module has been successfully removed from the course
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update courses
     * @throws      \InvalidArgumentException If course id, module id is empty,
     * less than or equal to zero or if admin id provided in the 
     * constructor is empty
     */
    public function deleteModuleFromCourse(int $id_course, int $id_module) : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Course id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
            
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM course_modules
            WHERE id_course = ? AND id_module = ?
        ");
        
        // Executes query
        $sql->execute(array($id_course, $id_module));
        
        return !empty($sql) && $sql->rowCount() > 0;
    }
    
    /**
     * Removes all modules from a course.
     * 
     * @param       int $id_course Course id
     * 
     * @return      bool If modules have been successfully removed
     */
    public function deleteAllModules(int $id_course) : bool
    {
        $sql = $this->db->query("
            DELETE FROM course_modules
            WHERE id_course = ".$id_course
        );
        
        return !empty($sql) && $sql->rowCount() > 0;
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
        if (empty($id_bundle) || $id_bundle <= 0)
            throw new \InvalidArgumentException("Bundle id cannot be empty ".
                "or less than or equal to zero");
            
        $response = array();

        // Query construction
        $sql = $this->db->prepare("
            SELECT      id_course, name, logo, description,
                        COUNT(id_student) AS total_students
            FROM        courses
                        NATURAL LEFT JOIN bundle_courses
            WHERE       id_bundle = ?
            GROUP BY    id_course, name, logo, description
        ");
            
        // Executes query
        $sql->execute(array($id_bundle));
        
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
    
    /**
     * Gets course logo.
     *
     * @param       int $id_course Course id
     *
     * @return      string Course logo filename or empty string if course does
     * not have a logo
     *
     * @throws      \InvalidArgumentException If course id is empty, less than
     * or equal to zero
     */
    public function getImage(int $id_course) : string
    {
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Course id cannot be empty ".
                "or less than or equal to zero");
            
        $response = "";
        
        $sql = $this->db->prepare("
            SELECT  logo
            FROM    courses
            WHERE   id_course = ".$id_course
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