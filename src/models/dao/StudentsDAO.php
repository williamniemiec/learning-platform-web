<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Student;
use models\_Class;
use models\enum\GenreEnum;
use models;
use models\util\DataUtil;


/**
 * Responsible for managing 'students' table.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class StudentsDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $db;
    private $id_student;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'students' table manager.
     *
     * @param       Database $db Database
     * @param       int $id_student [Optional] Student id
     */
    public function __construct(Database $db, int $id_student = -1)
    {
        $this->db = $db->getConnection();
        $this->id_student = $id_student;
    }


    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Checks whether student credentials are correct.
     *
     * @param       string $email Student's email
     * @param       string $pass Student's password
     *
     * @return      Student Information about student logged in or null if 
     * credentials provided are incorrect
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function login(string $email, string $pass) : ?Student
    {
        if (empty($email))
            throw new \InvalidArgumentException("Email cannot be empty");
        
        if (empty($pass))
            throw new \InvalidArgumentException("Password cannot be empty");
        
        $response = null;
            
        // Query construction
        $sql = $this->db->prepare("
            SELECT  * 
            FROM    students 
            WHERE   email = ? AND password = ?
        ");
        
        // Executes query
        $sql->execute(array($email, md5($pass)));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $student = $sql->fetch();
            var_dump($student);
            $response = new Student(
                (int)$student['id_student'],
                $student['name'],
                new GenreEnum($student['genre']),
                new \DateTime($student['birthdate']),
                $student['email'],
                $student['photo']
            );
        }
        
        return $response;
    }
    
    /**
     * Gets information about the logged in student.
     *
     * @return      Student Informations about the student or null if student 
     * does not exist
     * 
     * @throws      \InvalidArgumentException If student id provided in the 
     * constructor is empty, less than or equal to zero
     */
    public function get() : Student
    {
        if (empty($this->id_student) || $this->id_student <= 0)
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
        
        $response = NULL;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  * 
            FROM    students
            WHERE   id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($this->id_student));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $student = $sql->fetch();
            
            $response = new Student(
                (int)$student['id_student'],
                $student['name'], 
                new GenreEnum($student['genre']), 
                new \DateTime($student['birthdate']), 
                $student['email'],
                $student['photo'] 
            );
        }
        
        return $response;
    }
    
    /**
     * Gets last class watched by the student.
     *
     * @param       int $id_course Course id
     *
     * @return      _Class Last class watched by the student
     * 
     * @throws      \InvalidArgumentException If student id provided in the 
     * constructor or course id is empty, less than or equal to zero
     */
    public function getLastClassWatched(int $id_course) : _Class
    {
        if (empty($this->id_student) || $this->id_student <= 0)
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
        
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Course id cannot be empty ".
                "or less than or equal to zero");
        
        $response = null;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT      id_module, class_order,
                        CASE
                            WHEN class_type = 0 THEN 'video'
                            ELSE 'questionnaire'
                        END AS class_type
            FROM        student_historic
            WHERE       id_student = ? AND
                        id_module IN (SELECT    id_module
                                      FROM      course_modules
                                      WHERE     id_course = ?)
            ORDER BY    date DESC
            LIMIT 1
        ");
        
        // Executes query
        $sql->execute(array($this->id_student, $id_course));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $class = $sql->fetch();
            
            if ($class['class_type'] == 'video') {
                $videos = new VideosDAO($this->db);
                
                $response = $videos->get(
                    (int)$class['id_module'], 
                    (int)$class['class_order']
                ); 
            }
            else {
                $questionnaries = new QuestionnairesDAO($this->db);
                
                $response = $questionnaries->get(
                    (int)$class['id_module'],
                    (int)$class['class_order']
                ); 
            }
        }
        
        return $response;
    }
    
    /**
     * Adds a new student.
     *
     * @param       Student $student Informations about the student
     * @param       string $password Student password
     * @param       bool $autologin [Optional] If true, after registration is
     * completed the student will automatically login to the system
     *
     * @return      int Student id or -1 if the student has not been added
     *
     * @throws      \InvalidArgumentException If student or password are empty
     */
    public function register(Student $student, string $password, bool $autologin = true) : int
    {
        if (empty($student))
            throw new \InvalidArgumentException("Student cannot be empty");
            
        if (empty($password))
            throw new \InvalidArgumentException("Password cannot be empty");
                
        $response = -1;
        
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO students
            (name,genre,birthdate,email,password)
            VALUES (?,?,?,?,?)
        ");
                
        // Executes query
        $sql->execute(array(
            $student->getName(),
            $student->getGenre(),
            $student->getBirthdate(),
            $student->getEmail(),
            md5($password)
        ));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            if ($autologin)
                $_SESSION['s_login'] = $this->db->lastInsertId();
                
            $response = $this->db->lastInsertId();
        }
        
        return $response;
    }
    
    /**
     * Updates current student information.
     * 
     * @param       string $name
     * @param       GenreEnum $genre New genre
     * @param       string $birthdate New birthdate
     * 
     * @return      boolean If student information has been successfully updated
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function update(string $name, GenreEnum $genre, string $birthdate) : bool
    {
        if (empty($this->id_student) || $this->id_student <= 0)
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
        
        if (empty($genre) || empty($genre->get()))
            throw new \InvalidArgumentException("Genre cannot be empty");
        
        if (empty($name))
            throw new \InvalidArgumentException("Name cannot be empty");
            
        if (empty($birthdate))
            throw new \InvalidArgumentException("Birthdate cannot be empty");
        
        // Query construction
        $sql = $this->db->prepare("
            UPDATE students 
            SET name = ?, genre = ?, birthdate = ? 
            WHERE id = ?
        ");
        
        // Executes query
        $sql->execute(array($name, $genre, $birthdate));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Deletes current student.
     * 
     * @return      bool If student has been successfully deleted
     * 
     * @throws      \InvalidArgumentException If student id provided in the 
     * constructor is empty, less than or equal to zero
     */
    public function delete() : bool
    {
        if (empty($this->id_student) || $this->id_student <= 0)
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
        
        // Query construction
        $sql = $this->db->query("
            DELETE FROM students 
            WHERE id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($this->id_student));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Updates photo of the current student.
     * 
     * @param       array $photo New photo (from $_FILES)
     * 
     * @return      boolean If photo has been successfully updated
     * 
     * @throws      \InvalidArgumentException If photo is invalid
     * 
     * @implSpec    If photo is empty, current photo will be removed
     */
    public function updatePhoto(array $photo) : bool
    {
        // Deletes old image (if there is one)
        $imageName = $this->getPhoto();
        
        // Deletes photo
        if (!empty($imageName))
            unlink("assets/images/profile_photos/".$imageName);
        
        if (!empty($photo)) {
            if (empty($photo['tmp_name']) || DataUtil::isPhoto($photo))
                throw new \InvalidArgumentException("Invalid photo");
            
            $extension = explode("/", $photo['type'])[1];
            
            // Checks if photo extension has an accepted extension or not
            if ($extension != "jpg" && $extension != "jpeg" && $extension != "png")
                throw new \InvalidArgumentException("Invalid photo extension - must be .jpg, .jpeg or .png");
            
            // Generates photo name
            $filename = md5(rand(1,9999).time().rand(1,9999));
            $filename = $filename."."."jpg";
            
            // Saves photo
            move_uploaded_file($photo['tmp_name'], "assets/images/profile_photos/".$filename);
        }
        
        $filename = empty($filename) ? "'".$filename."'" : NULL;
        
        // Query construction
        $sql = $this->db->prepare("
            UPDATE students 
            SET photo = ".$filename." 
            WHERE id = ?
        ");
        
        // Executes query
        $sql->execute(array($this->id_student));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Updates password from current student.
     * 
     * @param       string $currentPassword Current student password
     * @param       string $newPassword New password
     * 
     * @return      bool If password has been successfully updated
     * 
     * @throws      \InvalidArgumentException If any password is empty 
     */
    public function updatePassword(string $currentPassword, string $newPassword) : bool
    {
        if (empty($currentPassword))
            throw new \InvalidArgumentException("Current password cannot be empty");
        
        if (empty($currentPassword))
            throw new \InvalidArgumentException("New password cannot be empty");
        
        $response = false;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  COUNT(*) AS correctPassword 
            FROM    students 
            WHERE   id_student = ? AND password = '".md5($currentPassword)."'
        ");
        
        // Executes query
        $sql->execute(array($this->id_student));
        
        // Checks if current password is correct
        if ($sql->fetch()['correctPassword'] > 0) {
            // Query construction
            $sql = $this->db->query("
                UPDATE  students 
                SET     password = '".md5($newPassword)."' 
                WHERE   id_student = ?
            ");
            
            // Executes query
            $sql->execute(array($this->id_student));
            
            $response = $sql && $sql->rowCount() > 0;
        }
        
        return $response;
    }
    
    /**
     * Gets total classes watched by current student along with its total
     * duration (in minutes).
     * 
     * @return      array Total classes watched by current student along with
     * its total duration. The returned array has the following keys:
     * <ul>
     *  <li><b>total_length_watched</b>: Total time of classes watched by current
     *  student/li>
     *  <li><b>total_classes_watched</b>: Total classes watched by current 
     *  student</li>
     * </ul>
     * 
     * @throws      \InvalidArgumentException If student id provided in the 
     * constructor is empty, less than or equal to zero
     */
    public function getTotalWatchedClasses() : array
    {
        if (empty($this->id_student) || $this->id_student <= 0)
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT      SUM(length) AS total_length_watched,
                        COUNT(id_module) AS total_classes_watched
            FROM        vw_student_historic_watched_length
            WHERE       id_student = ?
            GROUP BY    id_module
            HAVING      id_module IN (SELECT    id_module
            			        	  FROM      course_modules 
                                                NATURAL JOIN bundle_courses 
                                                NATURAL JOIN purchases
            					      WHERE     id_student = ?)
        ");
        
        // Executes query
        $sql->execute(array($this->id_student, $this->id_student));
        
        return $sql->fetch();
    }

    /**
     * Gets all bundles purchased by the student.
     * 
     * @param       int $limit [Optional] Maximum bundles returned
     * @param       int $offset [Optional] Ignores first results from the return
     * 
     * @return      models\Bundle[] Bundles purchased by the student
     * 
     * @throws      \InvalidArgumentException If student id provided in the 
     * constructor or bundle id is empty, less than or equal to zero
     */
    public function getPurchases(int $limit = -1, int $offset = -1) : array
    {
        if (empty($this->id_student) || $this->id_student <= 0)
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
            
        // Query construction
        $query = "
            SELECT  *
            FROM    purchases NATURAL JOIN bundles
            WHERE   id_student = ?    
        ";
        
        if ($limit > 0) {
            if ($offset > 0)
                $query .= " LIMIT ".$offset.",".$limit;
            else
                $query .= " LIMIT ".$limit;
        }
            
        $sql = $this->db->prepare($query);
            
        // Executes query
        $sql->execute(array($this->id_student));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Adds a bundle to current student.
     * 
     * @param       int $id_bundle Bundle id to be added
     * 
     * @return      bool If the bundle has been successfully added
     * 
     * @throws      \InvalidArgumentException If student id provided in the 
     * constructor or bundle id is empty, less than or equal to zero
     */
    public function addBundle(int $id_bundle) : bool
    {
        if (empty($this->id_student) || $this->id_student <= 0)
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
        
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO purchases
            (id_student, id_bundle, date)
            VALUES (?, ?, NOW())
        ");
        
        // Executes query
        $sql->execute(array($this->id_student, $id_bundle));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Checks whether an email is already in use.
     *
     * @param       string $email Email to be analyzed
     *
     * @return      bool If there is already an user using the given email
     * 
     * @throws      \InvalidArgumentException If email is empty
     */
    public function existStudent(string $email) : bool
    {
        if (empty($email))
            throw new \InvalidArgumentException("Email cannot be empty");

        // Query construction
        $sql = $this->db->prepare("
            SELECT  COUNT(*) AS count 
            FROM    students, admins 
            WHERE   email = ?
        ");
        
        // Executes query
        $sql->execute(array($email));

        return $sql->fetch()['count'] > 0;
    }
}