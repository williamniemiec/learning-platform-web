<?php
namespace panel\controllers;


use panel\config\Controller;
use panel\repositories\pdo\MySqlPDODatabase;
use panel\domain\Admin;
use panel\dao\StudentsDAO;
use panel\dao\CoursesDAO;


/**
 * Responsible for the behavior of the StudentsManagerView.
 */
class StudentsController extends Controller
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Checks whether admin is logged in and if he has authorization to access 
     * the page. If he is not, redirects him to login page.
     */
    public function __construct()
    {
        if (!Admin::isLogged() || !$this->hasLoggedAdminAuthorization(0)) {
            $this->redirectTo("login");
        }
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * @Override
     */
    public function index ()
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $coursesDao = new CoursesDAO($dbConnection);
        $header = array(
            'title' => 'Students manager - Learning platform',
            'styles' => array('StudentsManagerStyle'),
            'robots' => 'index'
        );
        $viewArgs = array(
            'header' => $header,
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'students' => $this->fetchStudents($dbConnection),
            'scripts' => array('StudentsManagerScript'),
            'courses' => $coursesDao->getAll(),
            'selectedCourse' => $this->getSelectedCourse()
        );
        
        $this->loadTemplate("studentsManager/StudentsManagerView", $viewArgs);
    }

    private function getSelectedCourse()
    {
        if (empty($_GET['filter-course'])) {
            return 0;
        }

        return $_GET['filter-course'];
    }

    private function fetchStudents($dbConnection)
    {
        $students = array();
        $studentsDao = new StudentsDAO($dbConnection);
        
        if (empty($_GET['filter-course'])) {
            $students = $studentsDao->getAll();
        }
        else {
            $students = $studentsDao->getAll($_GET['filter-course']);
        }
        
        foreach ($students as $student) {
            $student->setDatabase($dbConnection);
        }

        return $students;
    }
    
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------    
    /**
     * Updates a student.
     * 
     * @param       int $_POST['id_student'] Student id
     * @param       string $_POST['email'] New student email
     * @param       string $_POST['password'] [Optional] New student password
     * 
     * @return      bool If the student has been successfully edited
     * 
     * @apiNote     Must be called using POST request method
     */
    public function editStudent()
    {
        if ($this->getHttpRequestMethod() != 'POST') {
            $this->redirectToRoot();
        }

        if (empty($_POST['email'])) { 
            echo false; 
        }
        
        $dbConnection = new MySqlPDODatabase();
        $studentsDao = new StudentsDAO(
            $dbConnection, 
            Admin::getLoggedIn($dbConnection)
        );
        
        $success = false;
        
        if (empty($_POST['password'])) {
            $success = $studentsDao->update(
                (int) $_POST['id_student'],
                $_POST['email']
            );
        }
        else {
            $success = $studentsDao->update(
                (int) $_POST['id_student'],
                $_POST['email'],
                $_POST['password']
            );
        }

        echo $success;
    }
    
    /**
     * Gets information about a student
     * 
     * @param       int $_POST['id_student'] Student id
     * 
     * @return      string JSON containing student information
     * 
     * @apiNote     Must be called using POST request method
     */
    public function getStudent()
    {
        if ($this->getHttpRequestMethod() != 'POST') {
            $this->redirectToRoot();
        }
        
        if (empty($_POST['id_student'])) { 
            echo json_encode(array()); 
        }
        
        $dbConnection = new MySqlPDODatabase();
        $studentsDao = new StudentsDAO(
            $dbConnection, 
            Admin::getLoggedIn($dbConnection)
        );
        
        echo json_encode($studentsDao->get((int) $_POST['id_student']));
    }
    
    /**
     * Removes a student.
     * 
     * @param       int $_POST['id_student'] Student id
     * 
     * @return      boolean If the student has been successfully removed.
     * 
     * @apiNote     Must be called using POST request method
     */
    public function deleteStudent()
    {
        if ($this->getHttpRequestMethod() != 'POST') {
            $this->redirectToRoot();
        }
        
        if (empty($_POST['id_student'])) { 
            echo false; 
        }
        
        $dbConnection = new MySqlPDODatabase();
        $studentsDao = new StudentsDAO(
            $dbConnection, 
            Admin::getLoggedIn($dbConnection)
        );
        
        echo $studentsDao->delete((int) $_POST['id_student']);
    }
    
    /**
     * Gets information about all bundles that a student has.
     * 
     * @param       int $_POST['id_student'] Student id
     * 
     * @return      string JSON with information about all bundles that a
     * student has
     * 
     * @apiNote     Must be called using POST request method
     */
    public function getBundles()
    {
        if ($this->getHttpRequestMethod() != 'POST') {
            $this->redirectToRoot();
        }
        
        if (empty($_POST['id_student'])) { 
            echo false; 
        }
        
        $dbConnection = new MySqlPDODatabase();
        $studentsDao = new StudentsDAO(
            $dbConnection, 
            Admin::getLoggedIn($dbConnection)
        );
        
        echo json_encode($studentsDao->getBundles((int) $_POST['id_student']));
    }
    
    /**
     * Adds bundles to a student.
     * 
     * @param       int $_POST['id_student'] Student id
     * @param       int $_POST['id_bundle'] Bundle id
     * 
     * @apiNote     Must be called using POST request method
     */
    public function addStudentBundle()
    {
        if ($this->getHttpRequestMethod() != 'POST') {
            $this->redirectToRoot();
        }
        
        if (empty($_POST['id_student']) || empty($_POST['id_bundle'])) { 
            echo false; 
        }
        
        $dbConnection = new MySqlPDODatabase();
        $studentsDao = new StudentsDAO(
            $dbConnection, 
            Admin::getLoggedIn($dbConnection)
        );
        
        $studentsDao->addBundle(
            (int) $_POST['id_student'],
            (int) $_POST['id_bundle']
        );
    }
}
