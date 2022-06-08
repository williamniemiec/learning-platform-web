<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Admin;
use domain\Bundle;
use domain\Student;
use domain\enum\GenreEnum;
use domain\Action;
use util\IllegalAccessException;


/**
 * Responsible for managing 'students' table.
 */
class StudentsDAO extends DAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $admin;
    
    
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
        parent::__construct($db);
        $this->admin = $admin;
    }


    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Checks if a student has a course.
     * 
     * @param       int $idStudent Student id 
     * @param       int $idCourse Course id
     * 
     * @return      bool If student has the course or not
     * 
     * @throws      \InvalidArgumentException If student id or course id is 
     * empty, less than or equal to zero
     */
    public function hasCourse(int $idStudent, int $idCourse) : bool
    {
        $this->validateStudentId($idStudent);
        $this->validateCourseId($idCourse);
        $this->withQuery("
            SELECT  count(*) AS hasCourse
            FROM    student_course
            WHERE   id_student = ? AND
                    id_module IN = (SELECT  id_module
                                    FROM    course_modules
                                    WHERE   id_course = ?)
        ");
        
        // Executes query
        $sql->execute(array($idStudent, $idCourse));
        
        return $sql && $sql->fetch()['hasCourse'];
    }

    private function validateStudentId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Student id cannot be empty ".
                                                "or less than or equal to zero");
        }
    }

    private function validateCourseId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Course id cannot be empty or ".
                                                "less than or equal to zero");
        }
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
        $this->validateStudentId($idStudent);
        $response = null;
        
        // Query construction
        $this->withQuery("
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
     * @param       int $idStudent Student id
     *
     * @return      Bundle[] Bundles that the student has
     *
     * @throws      \InvalidArgumentException If student id is empty, less than
     * or equal to zero
     */
    public function getBundles(int $idStudent) : array
    {
        $this->validateStudentId($idStudent);
            
        $response = array();
        
        // Query construction
        $sql = $this->db->query("
            SELECT      bundles.id_bundle, bundles.name, 
                        bundles.price, bundles.logo, bundles.description
            FROM        bundles
                        NATURAL LEFT JOIN bundle_courses 
                        LEFT JOIN purchases USING (id_bundle)
            WHERE       id_student = ".$idStudent."
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
     * @param       int $idStudent Student id
     * 
     * @return      bool If student has been successfully removed 
     * 
     * @throws      \InvalidArgumentException If student id is empty, less than
     * or equal to zerois empty, less than or equal to zero or if admin provided
     * in the constructor is empty
     */
    public function delete(int $idStudent) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0); 
        $this->validateStudentId($idStudent);

        $response = false;
            
        // Query construction
        $this->withQuery("
            DELETE FROM students 
            WHERE id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($idStudent));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->deleteStudent($idStudent);
            $adminsDAO->newAction($action);
            $response = true;
        }
        
        return $response;
    }
    
    /**
     * Updates a student.
     * 
     * @param       int idStudent Student id
     * @param       string $newEmail New student email
     * @param       string $newPassword [Optional] New student password
     * 
     * @return      bool If student has been successfully edited
     * 
     * @throws      \InvalidArgumentException If student id is empty, less than
     * or equal to zero or if email, password or admin id provided
     * in the constructor is empty
     */
    public function update(int $idStudent, string $newEmail, string $newPassword = '') : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0); 
        $this->validateStudentId($idStudent);
        $this->validateEmail($newEmail);

        if (!empty($newPassword)) {
            $this->updatePassword($idStudent, $newPassword);
        }

        $response = false;
            
        // Query construction
        $this->withQuery("
            UPDATE  students 
            SET     email = ?
            WHERE   id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($newEmail, $idStudent));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateStudent($idStudent);
            $adminsDAO->newAction($action);
            $response = true;
        }
        
        return $response;
    }

    private function validateEmail($email)
    {
        if (empty($email)) {
            throw new \InvalidArgumentException("New email cannot be empty");
        }
    }
    
    /**
     * Updates a student password.
     *
     * @param       int $idStudent Student id
     * @param       string $newPassword New password
     *
     * @return      bool If password has been successfully updated
     *
     * @throws      \InvalidArgumentException If student id is empty, less than
     * or equal to zero or if password or admin id provided in the constructor
     * is empty
     */
    public function updatePassword(int $idStudent, string $newPassword) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0);   
        $this->validateStudentId($idStudent);
        $this->validatePassword($newPassword);

        $response = false;
            
        // Query construction
        $sql = $this->db->query("
            UPDATE students
            SET password = MD5(".$newPassword.")
            WHERE id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($idStudent));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateStudent($idStudent);
            $adminsDAO->newAction($action);
            $response = true;
        }
        
        return $response;
    }

    private function validatePassword($password)
    {
        if (empty($password)) {
            throw new \InvalidArgumentException("New email cannot be empty");
        }
    }
    
    /**
     * Adds a bundle to current student.
     * 
     * @param       int $idStudent Student id
     * @param       int $idBundle Bundle id to be added
     * 
     * @return      bool If bundle has been successfully added
     * 
     * @throws      \InvalidArgumentException If student id, bundle id is empty,
     * less than or equal to zero or admin provided in the constructor is empty
     */
    public function addBundle(int $idStudent, int $idBundle) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0); 
        $this->validateStudentId($idStudent);
        $this->validateBundleId($idBundle);
        
        $response = false;
            
        // Query construction
        $this->withQuery("
            INSERT INTO purchases 
            (id_student, id_bundle, date)
            VALUES (?, ?, NOW())
        ");
        
        // Executes query
        $sql->execute(array($idStudent, $idBundle));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateStudent($idStudent);
            $adminsDAO->newAction($action);
            $response = true;
        }
        
        return $response;
    }

    private function validateBundleId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Bundle id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }
    
    /**
     * Removes a bundle to current student.
     *
     * @param       int idStudent Student id
     * @param       int idBundle Bundle id to be added
     *
     * @return      bool If bundle has been successfully added
     *
     * @throws      \InvalidArgumentException If student id, bundle id is 
     * empty, less than or equal to zero or admin provided in the 
     * constructor is empty
     */
    public function removeBundle(int $idStudent, int $idBundle) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0); 
        $this->validateStudentId($idStudent);
        $this->validateBundleId($idBundle);
            
        $response = false;
            
        // Query construction
        $this->withQuery("
            DELETE FROM purchases
            WHERE   id_student = ? AND id_bundle = ?
        ");
        
        // Executes query
        $sql->execute(array($idStudent, $idBundle));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateStudent($idStudent);
            $adminsDAO->newAction($action);
            $response = true;
        }
        
        return $response;
    }
}