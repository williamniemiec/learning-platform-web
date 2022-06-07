<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Student;
use domain\ClassType;
use domain\enum\GenreEnum;
use util\FileUtil;
use domain\Bundle;
use domain\Purchase;


/**
 * Responsible for managing 'students' table.
 */
class StudentsDAO extends DAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
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
        parent::__construct($db);
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
        $this->validateEmail($email);
        $this->validatePassword($pass);
        $this->withQuery("
            SELECT  * 
            FROM    students 
            WHERE   email = ? AND password = ?
        ");
        $this->runQueryWithArguments($email, md5($pass));
        
        return $this->parseStudentResponseQuery();
    }

    private function validateEmail($email)
    {
        if (empty($email)) {
            throw new \InvalidArgumentException("Email cannot be empty");
        }
    }

    private function validatePassword($password)
    {
        if (empty($password)) {
            throw new \InvalidArgumentException("Password cannot be empty");
        }
    }

    private function parseStudentResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return null;
        }

        $studentRaw = $this->getResponseQuery();
        
        return new Student(
            (int) $studentRaw['id_student'],
            $studentRaw['name'],
            new GenreEnum($studentRaw['genre']),
            new \DateTime($studentRaw['birthdate']),
            $studentRaw['email'],
            $studentRaw['photo']
        );
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
        $this->validateStudentId($this->idStudent);
        $this->withQuery("
            SELECT  * 
            FROM    students
            WHERE   id_student = ?
        ");
        $this->runQueryWithArguments($this->idStudent);
        
        return $this->parseStudentResponseQuery();
    }

    private function validateStudentId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Student id logged in must be ".
                                                "provided in the constructor");
        }
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
        $this->validateStudentId($this->idStudent);
        $this->validateCourseId($idCourse);
        $this->withQuery("
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
        $this->runQueryWithArguments($this->idStudent, $idCourse);
        
        return $this->parseClassResponseQuery();
    }

    private function validateCourseId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Course id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }

    private function parseClassResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return null;
        }

        $class = null;
        $rawClass = $this->getResponseQuery();
            
        if ($rawClass['class_type'] == 'video') {
            $videosDao = new VideosDAO($this->db);
            $class = $videosDao->get(
                (int) $rawClass['id_module'], 
                (int) $rawClass['class_order']
            ); 
        }
        else {
            $questionnairesDao = new QuestionnairesDAO($this->db);
            $class = $questionnairesDao->get(
                (int) $rawClass['id_module'],
                (int) $rawClass['class_order']
            ); 
        }

        return $class;
    }
    
    /**
     * Adds a new student.
     *
     * @param       Student $student Information about the student
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
        $this->validateStudent($student);
        $this->validatePassword($password);
        $this->withQuery("
            INSERT INTO students
            (name,genre,birthdate,email,password)
            VALUES (?,?,?,?,?)
        ");
        $this->runQueryWithArguments(
            $student->getName(),
            $student->getGenre()->get() == 1,
            $student->getBirthdate()->format("Y-m-d"),
            $student->getEmail(),
            md5($password)
        );
        
        return $this->parseNewStudentResponseQuery($autoLogin);
    }

    private function parseNewStudentResponseQuery($autoLogin)
    {
        if (!$this->hasResponseQuery()) {
            return -1;
        }

        if ($autoLogin) {
            $_SESSION['s_login'] = $this->db->lastInsertId();
        }
            
        return $this->db->lastInsertId();
    }

    private function validateStudent($student)
    {
        if (empty($student)) {
            throw new \InvalidArgumentException("Student cannot be empty");
        }
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
        $this->validateStudent($student);
        $this->withQuery("
            UPDATE  students 
            SET     name = ?, genre = ?, birthdate = ? 
            WHERE   id_student = ?
        ");
        $this->runQueryWithArguments(
            $student->getName(), 
            $student->getGenre()->get(), 
            $student->getBirthdate()->format("Y-m-d"),
            $student->getId()
        );
        
        return $this->hasResponseQuery();
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
        $this->validateStudentId($this->idStudent);
        $this->withQuery("
            DELETE FROM students 
            WHERE id_student = ?
        ");
        $this->runQueryWithArguments($this->idStudent);
        
        return $this->hasResponseQuery();
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
        $this->removeOldPhoto();
        $filename = $this->storeNewPhoto($photo);
        $this->withQuery("
            UPDATE students 
            SET photo = ".$filename."
            WHERE id_student = ?
        ");
        $this->runQueryWithArguments($this->idStudent);
        
        return $this->hasResponseQuery();
    }

    private function removeOldPhoto()
    {
        $imageName = $this->getPhoto();

        if (!empty($imageName)) {
            unlink("assets/img/profile_photos/".$imageName);
        }
    }

    /**
     * Gets photo of the logged in student.
     * 
     * @return      string Photo filename or empty string if student does not
     * have a photo
     */
    private function getPhoto()
    {
        $this->withQuery("
            SELECT  photo
            FROM    students
            WHERE   id_student = ".Student::getLoggedIn($this->db)->getId()
        );
        $this->runQueryWithoutArguments();
    
        return $this->parsePhotoResponseQuery();
    }

    private function parsePhotoResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return "";
        }
        
        return $this->getResponseQuery()['photo'];
    }

    private function storeNewPhoto($photo)
    {
        $filename = null;

        if (!empty($photo)) {
            $filename = FileUtil::storePhoto($photo, "../assets/img/profile_photos/");
        }
        
        return empty($filename) ? null : "'".$filename."'";
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
        $this->validateCurrentPassword($currentPassword);
        $this->validateNewPassword($newPassword);
        $this->withQuery("
            SELECT  COUNT(*) AS correctPassword 
            FROM    students 
            WHERE   id_student = ? AND password = '".md5($currentPassword)."'
        ");
        $this->runQueryWithArguments($this->idStudent);

        return $this->parseUpdatePasswordResponseQuery($newPassword);
    }

    private function validateCurrentPassword($password)
    {
        if (empty($password)) {
            throw new \InvalidArgumentException("Current password cannot be empty");
        }
    }

    private function validateNewPassword($password)
    {
        if (empty($password)) {
            throw new \InvalidArgumentException("New password cannot be empty");
        }
    }

    private function parseUpdatePasswordResponseQuery($newPassword)
    {
        if ($this->getResponseQuery()['correctPassword'] <= 0) {            
            return false;
        }

        $this->withQuery("
            UPDATE  students 
            SET     password = '".md5($newPassword)."' 
            WHERE   id_student = ?
        ");
        $this->runQueryWithArguments($this->idStudent);
        
        return $this->hasResponseQuery();
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
        $this->validateStudentId($this->idStudent);
        $this->withQuery("
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
        $this->runQueryWithArguments($this->idStudent, $this->idStudent);
        
        return $this->getResponseQuery();
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
        $this->validateStudentId($this->idStudent);
        $this->withQuery($this->buildGetPurchasesQuery($limit, $offset));
        $this->runQueryWithArguments($this->idStudent);
        
        return $this->parsePurchasesResponseQuery();
    }

    private function buildGetPurchasesQuery($limit, $offset)
    {
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

        return $query;
    }

    private function parsePurchasesResponseQuery()
    {
        if (!$this->hasResponseQuery()) {            
            return array();
        }

        $purchases = array();

        foreach ($this->getAllResponseQuery() as $purchase) {
            $purchases[] = new Purchase(
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

        return $purchases;
    }
    
    /**
     * Gets total student purchases.
     * 
     * @return      int Total purchases
     */
    public function countPurchases() : int
    {
        $this->withQuery("
            SELECT  COUNT(*) AS total
            FROM    purchases
            WHERE   id_student = ".$this->idStudent
        );
        $this->runQueryWithoutArguments();

        return ((int) $this->getResponseQuery()['total']);
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
        $this->validateStudentId($this->idStudent);
        $this->validateBundleId($idBundle);
        $this->withQuery("
            INSERT INTO purchases
            (id_student, id_bundle, date)
            VALUES (?, ?, NOW())
        ");
        $this->runQueryWithArguments($this->idStudent, $idBundle);
        
        return $this->hasResponseQuery();
    }

    private function validateBundleId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Bundle id cannot be less ".
                                                "than or equal to zero");
        }
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
        $this->validateStudentId($this->idStudent);
        $this->validateBundleId($idBundle);
        $this->withQuery("
            SELECT  COUNT(*) AS has_bundle
            FROM    purchases
            WHERE   id_student = ? AND id_bundle = ?
        ");
        $this->runQueryWithArguments($this->idStudent, $idBundle);
        
        return ($this->getResponseQuery()['has_bundle'] > 0);
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
        $this->validateEmail($email);
        $this->withQuery("
            SELECT  COUNT(*) AS count 
            FROM    students, admins 
            WHERE   email = ?
        ");
        $this->runQueryWithArguments($email);

        return ($this->getResponseQuery()['count'] > 0);
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
        $this->validateStudentId($this->idStudent);
        $this->withQuery("
            DELETE FROM student_historic
            WHERE id_student = ".$this->idStudent
        );
        $this->runQueryWithoutArguments();
        
        return $this->hasResponseQuery();
    }
}