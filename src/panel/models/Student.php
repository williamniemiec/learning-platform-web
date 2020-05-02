<?php
namespace models;


class Student
{
    //-----------------------------------------------------------------------
    //        Attributes
    //-----------------------------------------------------------------------
    private $id;
    private $name;
    private $genre;
    private $birthdate;
    private $email;
    private $password;
    

    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    public function __construct($name, $genre, $birthdate, $email, $password = "")
    {
        $this->name = $name;
        $this->genre = $genre;
        $this->birthdate = $birthdate;
        $this->email = $email;
        $this->password = $password;
    }

    
    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
    public function getName()
    {
        return $this->name;
    }
    
    public function getGenre()
    {
        return $this->genre;
    }
    
    public function getBirthdate()
    {
        return $this->birthdate;
    }
    
    public function getEmail()
    {
        return $this->email;
    }
    
    public function getPassword()
    {
        return $this->password;
    }
    
    public function setPassword($password)
    {
        $this->password = $password;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getCourses()
    {
        $students = new Students();
        return $students->getCourses($this->id);
    }
    
    public function getCoursesName()
    {
        $response = array();
        
        foreach ($this->getCourses() as $course) {
            $response[] = $course['name'];
        }
            
        return $response;
    }
}