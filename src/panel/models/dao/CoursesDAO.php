<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Course;
use models\enum\OrderDirectionEnum;
use models\enum\CourseOrderByEnum;


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
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates courses manager.
     *
     * @param       mixed $db Database
     */
    public function __construct(Database $db)
    {
        $this->db = $db->getConnection();
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
     * @throws      \InvalidArgumentException If course id is invalid 
     */
    public function countClasses(int $id_course) : array
    {
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Invalid course id");
       
        // Query construction
        $sql = $this->db->prepare("
            SELECT  SUM(total) AS total_classes, 
                    SUM(length) as total_length
            FROM    (SELECT      COUNT(*) AS total, 5 AS length
                     FROM        questionnaires NATURAL JOIN course_modules
                     WHERE       id_course = ?
                     UNION ALL
                     SELECT      COUNT(*) AS total, length
                     FROM        videos NATURAL JOIN course_modules
                     WHERE       id_course = ?) AS tmp
        ");
        
        // Executes query
        $sql->execute(array($id_course, $id_course));
        
        return $sql->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Searches for courses that belongs to a bundle with a specific name.
     * 
     * @param       string $bundleName Bundle name
     * 
     * @return      Course[] Courses with the specified name or empty array if
     * there are no courses that belongs to the specified bundle
     * 
     * @throws      \InvalidArgumentException If bundle name is empty 
     */
    public function getCoursesByBundle(string $bundleName) : array 
    {
        if (empty($bundleName))
            throw new \InvalidArgumentException("Bundle name cannot be empty");
        
        $response = array();
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT      id_course, courses.name, logo, courses.description,
                        COUNT(id_student) AS tot_students
            FROM		courses 
                        NATURAL JOIN bundle_courses
            			NATURAL JOIN purchases
                        JOIN bundles USING (id_bundle)
            WHERE       bundles.name LIKE ?
            GROUP BY    id_course, name, logo, description
        ");
        
        // Executes query
        $sql->execute(array($bundleName));
        
        // Parses results
        if ($sql->rowCount() > 0) {
            foreach ($sql->fetchAll(\PDO::FETCH_ASSOC) as $course) {
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
     * Gets informations about all registered courses.
     * 
     * @param       string $name [Optional] Course name
     * @param       int $limit [Optional] Maximum courses returned
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
    public function getAll(string $name = '', int $limit = -1, CourseOrderByEnum $orderBy = null, 
        OrderDirectionEnum $orderType = null) : array
    {
        $response = array();
        
        if (empty($orderType))
            $orderType = new OrderDirectionEnum(OrderDirectionEnum::ASCENDING);

        // Query construction
        $query = "
            SELECT      id_course, name, logo, description, 
                        COUNT(id_student) AS total_students
            FROM		courses 
                        NATURAL JOIN bundle_courses 
            			NATURAL JOIN purchases
			GROUP BY    id_course, name, logo, description
        ";
        
        if (!empty($orderBy)) {
            $query .= "ORDER BY ".$orderBy->get()." ".$orderType->get();
        }
        
        // Executes query
        if (!empty($name)) {
            $query .= " HAVING name LIKE ?";
            $sql = $this->db->prepare($query);
            $sql->execute(array($name.'%'));
        }
        else {
            $sql = $this->db->query($query);
        }
        
        // Limits the results (if a limit was given)
        if ($limit > 0)
            $query .= " LIMIT ".$limit;
        
        // Parses results
        if ($sql->rowCount() > 0) {
            foreach($sql->fetchAll(\PDO::FETCH_ASSOC) as $course) {
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
     * Deletes a course.
     * 
     * @param       int $id_course Course id to be deleted
     * 
     * @throws      \InvalidArgumentException If course id is invalid 
     */
    public function delete(int $id_course) : bool
    {
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Invalid course id");
        
        // Delete image, if there is one
        $imageName = $this->getImage($id_course);
        
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM courses
            WHERE id_course = ?
        ");
        
        // Executes query
        $sql->execute(array($id_course));
        
        // Removes course logo
        if (!empty($imageName)) {
            unlink("../assets/images/logos/".$imageName);
        }
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Gets course banner.
     * 
     * @param       int $id_course Course id
     * 
     * @return      string Course logo filename or empty string if course does
     * not have a logo 
     * 
     * @throws      \InvalidArgumentException If course id is invalid 
     */
    private function getImage($id_course)
    {
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Invalid course id");
        
        $response = "";
        
        $sql = $this->db->prepare("
            SELECT logo 
            FROM courses 
            WHERE id_course = ?
        ");
        $sql->execute(array($id_course));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch()['logo'];
            
            if (empty($response))
                $response = "";
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
     * @throws      \InvalidArgumentException If course id is invalid 
     */
    public function get(int $id_course) : Course
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
        if ($sql && $sql->rowCount() > 0) {
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
     * Adds a new course.
     * 
     * @param       string $name Course name
     * @param       string $description [Optional] Course description
     * @param       string $logo [Optional] Course logo (obtained from $POST) 
     * 
     * @return      bool If course was successfully added
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function add(string $name, string $description = '', string $logo = array()) : bool
    {
        if (empty($name))
            throw new \InvalidArgumentException("Course name cannot be empty");
        
        $data_keys = array();
        $data_values = array();
        $data_sql = array();
        
        // Query construction
        $data_keys[] = "name";
        $data_values[] = $name;
        $data_sql[] = "?";
        
        if (!empty($description)) {
            $data_keys[] = "description";
            $data_values[] = $description;
            $data_sql[] = "?";
        }
        
        // Parses course logo
        if (!empty($logo)) {
            if (empty($logo['tmp_name']) || $this->isPhoto($logo))
                throw new \InvalidArgumentException("Invalid logo");
            
            $extension = explode("/", $logo['type'])[1];
            
            // Checks if photo extension has an accepted extension or not
            if ($extension != "jpg" && $extension != "jpeg" && $extension != "png")
                throw new \InvalidArgumentException("Invalid photo extension - must be .jpg, .jpeg or .png");
            
            $data_keys[] = "logo";
            
            // Generates photo name
            $filename = md5(rand(1,9999).time().rand(1,9999));
            $filename = $filename."."."jpg";
            
            // Saves photo
            move_uploaded_file($logo['tmp_name'], "../assets/images/logos/".$filename);
            
            // Puts image file name in the query
            $data_values[] = $filename;
            $data_sql[] = "?";
        }
        
        $sql = "
            INSERT INTO courses 
            (".implode(",",$data_keys).") 
            VALUES (".implode(",", $data_sql).")";
        
        // Executes query
        $sql = $this->db->prepare($sql);
        $sql->execute($data_values);
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Edits a course.
     *
     * @param       int $id_course Course id to be edited
     * @param       string $name New course name
     * @param       string $description [Optional] New course description
     * @param       string $logo [Optional] New course banner (obtained from
     * POST)
     * 
     * @return      bool If course was successfully edited
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function edit(int $id_course, string $name, string $description = '',
        array $logo = array()) : bool
    {
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Invalid course id");
        
        if (empty($name))
            throw new \InvalidArgumentException("Course name cannot be empty");
        
        $data_values = array();
        $data_sql = array();
        
        // Query construction
        $data_values[] = $name;
        $data_sql[] = "name = ?";
        
        if (!empty($description)) {
            $data_values[] = $description;
            $data_sql[] = "description = ?";
        }
        
        // Parses course logo
        if (!empty($logo)) {
            if (empty($logo['tmp_name']) || $this->isPhoto($logo))
                throw new \InvalidArgumentException("Invalid logo");
                
            $extension = explode("/", $logo['type'])[1];
            
            // Checks if photo extension has an accepted extension or not
            if ($extension != "jpg" && $extension != "jpeg" && $extension != "png")
                throw new \InvalidArgumentException("Invalid photo extension - must be .jpg, .jpeg or .png");
    
            // Generates photo name
            $filename = md5(rand(1,9999).time().rand(1,9999));
            $filename = $filename."."."jpg";
            
            // Saves photo
            move_uploaded_file($logo['tmp_name'], "../assets/images/logos/".$filename);
            
            // Puts image file name in the query
            $data_values[] = $filename;
            $data_sql[] = "logo = ?";
            
            // Deletes old image (if there is one)
            $imageName = $this->getImage($id_course);
            
            if (!empty($imageName)) {
                unlink("../assets/images/logos/".$imageName);
            }
        }

        $data_values[] = $id_course;
        
        $sql = "
            UPDATE courses 
            SET ".implode(",", $data_sql)." 
            WHERE id = ?
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
     * @return      bool If module was successfully added
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function addModule(int $id_course, int $id_module, int $order) : bool
    {   
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Invalid course id");
        
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Invalid module id");
        
        if (empty($order) || $order <= 0)
            throw new \InvalidArgumentException("Invalid order");
        
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO course_modules
            (id_module, id_course, order)
            VALUES (?, ?, ?)
        ");
        
        // Executes query
        $sql->execute($id_course, $id_module, $order);
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Removes a module from a course.
     * 
     * @param       int $id_course Course id
     * @param       int $id_module Module id
     * 
     * @return      bool If module was successfully removed from the course
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function deleteModuleFromCourse($id_course, $id_module)
    {
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Invalid course id");
            
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Invalid module id");
            
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM course_modules
            WHERE id_course = ? AND id_module = ?
        ");
        
        // Executes query
        $sql->execute(array($id_course, $id_module));
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Checks if a submitted photo is really a photo.
     *
     * @param       array $photo Submitted photo (from $_FILES)
     * 
     * @return      boolean If the photo is really a photo
     * 
     * @throws      \InvalidArgumentException If photo is empty
     */
    private function isPhoto(array $photo) : bool
    {
        if (empty($photo))
            throw new \InvalidArgumentException("Photo cannot be empty");
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $photo['tmp_name']);
        finfo_close($finfo);
        
        return explode("/", $mime)[0] == "image";
    }
}