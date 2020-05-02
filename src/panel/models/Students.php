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
    
    public function register(Student $student, $autologin = true)
    {
        if ($this->existUser($student)) { return -1; }
        
        $sql = $this->db->prepare("INSERT INTO students (name,genre,birthdate,email,password) VALUES (?,?,?,?,?)");
        $sql->execute(array(
            $student->getName(), 
            $student->getGenre(),
            $student->getBirthdate(),
            $student->getEmail(),
            md5($student->getPassword())
        ));

        if ($sql->rowCount() == 0) { return -1; }
        
        if ($autologin)
            $_SESSION['s_login'] = $this->db->lastInsertId();
        
        return $this->db->lastInsertId();
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
        if ($this->id_user == -1 && $id_user == -1) { return array(); }
        
        $response = array();
        
        $id_user = $id_user == -1 ? $this->id_user : $id_user;
        
        $sql = $this->db->prepare("SELECT * FROM students WHERE id = ?");
        $sql->execute(array($id_user));
        
        if ($sql->rowCount() > 0) {
            $data = $sql->fetch();
            $response = new Student($data['name'], $data['genre'], $data['birthdate'], $data['email']);
        }
        
        return $response;
    }
    
    public function getAll()
    {
        $response = array();

        $sql = $this->db->query("SELECT * FROM students");
        
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $student) {
                $s = new Student($student['name'], $student['genre'], $student['birthdate'], $student['email']);
                $s->setId($student['id']);
                $response[] = $s;
            }
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
    
    public function getCourses($id_student)
    {
        if (empty($id_student) || $id_student <= 0) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("SELECT id_course FROM student_course WHERE id_student = ?");
        $sql->execute(array($id_student));

        if ($sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $course) {
                $sql = $this->db->query("SELECT * FROM courses WHERE id = ".$course['id_course']);
                
                if ($sql->rowCount() > 0) {
                    $response[] = $sql->fetch(\PDO::FETCH_ASSOC);
                }
            }
        }
        
        return $response;
    }
    
    public function delete($id_student)
    {
        if (empty($id_student)) { return false; }
        
        $response = false;
        
        $sql = $this->db->prepare("DELETE FROM students WHERE id = ?");
        $sql->execute(array($id_student));
        
        if ($sql->rowCount() > 0) {
            $sql = $this->db->prepare("DELETE FROM historic WHERE id_student = ?");
            $sql->execute(array($id_student));
            
            $sql = $this->db->prepare("DELETE FROM student_course WHERE id_student = ?");
            $sql->execute(array($id_student));
            
            $response = true;
        }
        
        return $response;
    }
    
    public function edit(Student $student)
    {
        if (empty($student) || empty($student->getId())) { return false; }
        
        if (empty($student->getPassword())) {
            $sql = $this->db->prepare("UPDATE students SET name = ?, genre = ?, birthdate = ?, email = ? WHERE id = ?");
            $sql->execute(array(
                $student->getName(),
                $student->getGenre(),
                $student->getBirthdate(),
                $student->getEmail(),
                $student->getId()
            ));
        } else {
            $sql = $this->db->prepare("UPDATE students SET name = ?, genre = ?, birthdate = ?, email = ?, password = ? WHERE id = ?");
            $sql->execute(array(
                $student->getName(),
                $student->getGenre(),
                $student->getBirthdate(),
                $student->getEmail(),
                md5($student->getPassword()),
                $student->getId()
            ));
        }
        
        return $sql->rowCount() > 0;
    }
    
    public function addCourse($id_student, $id_course)
    {
        if (empty($id_student) || empty($id_course)) { return false; }
        
        $sql = $this->db->prepare("INSERT INTO student_course SET id_student = ?, id_course = ?");
        $sql->execute(array($id_student, $id_course));
        
        return $sql->rowCount() > 0;
    }
    
    public function deleteAllCourses($id_student)
    {
        if (empty($id_student)) { return false; }
        
        $sql = $this->db->prepare("DELETE FROM student_course WHERE id_student = ?");
        $sql->execute(array($id_student));
        
        return $sql->rowCount() > 0;
    }
    
    private function existUser($student) 
    {
        $email = $student->getEmail();
        
        if (empty($email)) { return false; }
        
        $sql = $this->db->prepare("SELECT COUNT(*) as count FROM students WHERE email = ?");
        $sql->execute(array($email));

        return $sql->fetch()['count'] > 0;
    }
}