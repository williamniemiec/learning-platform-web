<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Admin;
use models\Bundle;
use models\Student;
use models\Course;
use models\util\IllegalAccessException;
use models\enum\GenreEnum;


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
    private $admin;
    private $db;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'students' table manager.
     *
     * @param       Database $db Database
     * @param       int $admin [Optional] Admin logged in
     */
    public function __construct(Database $db, Admin $admin = null)
    {
        $this->db = $db->getConnection();
        $this->admin = $admin;
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
    public function get(int $id_student) : ?Student
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        $response = null;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  * 
            FROM    students
            WHERE   id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($id_student));
        
        // Parses results
        if (!empty($sql) && $sql->rowCount() > 0) {
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
     * Gets all registered students.
     * 
     * @param       int $id_course [Optional] Filters students who have a 
     * course with the provided id
     * @param       int $limit [Optional] Maximum results that will be returned
     * 
     * @return      \models\Student[] All registered students or empty 
     * array if there are no registered students
     */
    public function getAll(int $id_course = -1, int $limit = -1) : array
    {
        $response = array();

        // Query construction
        $query = "
            SELECT  * 
            FROM    students
        ";
        
        if ($id_course > 0) {
            $query .= " 
                WHERE id_student IN (SELECT id_student
                                     FROM   purchases 
                                            NATURAL JOIN bundle_courses
                                            NATURAL JOIN courses
                                     WHERE  id_course = ".$id_course.")
            ";
        }
        
        // Executes query
        $sql = $this->db->query($query);
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $student) {
                $response[] = new Student(
                    (int)$student['id_student'],
                    $student['name'], 
                    new GenreEnum($student['genre']), 
                    new \DateTime($student['birthdate']), 
                    $student['email']
                );
            }
        }

        return $response;
    }
    
    /**
     * Gets all bundles that a student has.
     *
     * @param       int $id_student Student id
     *
     * @return      Bundle[] Bundles that the student has
     *
     * @throws      \InvalidArgumentException If student id is empty, less than
     * or equal to zero
     */
    public function getBundles(int $id_student) : array
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
            
            $response = array();
            
            // Query construction
            $sql = $this->db->query("
            SELECT      bundles.id_bundle, bundles.name, 
                        bundles.price, bundles.logo, bundles.description
            FROM        bundles
                        NATURAL LEFT JOIN bundle_courses 
                        LEFT JOIN purchases USING (id_bundle)
            WHERE       id_student = ".$id_student."
            GROUP BY    bundles.id_bundle, bundles.name, 
                        bundles.price, bundles.logo, bundles.description
        ");
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $bundle) {
                $response[] = new Bundle(
                    (int)$bundle['id_bundle'],
                    $bundle['name'],
                    (float)$bundle['price'],
                    $bundle['logo'],
                    $bundle['description']
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
     * @throws      \InvalidArgumentException If student id is empty, less than
     * or equal to zerois empty, less than or equal to zero or if admin provided
     * in the constructor is empty
     */
    public function delete(int $id_student) : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
            if ($this->admin->getAuthorization()->getLevel() != 0)
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
     * @throws      \InvalidArgumentException If student id is empty, less than
     * or equal to zero or if email, password or admin id provided
     * in the constructor is empty
     */
    public function update(int $id_student, string $newEmail, string $newPassword = '') : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0)
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
     * @throws      \InvalidArgumentException If student id is empty, less than
     * or equal to zero or if password or admin id provided in the constructor
     * is empty
     */
    public function updatePassword(int $id_student, string $newPassword) : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0)
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
     * @throws      \InvalidArgumentException If student id, bundle id is empty,
     * less than or equal to zero or admin provided in the constructor is empty
     */
    public function addBundle(int $id_student, int $id_bundle) : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0)
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
     * @throws      \InvalidArgumentException If student id, bundle id is 
     * empty, less than or equal to zero or admin provided in the 
     * constructor is empty
     */
    public function removeBundle(int $id_student, int $id_bundle) : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0)
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