<?php
namespace panel\controllers;


use panel\config\Controller;
use panel\models\Student;
use panel\models\Admin;
use panel\database\pdo\MySqlPDODatabase;
use panel\models\dao\StudentsDAO;
use panel\models\dao\CoursesDAO;


/**
 * Responsible for the behavior of the view {@link studentsManager/students_manager.php}.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
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
        if (!Admin::isLogged() || 
            !(Admin::getLoggedIn(new MySqlPDODatabase())->getAuthorization()->getLevel() == 0)) {
            header("Location: ".BASE_URL."login");
            exit;
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
        $studentsDAO = new StudentsDAO($dbConnection);
        $coursesDAO = new CoursesDAO($dbConnection);
        $selectedCourse = 0;
        
        if (!empty($_GET['filter-course'])) {
            $students = $studentsDAO->getAll($_GET['filter-course']);
            $selectedCourse = $_GET['filter-course'];
        }
        else {
            $students = $studentsDAO->getAll();
        }
        
        foreach ($students as $student) {
            $student->setDatabase($dbConnection);
        }
        
        $header = array(
            'title' => 'Students manager - Learning platform',
            'styles' => array('StudentsManagerStyle'),
            'robots' => 'index'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'students' => $students,
            'header' => $header,
            'scripts' => array('StudentsManagerScript'),
            'courses' => $coursesDAO->getAll(),
            'selectedCourse' => $selectedCourse
        );
        
        $this->loadTemplate("studentsManager/StudentsManagerView", $viewArgs);
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
    public function edit_student()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        if (empty($_POST['email'])) { echo false; }
        
        $dbConnection = new MySqlPDODatabase();
        $studentsDAO = new StudentsDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        $response = false;
        
        if (empty($_POST['passowrd'])) {
            $response = $studentsDAO->update(
                (int)$_POST['id_student'],
                $_POST['email']
            );
        }
        else {
            $response = $studentsDAO->update(
                (int)$_POST['id_student'],
                $_POST['email'],
                $_POST['passowrd']
            );
        }

        echo $response;
    }
    
    /**
     * Gets informations about a student
     * 
     * @param       int $_POST['id_student'] Student id
     * 
     * @return      string JSON containing student information
     * 
     * @apiNote     Must be called using POST request method
     */
    public function get_student()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        if (empty($_POST['id_student'])) { echo json_encode(array()); }
        
        $dbConnection = new MySqlPDODatabase();
        $studentsDAO = new StudentsDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        
        echo json_encode($studentsDAO->get((int)$_POST['id_student']));
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
    public function delete_student()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        if (empty($_POST['id_student'])) { echo false; }
        
        $dbConnection = new MySqlPDODatabase();
        $studentsDAO = new StudentsDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        
        echo $studentsDAO->delete((int)$_POST['id_student']);
    }
    
    /**
     * Gets informations about all bundles that a student has.
     * 
     * @param       int $_POST['id_student'] Student id
     * 
     * @return      string JSON with informations about all bundles that a
     * student has
     * 
     * @apiNote     Must be called using POST request method
     */
    public function get_bundles()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        if (empty($_POST['id_student'])) { echo false; }
        
        $dbConnection = new MySqlPDODatabase();
        $studentsDAO = new StudentsDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        
        echo json_encode($studentsDAO->getBundles((int)$_POST['id_student']));
    }
    
    /**
     * Adds bundles to a student.
     * 
     * @param       int $_POST['id_student'] Student id
     * @param       int $_POST['id_bundle'] Bundle id
     * 
     * @apiNote     Must be called using POST request method
     */
    public function add_student_bundle()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        if (empty($_POST['id_student']) || empty($_POST['id_bundle'])) { echo false; }
        
        $dbConnection = new MySqlPDODatabase();
        $studentsDAO = new StudentsDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        
        $studentsDAO->addBundle(
            (int)$_POST['id_student'],
            (int)$_POST['id_bundle']
        );
    }
}
