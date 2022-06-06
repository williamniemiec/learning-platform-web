<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Student;
use domain\ClassType;
use domain\enum\GenreEnum;
use domain\util\FileUtil;
use domain\Bundle;
use domain\Purchase;


/**
 * Responsible for managing 'students' table.
 */
class StudentsDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $db;
    private $idStudent;
    
    
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
        $this->idStudent = $id_student;
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
        if (empty($email)) {
            throw new \InvalidArgumentException("Email cannot be empty");
        }
        
        if (empty($pass)) {
            throw new \InvalidArgumentException("Password cannot be empty");
        }
        
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
            $response = new Student(
                (int) $student['id_student'],
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
     * @return      Student Information about the student or null if student 
     * does not exist
     * 
     * @throws      \InvalidArgumentException If student id provided in the 
     * constructor is empty, less than or equal to zero
     */
    public function get() : ?Student
    {
        if (empty($this->idStudent) || $this->idStudent <= 0) {
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
        }
        
        $response = null;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  * 
            FROM    students
            WHERE   id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($this->idStudent));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $student = $sql->fetch();
            
            $response = new Student(
                (int) $student['id_student'],
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
     * @param       int idCourse Course id
     *
     * @return      ClassType Last class watched by the student or null if student
     * has never watched one 
     * 
     * @throws      \InvalidArgumentException If student id provided in the 
     * constructor or course id is empty, less than or equal to zero
     */
    public function getLastClassWatched(int $idCourse) : ?ClassType
    {
        if (empty($this->idStudent) || $this->idStudent <= 0) {
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
        }
        
        if (empty($idCourse) || $idCourse <= 0) {
            throw new \InvalidArgumentException("Course id cannot be empty ".
                "or less than or equal to zero");
        }
        
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
        $sql->execute(array($this->idStudent, $idCourse));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $class = $sql->fetch();
            
            if ($class['class_type'] == 'video') {
                $videos = new VideosDAO($this->db);
                
                $response = $videos->get(
                    (int) $class['id_module'], 
                    (int) $class['class_order']
                ); 
            }
            else {
                $questionnaires = new QuestionnairesDAO($this->db);
                
                $response = $questionnaires->get(
                    (int) $class['id_module'],
                    (int) $class['class_order']
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
     * @param       bool $autoLogin [Optional] If true, after registration is
     * completed the student will automatically login to the system
     *
     * @return      int Student id or -1 if the student has not been added
     *
     * @throws      \InvalidArgumentException If student or password are empty
     */
    public function register(Student $student, string $password, bool $autoLogin = true) : int
    {
        if (empty($student)) {
            throw new \InvalidArgumentException("Student cannot be empty");
        }
            
        if (empty($password)) {
            throw new \InvalidArgumentException("Password cannot be empty");
        }
                
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
            $student->getGenre()->get() == 1,
            $student->getBirthdate()->format("Y-m-d"),
            $student->getEmail(),
            md5($password)
        ));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            if ($autoLogin) {
                $_SESSION['s_login'] = $this->db->lastInsertId();
            }
                
            $response = $this->db->lastInsertId();
        }
        
        return $response;
    }
    
    /**
     * Updates current student information.
     * 
     * @param       Student $student Student to be updated
     * 
     * @return      boolean If student information has been successfully updated
     * 
     * @throws      \InvalidArgumentException If student is empty
     */
    public function update(Student $student) : bool
    {
        if (empty($student)) {
            throw new \InvalidArgumentException("Student cannot be empty");
        }

        // Query construction
        $sql = $this->db->prepare("
            UPDATE  students 
            SET     name = ?, genre = ?, birthdate = ? 
            WHERE   id_student = ?
        ");
        
        // Executes query
        $sql->execute(array(
            $student->getName(), 
            $student->getGenre()->get(), 
            $student->getBirthdate()->format("Y-m-d"),
            $student->getId()
        ));
        
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
        if (empty($this->idStudent) || $this->idStudent <= 0) {
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
        }
        
        // Query construction
        $sql = $this->db->query("
            DELETE FROM students 
            WHERE id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($this->idStudent));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Updates photo of the current student.
     * 
     * @param       array $photo New photo (from $_FILES)
     * 
     * @return      bool If photo has been successfully updated
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
        if (!empty($imageName)) {
            unlink("assets/img/profile_photos/".$imageName);
        }

        if (!empty($photo)) {
            $filename = FileUtil::storePhoto($photo, "../assets/img/profile_photos/");
        }
        
        $filename = empty($filename) ? null : "'".$filename."'";
        
        // Query construction
        $sql = $this->db->prepare("
            UPDATE students 
            SET photo = ".$filename."
            WHERE id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($this->idStudent));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Updates password from current student.
     * 
     * @param       string current_password Current student password
     * @param       string new_password New password
     * 
     * @return      bool If password has been successfully updated
     * 
     * @throws      \InvalidArgumentException If any password is empty 
     */
    public function updatePassword(string $currentPassword, string $newPassword) : bool
    {
        if (empty($currentPassword)) {
            throw new \InvalidArgumentException("Current password cannot be empty");
        }
        
        if (empty($newPassword)) {
            throw new \InvalidArgumentException("New password cannot be empty");
        }
        
        $response = false;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  COUNT(*) AS correctPassword 
            FROM    students 
            WHERE   id_student = ? AND password = '".md5($currentPassword)."'
        ");
        
        // Executes query
        $sql->execute(array($this->idStudent));
        
        // Checks if current password is correct
        if ($sql->fetch()['correctPassword'] > 0) {            
            // Query construction
            $sql = $this->db->prepare("
                UPDATE  students 
                SET     password = '".md5($newPassword)."' 
                WHERE   id_student = ?
            ");
            
            // Executes query
            $sql->execute(array($this->idStudent));
            
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
        if (empty($this->idStudent) || $this->idStudent <= 0) {
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
        }
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT      SUM(total_length_watched) AS total_length_watched,
                        COUNT(total_classes_watched) AS total_classes_watched
            FROM (
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
            ) AS tmp
        ");
        
        // Executes query
        $sql->execute(array($this->idStudent, $this->idStudent));
        
        return $sql->fetch();
    }

    /**
     * Gets all bundles purchased by the student.
     * 
     * @param       int $limit [Optional] Maximum bundles returned
     * @param       int $offset [Optional] Ignores first results from the return
     * 
     * @return      Purchase[] Bundles purchased by the student or empty array if
     * student has not yet purchased a bundle
     * 
     * @throws      \InvalidArgumentException If student id provided in the 
     * constructor or bundle id is empty, less than or equal to zero
     */
    public function getPurchases(int $limit = -1, int $offset = -1) : array
    {
        if (empty($this->idStudent) || $this->idStudent <= 0) {
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
        }
        
        $response = null;
            
        // Query construction
        $query = "
            SELECT  *, 
                    bundles.price AS price_bundle, 
                    purchases.price AS price_purchase
            FROM    purchases JOIN bundles USING (id_bundle)
            WHERE   id_student = ?    
        ";
        
        if ($limit > 0) {
            if ($offset > 0) {
                $query .= " LIMIT ".$offset.",".$limit;
            }
            else {
                $query .= " LIMIT ".$limit;
            }
        }
            
        $sql = $this->db->prepare($query);
            
        // Executes query
        $sql->execute(array($this->idStudent));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $purchase) {
                $response[] = new Purchase(
                    new Bundle(
                        (int) $purchase['id_bundle'], 
                        $purchase['name'], 
                        (float) $purchase['price_bundle'],
                        $purchase['logo'],
                        $purchase['description']
                    ),
                    new \DateTime($purchase['date']),
                    (float) $purchase['price_purchase']
                );
            }
        }
        
        return $response;
    }
    
    /**
     * Gets total student purchases.
     * 
     * @return      int Total purchases
     */
    public function countPurchases() : int
    {
        return (int) $this->db->query("
            SELECT  COUNT(*) AS total
            FROM    purchases
            WHERE   id_student = ".$this->idStudent
        )->fetch()['total'];
    }
    
    /**
     * Adds a bundle to current student.
     * 
     * @param       int idBundle Bundle id to be added
     * 
     * @return      bool If the bundle has been successfully added
     * 
     * @throws      \InvalidArgumentException If student id provided in the 
     * constructor or bundle id is empty, less than or equal to zero
     */
    public function addBundle(int $idBundle) : bool
    {
        if (empty($this->idStudent) || $this->idStudent <= 0) {
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
        }
        
        if (empty($idBundle) || $idBundle <= 0) {
            throw new \InvalidArgumentException("Bundle id cannot be less ".
                "than or equal to zero");
        }
            
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO purchases
            (id_student, id_bundle, date)
            VALUES (?, ?, NOW())
        ");
        
        // Executes query
        $sql->execute(array($this->idStudent, $idBundle));
        
        return !empty($sql) && $sql->rowCount() > 0;
    }
    
    /**
     * Checks whether student has a bundle.
     * 
     * @param       int $idBundle Bundle id
     * 
     * @return      bool If student has the bundle or not
     * 
     * @throws      \InvalidArgumentException If bundle id or if student id 
     * provided in the constructor or bundle id is empty, less than or equal 
     * to zero
     */
    public function hasBundle(int $idBundle) : bool
    {
        if (empty($this->idStudent) || $this->idStudent <= 0) {
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
        }
            
        if (empty($idBundle) || $idBundle <= 0) {
            throw new \InvalidArgumentException("Bundle id cannot be less ".
                "than or equal to zero");
        }
        
        $sql = $this->db->prepare("
            SELECT  COUNT(*) AS has_bundle
            FROM    purchases
            WHERE   id_student = ? AND id_bundle = ?
        ");
        
        $sql->execute(array($this->idStudent, $idBundle));
        
        return $sql->fetch()['has_bundle'] > 0;
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
    public function isEmailInUse(string $email) : bool
    {
        if (empty($email)) {
            throw new \InvalidArgumentException("Email cannot be empty");
        }

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
    
    /**
     * Clears student history.
     * 
     * @return      bool If historic has been successfully removed
     * 
     * @throws      \InvalidArgumentException If student id provided in the 
     * constructor is empty, less than or equal to zero
     */
    public function clearHistory()
    {
        if (empty($this->idStudent) || $this->idStudent <= 0) {
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
        }
            
        $sql = $this->db->query("
            DELETE FROM student_historic
            WHERE id_student = ".$this->idStudent
        );
        
        return !empty($sql) && $sql->rowCount() > 0;
    }
    
    /**
     * Gets photo of the logged in student.
     * 
     * @return      string Photo filename or empty string if student does not
     * have a photo
     */
    private function getPhoto()
    {
        $response = "";
        
        $sql = $this->db->query("
            SELECT  photo
            FROM    students
            WHERE   id_student = ".Student::getLoggedIn($this->db)->getId()
        );
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = $sql->fetch()['photo'];
        }
        
        return $response;
    }
}