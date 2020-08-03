<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Student;
use models\Course;
use models\util\IllegalAccessException;


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
    private $id_admin;
    private $db;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'students' table manager.
     *
     * @param       Database $db Database
     * @param       int $id_admin [Optional] Admin id logged in
     */
    public function __construct(Database $db, int $id_admin = -1)
    {
        $this->db = $db->getConnection();
        $this->id_admin = $id_admin;
    }


    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Checks if a student has a course.
     * 
     * @param       int $id_student Student id 
     * @param       int $id_course Course id
     * 
     * @return      bool If student has the course or not
     * 
     * @throws      \InvalidArgumentException If student id or course id is 
     * empty, less than or equal to zero
     */
    public function hasCourse(int $id_student, int $id_course) : bool
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Course id cannot be empty ".
                "or less than or equal to zero");
            
        // Query construction
        $sql = $this->db->prepare("
            SELECT  count(*) AS hasCourse
            FROM    student_course
            WHERE   id_student = ? AND
                    id_module IN = (SELECT  id_module
                                    FROM    course_modules
                                    WHERE   id_course = ?)
        ");
        
        // Executes query
        $sql->execute(array($id_student, $id_course));
        
        return $sql && $sql->fetch()['hasCourse'];
    }
    
    /**
     * Gets information about the logged in student.
     *
     * @param       int $id_student Student id
     *
     * @return      Student Informations about the student or null if student 
     * does not exist
     * 
     * @throws      \InvalidArgumentException If student id is empty, less than
     * or equal to zero
     */
    public function get(int $id_student) : array
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
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
                $student['name'], 
                $student['genre'], 
                new \DateTime($student['birthdate']), 
                $student['email'],
                $student['photo'] 
            );
        }
        
        return $response;
    }
    
    /**
     * Gets all registered students.
     * 
     * @param       string $courseName [Optional] Filters students who have a 
     * course with a certain name
     * @param       int $limit [Optional] Maximum results that will be returned
     * 
     * @return      \models\Student[] All registered students or empty 
     * array if there are no registered students
     */
    public function getAll($courseName = '', $limit = -1)
    {
        $response = array();

        // Query construction
        $query = "
            SELECT  * 
            FROM    students
        ";
        
        if (!empty($courseName)) {
            $query .= " 
                WHERE id_student IN (SELECT id_student
                                     FROM   purchases 
                                            NATURAL JOIN bundle_courses
                                            NATURAL JOIN courses
                                     WHERE  name LIKE ?)
            ";
            
            // Executes query
            $sql = $this->db->prepare($query);
            $sql->execute(array($courseName));
        }
        else {
            // Executes query
            $sql = $this->db->query($query);
        }
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $student) {
                $response[] = new Student(
                    $student['id'],
                    $student['name'], 
                    $student['genre'], 
                    new \DateTime($student['birthdate']), 
                    $student['email']
                );
            }
        }

        return $response;
    }
    
    /**
     * Gets all courses that a student has.
     * 
     * @param       int $id_student Student id
     * 
     * @return      Course[] Courses that this student has
     * 
     * @throws      \InvalidArgumentException If student id is empty, less than
     * or equal to zero
     */
    public function getCourses(int $id_student) : array
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        $response = array();
        
        // Query construction
        $sql = $this->db->query("
            SELECT  *
            FROM    courses
            WHERE   id_course IN (SELECT id_course
                                  FROM bundle_courses
                                  WHERE id_bundle IN (SELECT  id_bundle
                                                      FROM    purchases
                                                      WHERE   id_student = ?))
        ");
        
        // Executes query
        $sql->execute(array($id_student));

        // Parses results
        if ($sql && $sql->rowCount() > 0) {
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
     * @return      bool If student has been successfully removed 
     * 
     * @throws      \InvalidArgumentException If student id or admin id provided
     * in the constructor is empty, less than or equal to zerois empty, less than
     * or equal to zero
     */
    public function delete(int $id_student) : bool
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->getAuthorization()->getLevel() != 0)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");

        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM students 
            WHERE id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($id_student));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Updates a student.
     * 
     * @param       int $id_student Student id
     * @param       string $newEmail New student email
     * @param       string $newPassword [Optional] New student password
     * 
     * @return      bool If student has been successfully edited
     * 
     * @throws      \InvalidArgumentException If student id or admin id provided
     * in the constructor is empty, less than or equal to zerois empty, less than
     * or equal to zero or if email is empty
     */
    public function update(int $id_student, string $newEmail, string $newPassword = '') : bool
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->getAuthorization()->getLevel() != 0)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
        
            if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($newEmail))
            throw new \InvalidArgumentException("New email cannot be empty");

        if (!empty($newPassword))
            $this->updatePassword($id_student, $newPassword);

        // Query construction
        $sql = $this->db->prepare("
            UPDATE  students 
            SET     email = ?
            WHERE   id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($newEmail, $id_student));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Updates a student password.
     *
     * @param       int $id_student Student id
     * @param       string $newPassword New password
     *
     * @return      bool If password has been successfully updated
     *
     * @throws      \InvalidArgumentException If student id or admin id provided
     * in the constructor is empty, less than or equal to zerois empty, less than
     * or equal to zero or if password is empty
     */
    public function updatePassword(int $id_student, string $newPassword) : bool
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->getAuthorization()->getLevel() != 0)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($newPassword))
            throw new \InvalidArgumentException("New password cannot be empty");
            
        // Query construction
        $sql = $this->db->query("
            UPDATE students
            SET password = MD5(".$newPassword.")
            WHERE id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($id_student));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Adds a bundle to current student.
     * 
     * @param       int $id_student Student id
     * @param       int $id_bundle Bundle id to be added
     * 
     * @return      bool If bundle has been successfully added
     * 
     * @throws      \InvalidArgumentException If student id, bundle id or admin
     * id provided in the constructor is empty, less than or equal to zero
     */
    public function addBundle(int $id_student, int $id_bundle) : bool
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->getAuthorization()->getLevel() != 0)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($id_bundle) || $id_bundle <= 0)
            throw new \InvalidArgumentException("Bundle id cannot be empty ".
                "or less than or equal to zero");
        
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO purchases 
            (id_student, id_bundle, date)
            VALUES (?, ?, NOW())
        ");
        
        // Executes query
        $sql->execute(array($id_student, $id_bundle));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Removes a bundle to current student.
     *
     * @param       int $id_student Student id
     * @param       int $id_bundle Bundle id to be added
     *
     * @return      bool If bundle has been successfully added
     *
     * @throws      \InvalidArgumentException If student id, bundle id or admin
     * id provided in the constructor is empty, less than or equal to zero
     */
    public function removeBundle(int $id_student, int $id_bundle) : bool
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->getAuthorization()->getLevel() != 0)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($id_bundle) || $id_bundle <= 0)
            throw new \InvalidArgumentException("Bundle id cannot be empty ".
                "or less than or equal to zero");
            
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM purchases
            WHERE   id_student = ? AND id_bundle = ?
        ");
        
        // Executes query
        $sql->execute(array($id_student, $id_bundle));
        
        return $sql && $sql->rowCount() > 0;
    }
}