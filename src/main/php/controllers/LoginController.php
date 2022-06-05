<?php
namespace controllers;


use config\Controller;
use repositories\pdo\MySqlPDODatabase;
use domain\Student;
use dao\StudentsDAO;


/**
 * Responsible for the behavior of the view {@link login.php}.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class LoginController extends Controller
{      
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Checks if student is logged in. If yes, redirects him to home page.
     */
    public function __construct()
    {
        if (Student::isLogged()) {
            header("Location: ".BASE_URL);
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
        
        $header = array(
            'title' => 'Login - Learning platform',
            'styles' => array('LoginStyle'),
            'description' => "Start learning today",
            'keywords' => array('learning platform', 'login'),
            'robots' => 'index'
        );
        
        $viewArgs = array(
            'header' => $header,
            'error' => false,
            'msg' => ''
        );
        
        // Checks if login form has been sent
        if (!empty($_POST['email'])) {
            if (!empty(Student::login($dbConnection, $_POST['email'], $_POST['password']))) {
                // If login was successful, redirects him
                if (empty($_SESSION['redirect'])) {
                    header("Location: ".BASE_URL."courses");
                }
                else {
                    $redirect = $_SESSION['redirect'];
                    unset($_SESSION['redirect']);
                    header("Location: ".$redirect);
                }
                
                exit;
            }
            
            $viewArgs['error'] = true;
            $viewArgs['msg'] = "Email and / or password incorrect";
        }
        
        $this->loadTemplate("LoginView", $viewArgs, false);
    }
    
    /**
     * Registers a new student.
     */
    public function register()
    {
        $header = array(
            'title' => 'Register - Learning platform',
            'styles' => array(),
            'description' => "Start learning today",
            'keywords' => array('learning platform', 'register'),
            'robots' => 'index'
        );
        
        $viewArgs = array(
            'header' => $header,
            'scripts' => array(),
            'error' => false,
            'msg' => ''
        );
        
        // Checks if registration form has been sent
        if ($this->wasRegistrationFormSent()) {
            // Checks if all fields are filled
            if ($this->isAllFieldsFilled()) {
                $studentsDAO = new StudentsDAO(new MySqlPDODatabase());
                
                if ($studentsDAO->isEmailInUse($_POST['email'])) {
                    $viewArgs['error'] = true;
                    $viewArgs['msg'] = "Email is already being used";
                }
                else {
                    $student = new Student(
                        $_POST['name'],
                        $_POST['genre'],
                        $_POST['birthdate'],
                        $_POST['email'],
                        $_POST['password']
                    );
                    
                    if ($studentsDAO->register($student)) {
                        header("Location: ".BASE_URL);
                        exit;
                    }
                    
                    $viewArgs['error'] = true;
                    $viewArgs['msg'] = "Error when registering";
                }
            } 
            else {
                $viewArgs['error'] = true;
                $viewArgs['msg'] = "Fill in all fields!";
            }
            
        }
        
        $this->loadTemplate("RegisterView", $viewArgs, false);
    }
    
    /**
     * Checks if all required fields are filled. The required fields are:
     * <ul>
     *  <li>Name</li>
     *  <li>Genre</li>
     *  <li>Birthdate</li>
     *  <li>Email</li>
     *  <li>Password</li>
     * </ul>
     * 
     * @return      boolean If all required fields are filled
     */
    private function isAllFieldsFilled()
    {
        return (
            isset($_POST['name']) &&
            isset($_POST['genre']) &&
            isset($_POST['birthdate']) &&
            isset($_POST['email']) &&
            isset($_POST['password'])
        );
    }
    
    /**
     * Checks if registration form was sent.
     * 
     * @return      boolean If registration form was sent
     */
    private function wasRegistrationFormSent()
    {
        return (
            !empty($_POST['name']) ||
            !empty($_POST['genre']) ||
            !empty($_POST['birthdate']) ||
            !empty($_POST['email']) ||
            !empty($_POST['password'])
        );
    }
}
