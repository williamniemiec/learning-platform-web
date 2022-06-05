<?php
declare (strict_types=1);

namespace domain;


use DateTime;
use domain\enum\GenreEnum;
use dao\StudentsDAO;
use repositories\Database;


/**
 * Responsible for representing student-type users.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Student extends User
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $photo;
    

    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a student-type user.
     *
     * @param       int $id Student id
     * @param       string $name Student name
     * @param       GenreEnum $genre Student genre
     * @param       DateTime $birthdate Student birthdate
     * @param       string $email Student email
     * @param       string $photo [Optional] Name of the student photo file
     */
    public function __construct(int $id, string $name, GenreEnum $genre, 
        DateTime $birthdate, string $email, ?string $photo = '')
    {
        $this->id = $id;
        $this->name = $name;
        $this->genre = $genre;
        $this->birthdate = $birthdate;
        $this->email = $email;
        $this->photo = empty($photo) ? "" : $photo;
    }

    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Checks whether a student is logged.
     *
     * @return      bool If student is logged
     */
    public static function is_logged() : bool
    {
        return !empty($_SESSION['s_login']);
    }
    
    /**
     * Checks if login has been successfully or failed.
     *
     * @param       string $email Student's email
     * @param       string $password Student's password
     *
     * @return      Student Information about student logged in or null if 
     * login failed
     */
    public static function login(Database $db, string $email, string $password) : ?Student
    {
        $studentsDAO = new StudentsDAO($db);
        $student = $studentsDAO->login($email, $password);
        
        if (!empty($student))
            $_SESSION['s_login'] = $student->get_id();
        
        return $student;
    }
    
    
    /**
     * Gets logged in student.
     * 
     * @param       Database $db Database
     * 
     * @return      Student Student logged in or null if there is no student 
     * logged in
     */
    public static function get_logged_in(Database $db) : ?Student
    {
        if (empty($_SESSION['s_login']))
            return null;
        
        $studentsDAO = new StudentsDAO($db, $_SESSION['s_login']);
        
        return $studentsDAO->get();
    }
    
    /**
     * Logout current student.
     */
    public static function logout() : void
    {
        unset($_SESSION['s_login']);
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets the name of the student photo file.
     * 
     * @return      string Name of the student photo file or empty string if
     * the student does not have a registered photo.
     */
    public function getPhoto() : string
    {
        return $this->photo;
    }
    
    
    //-------------------------------------------------------------------------
    //        Serialization
    //-------------------------------------------------------------------------
    /**
     * {@inheritDoc}
     *  @see \JsonSerializable::jsonSerialize()
     *
     *  @Override
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $json['photo'] = $this->photo;
        
        return $json;
    }
}