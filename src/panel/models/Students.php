<?php
namespace models;

use core\Model;


/**
 * Responsible for managing students.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class Students extends Model
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $id_user;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates students manager.
     *
     * @param       int $id_user [Optional] Student id
     * 
     * @apiNote     It will connect to the database when it is instantiated
     */
    public function __construct($id_user = -1)
    {
        parent::__construct();
        $this->id_user = $id_user;
    }


    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Checks whether a student is logged.
     * 
     * @return      boolean If student is logged
     */
    public static function isLogged()
    {
        return !empty($_SESSION['s_login']);
    }
    
    /**
     * Checks whether student credentials are correct.
     * 
     * @param       string $email Student's email
     * @param       string $pass Student's password
     * 
     * @return      boolean If student credentials are correct
     */
    public function login($email, $pass)
    {
        if (empty($email) || empty($pass)) { return false; }
        
        $sql = $this->db->prepare("
            SELECT id 
            FROM students 
            WHERE email = ? AND password = ?
        ");
        $sql->execute(array($email, md5($pass)));
        
        if ($sql->rowCount() == 0) { return false; }
        
        $_SESSION['s_login'] = $sql->fetch()['id'];
        $this->id_user = $sql->fetch()['id'];
        
        return true;
    }
    
    /**
     * Adds a new student.
     * 
     * @param       Student $student Informations about the student
     * @param       boolean $autologin [Optional] If true, after registration is completed
     * the student will automatically login to the system
     * 
     * @return      int Student id or -1 if the student has not been added
     */
    public function register(Student $student, $autologin = true)
    {
        if ($this->existUser($student)) { return -1; }
        
        $sql = $this->db->prepare("
            INSERT INTO students 
            (name,genre,birthdate,email,password) 
            VALUES (?,?,?,?,?)
        ");
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
    
    /**
     * Gets student name.
     * 
     * @return      string Student's name
     */
    public function getName()
    {
        if ($this->id_user == -1) { return ""; }
        
        $response = "";
        
        $sql = $this->db->query("
            SELECT name 
            FROM students 
            WHERE id = $this->id_user
        ");
        
        if ($sql && $sql->rowCount() > 0) {
            $response = $sql->fetch()['name'];
        }
        
        return $response;
    }
    
    /**
     * Gets information about a student.
     * 
     * @param       int $id_user [Optional] Student id
     * 
     * @return      array Informations about the student
     */
    public function get($id_user = -1)
    {
        if ($this->id_user == -1 && $id_user == -1) { return array(); }
        
        $response = array();
        
        $id_user = $id_user == -1 ? $this->id_user : $id_user;
        
        $sql = $this->db->prepare("
            SELECT * 
            FROM students 
            WHERE id = ?
        ");
        $sql->execute(array($id_user));
        
        if ($sql->rowCount() > 0) {
            $data = $sql->fetch();
            $response = new Student($data['name'], $data['genre'], $data['birthdate'], $data['email']);
        }
        
        return $response;
    }
    
    /**
     * Gets all registered students.
     * 
     * @return      \models\Student[] Information all registered students
     */
    public function getAll()
    {
        $response = array();

        $sql = $this->db->query("
            SELECT * 
            FROM students
        ");
        
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $student) {
                $s = new Student($student['name'], $student['genre'], $student['birthdate'], $student['email']);
                $s->setId($student['id']);
                $response[] = $s;
            }
        }

        return $response;
    }
    
    /**
     * Gets last class watched by the student.
     * 
     * @param       int $id_course Course id
     * 
     * @return      int Class id or -1 if the student has never watched a class
     */
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
    
    /**
     * Checks whether a student exists by its id.
     * 
     * @param       int $id_student Student id
     * 
     * @return      boolean If the student with the specified id exists
     */
    public function exist($id_student)
    {
        if (empty($id_student) || $id_student <= 0) { return false; }
        
        $sql = $this->db->prepare("
            SELECT COUNT(*) AS count 
            FROM students 
            WHERE id = ?
        ");
        $sql->execute(array($id_student));
        
        return $sql->fetch()['count'] > 0;
    }
    
    /**
     * Gets all courses that a student is enrolled.
     * 
     * @param       int $id_student Student id
     * 
     * @return      array Courses that this student is enrolled
     */
    public function getCourses($id_student)
    {
        if (empty($id_student) || $id_student <= 0) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT id_course
            FROM student_course 
            WHERE id_student = ?
        ");
        $sql->execute(array($id_student));

        if ($sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $course) {
                $sql = $this->db->query("
                    SELECT * 
                    FROM courses 
                    WHERE id = ".$course['id_course']
                );
                
                if ($sql->rowCount() > 0) {
                    $response[] = $sql->fetch(\PDO::FETCH_ASSOC);
                }
            }
        }
        
        return $response;
    }
    
    /**
     * Deletes a student.
     * 
     * @param       int $id_student Student id
     * 
     * @return      boolean If student was sucessfully removed 
     */
    public function delete($id_student)
    {
        if (empty($id_student)) { return false; }
        
        $response = false;
        
        $sql = $this->db->prepare("
            DELETE FROM students 
            WHERE id = ?
        ");
        $sql->execute(array($id_student));
        
        if ($sql->rowCount() > 0) {
            $sql = $this->db->prepare("
                DELETE FROM historic
                WHERE id_student = ?
            ");
            $sql->execute(array($id_student));
            
            $sql = $this->db->prepare("
                DELETE FROM student_course 
                WHERE id_student = ?
            ");
            $sql->execute(array($id_student));
            
            $response = true;
        }
        
        return $response;
    }
    
    /**
     * Edits a student.
     * 
     * @param       Student $student Information to be updated
     * 
     * @return      boolean If student was sucessfully edited
     */
    public function edit(Student $student)
    {
        if (empty($student) || empty($student->getId())) { return false; }
        
        if (empty($student->getPassword())) {
            $sql = $this->db->prepare("
                UPDATE students 
                SET name = ?, genre = ?, birthdate = ?, email = ? 
                WHERE id = ?
            ");
            $sql->execute(array(
                $student->getName(),
                $student->getGenre(),
                $student->getBirthdate(),
                $student->getEmail(),
                $student->getId()
            ));
        } else {
            $sql = $this->db->prepare("
                UPDATE students 
                SET name = ?, genre = ?, birthdate = ?, email = ?, password = ? 
                WHERE id = ?
            ");
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
    
    /**
     * Enrolls a student in a course.
     * 
     * @param       int $id_student Student id to be enrolled
     * @param       int $id_course Course id
     * 
     * @return      boolean If the student was sucessfully enrolled
     */
    public function addCourse($id_student, $id_course)
    {
        if (empty($id_student) || empty($id_course)) { return false; }
        
        $sql = $this->db->prepare("
            INSERT INTO student_course 
            SET id_student = ?, id_course = ?
        ");
        $sql->execute(array($id_student, $id_course));
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * De-enroll a student from all courses.
     * 
     * @param       int $id_student Student id
     * 
     * @return      boolean If the student was sucessfully de-enrolled from all courses
     */
    public function deleteAllCourses($id_student)
    {
        if (empty($id_student)) { return false; }
        
        $sql = $this->db->prepare("
            DELETE FROM student_course 
            WHERE id_student = ?
        ");
        $sql->execute(array($id_student));
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Checks whether a student exists by its email.
     *
     * @param       Student $Student Informations about the student
     *
     * @return      boolean If there is already a student with the email used.
     */
    private function existUser($student) 
    {
        $email = $student->getEmail();
        
        if (empty($email)) { return false; }
        
        $sql = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM students
            WHERE email = ?
        ");
        $sql->execute(array($email));

        return $sql->fetch()['count'] > 0;
    }
}