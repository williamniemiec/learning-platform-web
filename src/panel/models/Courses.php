<?php
namespace models;

use core\Model;
use models\obj\Course;


/**
 * Responsible for managing courses.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class Courses extends Model
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates courses manager.
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
    public function getCoursesByBundle($bundleName) 
    {
        $sql = $this->db->prepare("
            SELECT      id_course, courses.name, logo, courses.description,
                        COUNT(id_student) AS tot_students
            FROM		courses NATURAL JOIN bundle_courses
            			NATURAL JOIN purchases
                        JOIN bundles USING (id_bundle)
            WHERE       bundles.name LIKE ?
            GROUP BY    id_course, name, logo, description
        ");
        
        $sql->execute(array($bundleName));
    }
    
    /**
     * Gets informations about all registered courses.
     * 
     * @return      array Informations about all registered courses
     */
    public function getCourses($name = '', $orderBy='', $orderType='')
    {
        $response = array();

        $query = "
            SELECT      id_course, name, logo, description, 
                        COUNT(id_student) AS total_students
            FROM		courses 
                        NATURAL JOIN bundle_courses 
            			NATURAL JOIN purchases
			GROUP BY    id_course, name, logo, description
        ";
        
        if (!empty($orderBy)) {
            $query .= "ORDER BY total_students ".$orderType;
        }
        
        if (!empty($name)) {
            $query .= " HAVING name LIKE ?";
            $sql = $this->db->prepare($query);
            $sql->execute(array($name.'%'));
        }
        else {
            $sql = $this->db->query($query);
        }
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetchAll();
        }
        
        return $response;
    }
    
    /**
     * Deletes a course.
     * 
     * @param       int $id_course Course id to be deleted
     */
    public function delete($id_course)
    {
        
        
//         // Get all classes from this course
//         $classIds = $this->getAllClasses($id_course);
        
//         // Delete classes from course
//         $sql = $this->db->prepare("DELETE FROM classes WHERE id_module = ?");
//         $sql->execute(array($id_course));
        
//         // Delete modules from course
//         $sql = $this->db->prepare("DELETE FROM modules WHERE id_course = ?");
//         $sql->execute(array($id_course));
        
//         // Delete historic from course
//         if (count($classIds) > 0) {
//             $this->db->query("DELETE FROM historic WHERE id_class IN (".implode(",",$classIds).")");
//         }
        
//         // Delete videos from course
//         $this->db->query("DELETE FROM videos WHERE id_class IN (".implode(",",$classIds).")");
        
//         // Delete questionnaires from course
//         $this->db->query("DELETE FROM questionnaries WHERE id_class IN (".implode(",",$classIds).")");
        
//         // Delete student-course relationships
//         $sql = $this->db->prepare("DELETE FROM student_course WHERE id_course = ?");
//         $sql->execute(array($id_course));
        
        // Delete image, if there is one
        $imageName = $this->getImage($id_course);
        
        $sql = $this->db->prepare("
            DELETE FROM courses
            WHERE id_course = ?
        ");
        
        $sql->execute(array($id_course));
        
        if (!empty($imageName)) {
            unlink("../assets/images/logos/".$imageName);
        }
    }
    
    /**
     * Gets course banner.
     * 
     * @param       int $id_course Course id
     * 
     * @return      string Course banner filename
     */
    public function getImage($id_course)
    {
        if (empty($id_course) || $id_course <= 0) { return ""; }
        
        $response = "";
        
        $sql = $this->db->prepare("
            SELECT logo 
            FROM courses 
            WHERE id_course = ?
        ");
        $sql->execute(array($id_course));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch()['logo'];
        }
        
        return $response;
    }
    
    /**
     * Gets informations about all classes from a course.
     * 
     * @param       int $id_course Course id
     * 
     * @return      array Informations about all classes from a course
     */
//     public function getAllClasses($id_course)
//     {
//         if (empty($id_course) || $id_course <= 0) { return array(); }
        
//         $response = array();
        
//         $sql = $this->db->prepare("
//             SELECT id FROM classes WHERE id_course = ?
//         ");
//         $sql->execute(array($id_course));
        
//         if ($sql->rowCount() > 0) {
//             $classes = $sql->fetchAll();
            
//             foreach ($classes as $class) {
//                 $response[] = $class['id'];
//             }
//         }
        
//         return $response;
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
     * Adds a new course.
     * 
     * @param       string $name Course name
     * @param       string $description [Optional] Course description
     * @param       string $logo [Optional] Course banner (obtained from POST) 
     * 
     * @return      boolean If course was successfully added
     */
    public function add($name, $description = '', $logo = array())
    {
        if (empty($name)) { return false; }
        
        $data_keys = array();
        $data_values = array();
        $data_sql = array();
        
        $data_keys[] = "name";
        $data_values[] = $name;
        $data_sql[] = "?";
        
        if (!empty($description)) {
            $data_keys[] = "description";
            $data_values[] = $description;
            $data_sql[] = "?";
        }
        
        if (!empty($logo) && !empty($logo['tmp_name']) && $this->isPhoto($logo)) {
            $extension = explode("/", $logo['type'])[1];
            
            if ($extension == ".jpg" || $extension == ".jpeg" || $extension == ".png") {
                $data_keys[] = "logo";
                
                $filename = md5(rand(1,9999).time().rand(1,9999));
                $filename = $filename."."."jpg";
                move_uploaded_file($logo['tmp_name'], "../assets/images/logos/".$filename);
                $data_values[] = $filename;
                $data_sql[] = "?";
            }
        }
        
        $sql = "
            INSERT INTO courses 
            (".implode(",",$data_keys).") 
            VALUES (".implode(",", $data_sql).")";
        
        $sql = $this->db->prepare($sql);
        $sql->execute($data_values);
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Edits a course.
     *
     * @param       string $name New course name
     * @param       string $description [Optional] New course description
     * @param       string $logo [Optional] New course banner (obtained from POST)
     * 
     * @return      boolean If course was successfully edited
     */
    public function edit($id_course, $name, $description = '', $logo = array())
    {
        $data_values = array();
        $data_sql = array();
        
        $data_values[] = $name;
        $data_sql[] = "name = ?";
        
        if (!empty($description)) {
            $data_values[] = $description;
            $data_sql[] = "description = ?";
        }
        
        if (!empty($logo) && !empty($logo['tmp_name']) && $this->isPhoto($logo)) {
            $extension = explode("/", $logo['type'])[1];
            
            if ($extension == "jpg" || $extension == "jpeg" || $extension == "png") {
                
                $filename = md5(rand(1,9999).time().rand(1,9999));
                $filename = $filename."."."jpg";
                move_uploaded_file($logo['tmp_name'], "../assets/images/logos/".$filename);
                $data_values[] = $filename;
                $data_sql[] = "logo = ?";
                
                // Deletes old image (if there is one)
                $imageName = $this->getImage($id_course);

                if (!empty($imageName)) {
                    unlink("../assets/images/logos/".$imageName);
                }
            }
        }

        $data_values[] = $id_course;
        
        $sql = "
            UPDATE courses 
            SET ".implode(",", $data_sql)." 
            WHERE id = ?
        ";
        
        $sql = $this->db->prepare($sql);
        $sql->execute($data_values);
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Gets informations about all courses.
     * 
     * @param       int $id_student [Optional] Gets informations about all
     * courses that a student has
     * 
     * @return      array Informations about all courses
     */
    public function getAll($limit = -1)
    {
        $query = "SELECT * FROM courses";
        
        $response = array();
        
        if ($limit > 0)
            $query .= " LIMIT ".$limit;
        
        $sql = $this->db->query($query);
        
        if ($sql->rowCount() > 0) {
            $courses = $sql->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($courses as $course) {
                $response[] = new Course(
                    $course['id_course'], $course['name']);
            }
        }
        
        return $response;
    }
    
    public function addModule($id_course, $id_module, $order)
    {   
        $sql = $this->db->prepare("
            INSERT INTO course_modules
            (id_module, id_course, order)
            VALUES (?, ?, ?)
        ");
        
        $sql->execute($id_course, $id_module, $order);
        
        return $sql->rowCount() > 0;
    }
    
    public function deleteModuleFromCourse($id_course, $id_module)
    {
        $sql = $this->db->prepare("
            DELETE FROM course_modules
            WHERE id_course = ? AND id_module = ?
        ");
        
        $sql->execute(array($id_course, $id_module));
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Checks if a submitted photo is really a photo.
     *
     * @param array $photo Submitted photo
     * 
     * @return boolean If the photo is really a photo
     */
    private function isPhoto($photo)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $photo['tmp_name']);
        finfo_close($finfo);
        
        return explode("/", $mime)[0] == "image";
    }
}