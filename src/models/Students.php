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
    
    public function getLastClassWatched()
    {
        $response = -1;
        
        $sql = $this->db->query("SELECT id_class FROM historic WHERE id_student = $this->id_user ORDER BY date_watched DESC LIMIT 1");
        
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
    
    private function existUser($student) 
    {
        $email = $student->getEmail();
        
        if (empty($email)) { return false; }
        
        $sql = $this->db->prepare("SELECT COUNT(*) as count FROM students WHERE email = ?");
        $sql->execute(array($email));

        return $sql->fetch()['count'] > 0;
    }
}