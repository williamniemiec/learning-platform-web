<?php
/**
 * Copyright (c) William Niemiec.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

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
        if (Student::isLogged()) {
            $this->redirectToRoot();
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
        
        if ($this->hasLoginFormBeenSent()) {
            if ($this->hasLogged()) {
                $this->redirectLoggedUser();
            }
            
            $viewArgs['error'] = true;
            $viewArgs['msg'] = "Email and / or password incorrect";
        }
        
        $this->loadTemplate("LoginView", $viewArgs, false);
    }

    private function hasLoginFormBeenSent()
    {
        return  !empty($_POST['email']);
    }

    private function hasLogged()
    {
        $dbConnection = new MySqlPDODatabase();
        $loggedStudent = Student::login(
            $dbConnection, 
            $_POST['email'], 
            $_POST['password']
        );
        
        return !empty($loggedStudent);
    }

    private function redirectLoggedUser()
    {
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
        
        if ($this->hasRegistrationFormBeenSent()) {
            if ($this->isAllFieldsFilled()) {
                $studentsDao = new StudentsDAO(new MySqlPDODatabase());
                
                if ($studentsDao->isEmailInUse($_POST['email'])) {
                    $viewArgs['error'] = true;
                    $viewArgs['msg'] = "Email is already being used";
                }
                else {
                    $student = new Student(
                        $_POST['name'],
                        $_POST['genre'],
                        $_POST['birthdate'],
                        $_POST['email'],
                        null
                    );
                    
                    if ($studentsDao->register($student, $_POST['password'])) {
                        $this->redirectToRoot();
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
    private function hasRegistrationFormBeenSent()
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
