<?php
namespace controllers;


use config\Controller;
use repositories\pdo\MySqlPDODatabase;
use domain\Student;
use dao\StudentsDAO;


/**
 * Responsible for the behavior of the LoginView.
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
        if (Student::is_logged()) {
            $this->redirect_to_root();
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
        $db_connection = new MySqlPDODatabase();
        
        $header = array(
            'title' => 'Login - Learning platform',
            'styles' => array('LoginStyle'),
            'description' => "Start learning today",
            'keywords' => array('learning platform', 'login'),
            'robots' => 'index'
        );
        
        $view_args = array(
            'header' => $header,
            'error' => false,
            'msg' => ''
        );
        
        // Checks if login form has been sent
        if (!empty($_POST['email'])) {
            if (!empty(Student::login($db_connection, $_POST['email'], $_POST['password']))) {
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
            
            $view_args['error'] = true;
            $view_args['msg'] = "Email and / or password incorrect";
        }
        
        $this->load_template("LoginView", $view_args, false);
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
        
        $view_args = array(
            'header' => $header,
            'scripts' => array(),
            'error' => false,
            'msg' => ''
        );
        
        // Checks if registration form has been sent
        if ($this->was_registration_form_sent()) {
            // Checks if all fields are filled
            if ($this->is_all_fields_filled()) {
                $students_dao = new StudentsDAO(new MySqlPDODatabase());
                
                if ($students_dao->is_email_in_use($_POST['email'])) {
                    $view_args['error'] = true;
                    $view_args['msg'] = "Email is already being used";
                }
                else {
                    $student = new Student(
                        $_POST['name'],
                        $_POST['genre'],
                        $_POST['birthdate'],
                        $_POST['email'],
                        $_POST['password']
                    );
                    
                    if ($students_dao->register($student)) {
                        $this->redirect_to_root();
                        exit;
                    }
                    
                    $view_args['error'] = true;
                    $view_args['msg'] = "Error when registering";
                }
            } 
            else {
                $view_args['error'] = true;
                $view_args['msg'] = "Fill in all fields!";
            }
            
        }
        
        $this->load_template("RegisterView", $view_args, false);
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
    private function is_all_fields_filled()
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
    private function was_registration_form_sent()
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
