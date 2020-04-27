<?php
namespace models;

use core\Model;


/**
 *
 */
class Courses extends Model
{
    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    public function __construct()
    {
        parent::__construct();
    }
    
    
    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
    public function getCourses()
    {
        $response = array();

        $sql = $this->db->query("
            SELECT 
                *,
                (select count(*) from student_course where id_course = courses.id) as total_students 
            FROM courses
        "); 
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetchAll();
        }
        
        return $response;
    }
    
    public function delete($id_course)
    {
        // Get all classes from this course
        $classIds = $this->getAllClasses($id_course);
        
        // Delete classes from course
        $sql = $this->db->prepare("DELETE FROM classes WHERE id_module = ?");
        $sql->execute(array($id_course));
        
        // Delete modules from course
        $sql = $this->db->prepare("DELETE FROM modules WHERE id_course = ?");
        $sql->execute(array($id_course));
        
        // Delete historic from course
        if (count($classIds) > 0) {
            $this->db->query("DELETE FROM historic WHERE id_class IN (".implode(",",$classIds).")");
        }
        
        // Delete videos from course
        $this->db->query("DELETE FROM videos WHERE id_class IN (".implode(",",$classIds).")");
        
        // Delete questionnaires from course
        $this->db->query("DELETE FROM questionnaries WHERE id_class IN (".implode(",",$classIds).")");
        
        // Delete student-course relationships
        $sql = $this->db->prepare("DELETE FROM student_course WHERE id_course = ?");
        $sql->execute(array($id_course));
        
        // Delete image, if there is one
        $imageName = $this->getImage($id_course);
        
        if (!empty($imageName)) {
            unlink("../assets/images/logos/".$imageName);
        }
        
        // Delete course
        $sql = $this->db->prepare("DELETE FROM courses WHERE id = ?");
        $sql->execute(array($id_course));
    }
    
    public function getImage($id_course)
    {
        if (empty($id_course) || $id_course <= 0) { return ""; }
        
        $response = "";
        
        $sql = $this->db->prepare("SELECT logo FROM courses WHERE id_course = ?");
        $sql->execute(array($id_course));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch()['logo'];
        }
        
        return $response;
    }
    
    public function getAllClasses($id_course)
    {
        if (empty($id_course) || $id_course <= 0) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("SELECT id FROM classes WHERE id_course = ?");
        $sql->execute(array($id_course));
        
        if ($sql->rowCount() > 0) {
            $classes = $sql->fetchAll();
            
            foreach ($classes as $class) {
                $response[] = $class['id'];
            }
        }
        
        return $response;
    }
    
    public function countCourses()
    {
        $sql = $this->db->query("SELECT COUNT(*) as count FROM student_course WHERE id_student = $this->id_user");
        return $sql->fetch()['count'];
    }
    
    public function getCourse($id_course)
    {
        if (empty($id_course) || $id_course <= 0) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("SELECT * FROM courses WHERE id = ?");
        $sql->execute(array($id_course));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch();
            
            $modules = new Modules();
            $response['modules'] = $modules->getModules($id_course);
        }
        
        return $response;
    }
    
    public function isEnrolled($id_course)
    {
        $sql = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM student_course 
            WHERE 
                id_course = ? AND 
                id_student = $this->id_user
        ");
        $sql->execute(array($id_course));
        
        return $sql->fetch()['count'] > 0;
    }
    
    public function add($name, $description = '', $logo = '')
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
        
        if (!empty($logo['tmp_name']) && $this->isPhoto($logo)) {
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
        
        $sql = "INSERT INTO courses (".implode(",",$data_keys).") VALUES (".implode(",", $data_sql).")";
        
        $sql = $this->db->prepare($sql);
        $sql->execute($data_values);
        
        return $sql->rowCount() > 0;
    }
    
    public function edit($id_course, $name, $description = '', $logo = '')
    {
        $data_values = array();
        $data_sql = array();
        
        $data_values[] = $name;
        $data_sql[] = "name = ?";
        
        if (!empty($description)) {
            $data_values[] = $description;
            $data_sql[] = "description = ?";
        }
        
        if (!empty($logo['tmp_name']) && $this->isPhoto($logo)) {
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
        
        $sql = "UPDATE courses SET ".implode(",", $data_sql)." WHERE id = ?";
        $sql = $this->db->prepare($sql);
        $sql->execute($data_values);
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Checks if a submitted photo is really a photo.
     *
     * @param array $photo Submitted photo
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