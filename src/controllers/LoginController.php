<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Student;


/**
 * Responsible for the behavior of the view {@link login.php}.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class LoginController extends Controller
{      
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * @Override
     */
    public function index ()
    {
        $header = array(
            'title' => 'Login - Learning platform',
            'styles' => array('login'),
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
            $students = new Students();
            
            if ($students->login($_POST['email'], $_POST['password'])) {
                header("Location: ".BASE_URL);
                exit;
            }
            
            $viewArgs['error'] = true;
            $viewArgs['msg'] = "Email and / or password incorrect";
        }
        
        $this->loadTemplate("login", $viewArgs, false);
    }
    
    /**
     * Registers a new student.
     */
    public function register()
    {
        $header = array(
            'title' => 'Register - Learning platform',
            'styles' => array('ps_style'),
            'description' => "Start learning today",
            'keywords' => array('learning platform', 'register'),
            'robots' => 'index'
        );
        
        $viewArgs = array(
            'header' => $header,
            'scripts' => array('ps_script'),
            'error' => false,
            'msg' => ''
        );
        
        // Checks if registration form has been sent
        if ($this->wasRegistrationFormSent()) {
            // Checks if all fields are filled
            if ($this->isAllFieldsFilled()) {
                $students = new Students();
                
                $student = new Student(
                    $_POST['name'],
                    $_POST['genre'],
                    $_POST['birthdate'],
                    $_POST['email'],
                    $_POST['password']
                );
                
                if ($students->register($student)) {
                    header("Location: ".BASE_URL);
                    exit;
                }
                
                $viewArgs['error'] = true;
                $viewArgs['msg'] = "User already registered!";
            } 
            else {
                $viewArgs['error'] = true;
                $viewArgs['msg'] = "Fill in all fields!";
            }
            
        }
        
        $this->loadTemplate("register", $viewArgs, false);
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
