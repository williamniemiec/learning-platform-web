<?php
declare (strict_types=1);

namespace panel\dao;


use panel\repositories\Database;
use panel\domain\Admin;
use panel\domain\Bundle;
use panel\domain\Student;
use panel\domain\enum\GenreEnum;
use panel\domain\Action;
use panel\util\IllegalAccessException;


/**
 * Responsible for managing 'students' table.
 */
class StudentsDAO extends DAO
{
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
        parent::__construct($db, $admin);
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
        $this->runQueryWithArguments($idStudent, $idCourse);
        
        return $this->parseHasCourseResponseQuery();
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

    private function parseHasCourseResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        return ($this->getResponseQuery()['hasCourse'] > 0);
    }
    
    /**
     * Gets information about the logged in student.
     *
     * @param       int idStudent Student id
     *
     * @return      Student Information about the student or null if student 
     * does not exist
     * 
     * @throws      \InvalidArgumentException If student id is empty, less than
     * or equal to zero
     */
    public function get(int $idStudent) : ?Student
    {
        $this->validateStudentId($idStudent);
        $this->withQuery("
            SELECT  * 
            FROM    students
            WHERE   id_student = ?
        ");
        $this->runQueryWithArguments($idStudent);

        return $this->parseGetResponseQuery();
    }

    private function parseGetResponseQuery()
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
     * Gets all registered students.
     * 
     * @param       int $idCourse [Optional] Filters students who have a 
     * course with the provided id
     * @param       int $limit [Optional] Maximum results that will be returned
     * 
     * @return      Student[] All registered students or empty 
     * array if there are no registered students
     */
    public function getAll(int $idCourse = -1, int $limit = -1) : array
    {
        $this->withQuery($this->buildGetAllQuery($idCourse));
        $this->runQueryWithoutArguments();
        
        return $this->parseGetAllResponseQuery();
    }

    private function buildGetAllQuery($idCourse)
    {
        $query = "
            SELECT  * 
            FROM    students
        ";
        
        if ($idCourse > 0) {
            $query .= " 
                WHERE id_student IN (SELECT id_student
                                     FROM   purchases 
                                            NATURAL JOIN bundle_courses
                                            NATURAL JOIN courses
                                     WHERE  id_course = ".$idCourse.")
            ";
        }

        return $query;
    }

    private function parseGetAllResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $students = array();

        foreach ($this->getAllResponseQuery() as $student) {
            $students[] = new Student(
                (int) $student['id_student'],
                $student['name'], 
                new GenreEnum($student['genre']), 
                new \DateTime($student['birthdate']), 
                $student['email']
            );
        }

        return $students;
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
        $this->withQuery("
            SELECT      bundles.id_bundle, bundles.name, 
                        bundles.price, bundles.logo, bundles.description
            FROM        bundles
                        NATURAL LEFT JOIN bundle_courses 
                        LEFT JOIN purchases USING (id_bundle)
            WHERE       id_student = ".$idStudent."
            GROUP BY    bundles.id_bundle, bundles.name, 
                        bundles.price, bundles.logo, bundles.description
        ");
        $this->runQueryWithoutArguments();

        return $this->parseGetAllBundlesResponseQuery();
    }

    private function parseGetAllBundlesResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $bundles = array();

        foreach ($this->getAllResponseQuery() as $bundle) {
            $bundles[] = new Bundle(
                (int) $bundle['id_bundle'],
                $bundle['name'],
                (float) $bundle['price'],
                $bundle['logo'],
                $bundle['description']
            );
        }

        return $bundles;
    }
    
    /**
     * Deletes a student.
     * 
     * @param       int $idStudent Student id
     * 
     * @return      bool If student has been successfully removed 
     * 
     * @throws      \InvalidArgumentException If student id is empty, less than
     * or equal to zero is empty, less than or equal to zero or if admin 
     * provided in the constructor is empty
     */
    public function delete(int $idStudent) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0); 
        $this->validateStudentId($idStudent);
        $this->withQuery("
            DELETE FROM students 
            WHERE id_student = ?
        ");
        $this->runQueryWithArguments($idStudent);
        
        return $this->parseDeleteResponseQuery($idStudent);
    }

    private function parseDeleteResponseQuery($studentId)
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->deleteStudent($studentId);
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);

        return true;
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
        
        $this->withQuery("
            UPDATE  students 
            SET     email = ?
            WHERE   id_student = ?
        ");
        $this->runQueryWithArguments($newEmail, $idStudent);

        return $this->parseUpdateResponseQuery($idStudent);
    }

    private function validateEmail($email)
    {
        if (empty($email)) {
            throw new \InvalidArgumentException("New email cannot be empty");
        }
    }

    private function parseUpdateResponseQuery($studentId)
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->updateStudent($studentId);
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);

        return true;
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
        $this->withQuery("
            UPDATE students
            SET password = MD5(".$newPassword.")
            WHERE id_student = ?
        ");
        $this->runQueryWithArguments($idStudent);
        
        return $this->parseUpdateResponseQuery($idStudent);
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
        $this->withQuery("
            INSERT INTO purchases 
            (id_student, id_bundle, date)
            VALUES (?, ?, NOW())
        ");
        $this->runQueryWithArguments($idStudent, $idBundle);
        
        return $this->parseUpdateResponseQuery($idStudent);
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
        $this->withQuery("
            DELETE FROM purchases
            WHERE   id_student = ? AND id_bundle = ?
        ");
        $this->runQueryWithArguments($idStudent, $idBundle);
        
        return $this->parseUpdateResponseQuery($idStudent);
    }
}