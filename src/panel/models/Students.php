<?php
namespace models;

use core\Model;
use models\obj\Student;
use models\obj\Course;


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
    
    public function hasCourse($id_student, $id_course)
    {
        $sql = $this->db->prepare("
            SELECT  count(*) AS hasCourse
            FROM    student_course
            WHERE   id_student = ? AND
                    id_module IN = (SELECT  id_module
                                    FROM    course_modules
                                    WHERE   id_course = ?)
        ");
        
        $sql->execute(array($id_student, $id_course));
        
        return $sql->rowCount() > 0;
    }
    
    
    
    /**
     * Gets information about a student.
     * 
     * @param       int $id_user [Optional] Student id
     * 
     * @return      array Informations about the student
     */
    public function get($id_student)
    {
        $response = null;
        
        $sql = $this->db->prepare("
            SELECT  *
            FROM    students
            WHERE   id_student = ?
        ");
        $sql->execute(array($id_student));
        
        if ($sql->rowCount() > 0) {
            $student = $sql->fetch();
            
            $response = new Student(
                $student['name'],
                $student['genre'],
                $student['birthdate'],
                $student['email'],
                $student['photo']
            );
        }
        
        return $response;
    }
    
    /**
     * Gets all registered students.
     * 
     * @return      \models\obj\Student[] Information all registered students
     */
    public function getAll($courseName = '')
    {
        $response = array();

        $query = "
            SELECT  * 
            FROM    students
        ";
        
        if (!empty($courseName)) {
            $query .= " 
                WHERE id_student IN (SELECT id_student
                                     FROM   purchases NATURAL JOIN bundle_courses
                                            NATURAL JOIN courses
                                     WHERE  name LIKE ?)
            ";
            
            $sql = $this->db->prepare($query);
            $sql->execute(array($courseName));
        }
        else {
            $sql = $this->db->query($query);
        }
        
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $student) {
                $response[] = new Student(
                    $student['id'],
                    $student['name'], 
                    $student['genre'], 
                    $student['birthdate'], 
                    $student['email']
                );
            }
        }

        return $response;
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
        
        $sql = $this->db->query("
            SELECT  *
            FROM    courses
            WHERE   id_course IN (SELECT id_course
                                  FROM bundle_courses
                                  WHERE id_bundle IN (SELECT  id_bundle
                                                      FROM    purchases
                                                      WHERE   id_student = ?))
        ");
        
        $sql->execute(array($id_student));

        if ($sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $course) {
                $response[] = new Course(
                    $course['id_course'], 
                    $course['name']
                );
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
            WHERE id_student = ?
        ");
        $sql->execute(array($id_student));
        
        return $sql->rowCount() > 0;
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
        
            $sql = $this->db->prepare("
                UPDATE students 
                (name, genre, birthdate)
                VALUES (?, ?, ?)
                WHERE id_student = ?
            ");
            
            $sql->execute(array(
                $student->getName(),
                $student->getGenre(),
                $student->getBirthdate(),
                $student->getId()
            ));
        
        return $sql->rowCount() > 0;
    }
    
    public function updatePassword($id_student, $newPassword)
    {
        $sql = $this->db->query("
            UPDATE students
            SET password = MD5(".$newPassword.")
            WHERE id_student = ?
        ");
        
        $sql->execute(array($id_student));
        
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
    public function addBundle($id_student, $id_bundle)
    {
        if (empty($id_student) || empty($id_bundle)) { return false; }
        
        $sql = $this->db->prepare("
            INSERT INTO purchases 
            (id_student, id_bundle, date)
            VALUES (?, ?, NOW())
        ");
        
        $sql->execute(array($id_student, $id_bundle));
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * De-enroll a student from all courses.
     * 
     * @param       int $id_student Student id
     * 
     * @return      boolean If the student was sucessfully de-enrolled from all courses
     */
//     public function deleteAllBundles($id_student)
//     {
//         if (empty($id_student)) { return false; }
        
//         $sql = $this->db->prepare("
//             DELETE FROM purchases 
//             WHERE id_student = ?
//         ");
//         $sql->execute(array($id_student));
        
//         return $sql->rowCount() > 0;
//     }
    
    /**
     * Checks whether a student exists by its email.
     *
     * @param       Student $Student Informations about the student
     *
     * @return      boolean If there is already a student with the email used.
     */
//     private function existUser($student) 
//     {
//         $email = $student->getEmail();
        
//         if (empty($email)) { return false; }
        
//         $sql = $this->db->prepare("
//             SELECT COUNT(*) as count 
//             FROM students
//             WHERE email = ?
//         ");
//         $sql->execute(array($email));

//         return $sql->fetch()['count'] > 0;
//     }
}