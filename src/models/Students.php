<?php
namespace models;

use core\Model;


/**
 * 
 */
class Students extends Model
{
    //-----------------------------------------------------------------------
    //        Attributes
    //-----------------------------------------------------------------------
    private $id_user;
    
    
    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    public function __construct($id_user = -1)
    {
        parent::__construct();
        $this->id_user = $id_user;
    }


    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
    public static function isLogged()
    {
        if (empty($_SESSION['s_login'])) {
            return false;
        }
        
        return true;
    }
    
    public function login($email, $pass)
    {
        if (empty($email) || empty($pass)) { return false; }
        
        $sql = $this->db->prepare("SELECT id FROM students WHERE email = ? AND password = ?");
        $sql->execute(array($email, md5($pass)));
        
        if ($sql->rowCount() == 0) { return false; }
        
        $_SESSION['s_login'] = $sql->fetch()['id'];
        $this->id_user = $sql->fetch()['id'];
        
        return true;
    }
    
    public function register($student)
    {
        if ($this->existUser($student)) { return false; }
        
        $sql = $this->db->prepare("INSERT INTO students (name,genre,birthdate,email,password) VALUES (?,?,?,?,?)");
        $sql->execute(array(
            $student->getName(), 
            $student->getGenre(),
            $student->getBirthdate(),
            $student->getEmail(),
            md5($student->getPassword())
        ));

        if ($sql->rowCount() == 0) { return false; }
        
        $_SESSION['s_login'] = $this->db->lastInsertId();
        
        return true;
    }
    
    public function getName()
    {
        if ($this->id_user == -1) { return ""; }
        
        $response = "";
        
        $sql = $this->db->query("SELECT name FROM students WHERE id = $this->id_user");
        
        if ($sql && $sql->rowCount() > 0) {
            $response = $sql->fetch()['name'];
        }
        
        return $response;
    }
    
    public function get($id_user = -1)
    {
        if ($this->id_user == -1 && $id_user == -1) { return ""; }
        
        $response = null;
        
        $id_user = $id_user == -1 ? $this->id_user : $id_user;
        
        $sql = $this->db->prepare("SELECT * FROM students WHERE id = ?");
        $sql->execute(array($id_user));
        
        if ($sql->rowCount() > 0) {
            $data = $sql->fetch();
            $response = new Student($data['name'], $data['genre'], $data['birthdate'], $data['email']);
        }
        
        return $response;
    }
    
    public function getLastClassWatched($id_course)
    {
        $response = -1;
        
        $classes = new Classes();
        $courseClassIds = $classes->getClassesInCourse($id_course);
        
        $sql = $this->db->prepare("
            SELECT id_class 
            FROM historic 
            WHERE 
                id_student = $this->id_user AND
                id_class IN (".implode(",",$courseClassIds).")
            ORDER BY date_watched DESC 
            LIMIT 1
        ");
        $sql->execute(array($id_course));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch()['id_class'];
        }
        
        return $response;
    }
    
    public function exist($id_student)
    {
        if (empty($id_student) || $id_student <= 0) { return false; }
        
        $sql = $this->db->prepare("SELECT COUNT(*) AS count FROM students WHERE id = ?");
        $sql->execute(array($id_student));
        
        return $sql->fetch()['count'] > 0;
    }
    
    public function update($name, $genre, $birthdate)
    {
        if (empty($name)) { return false; }
        
        $sql = $this->db->prepare("UPDATE students SET name = ?, genre = ?, birthdate = ? WHERE id = ".$this->id_user);
        $sql->execute(array($name, $genre, $birthdate));
        
        return $sql->rowCount() > 0;
    }
    
    public function delete()
    {
        $response = false;
        
        $sql = $this->db->query("DELETE FROM students WHERE id = ".$this->id_user);
        
        if ($sql->rowCount() > 0) {
            $this->db->query("DELETE FROM historic WHERE id_student = ".$this->id_user);
            $this->db->query("DELETE FROM student_course WHERE id_student = ".$this->id_user);
            
            $response = true;
        }
        
        return $response;
    }
    
    public function updatePhoto($photo)
    {
        if (empty($photo)) { return false; }
        
        if (!empty($photo['tmp_name']) && $this->isPhoto($photo)) {
            $extension = explode("/", $photo['type'])[1];
            
            if ($extension == "jpg" || $extension == "jpeg" || $extension == "png") {
                
                $filename = md5(rand(1,9999).time().rand(1,9999));
                $filename = $filename."."."jpg";
                move_uploaded_file($photo['tmp_name'], "../assets/images/profile_photos/".$filename);
                
                // Deletes old image (if there is one)
                $imageName = $this->getPhoto();
                
                if (!empty($imageName)) {
                    unlink("../assets/images/profile_photos/".$imageName);
                }
            }
        }
        
        
        $sql = $this->db->query("UPDATE students SET photo = ".$filename." WHERE id = ".$this->id_user);
        
        return $sql->rowCount() > 0;
    }
    
    public function updatePassword($newPassword)
    {
        if (empty($newPassword)) { return false; }
        
        $sql = $this->db->query("UPDATE students SET password = ".md5($newPassword)." WHERE id = ".$this->id_user);
        
        return $sql->rowCount() > 0;
    }
    
    private function getPhoto()
    {
        $response = null;
        
        $sql = $this->db->query("SELECT photo FROM students WHERE id = ".$this->id_user);
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch()['photo'];
        }
        
        return $response;
    }
    
    private function existUser($student) 
    {
        $email = $student->getEmail();
        
        if (empty($email)) { return false; }
        
        $sql = $this->db->prepare("SELECT COUNT(*) as count FROM students WHERE email = ?");
        $sql->execute(array($email));

        return $sql->fetch()['count'] > 0;
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