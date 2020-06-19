<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Student;
use models\Admins;


/**
 * Responsible for the behavior of the view {@link login.php}.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class LoginController extends Controller
{      
    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
    /**
     * @Override
     */
    public function index ()
    {
        $params = array(
            'title' => 'Learning platform - Login',
            'error' => false,
            'msg' => ''
        );
        
        if (!empty($_POST['email'])) {
            $students = new Students();
            $admins = new Admins();
            
            if ($students->login($_POST['email'], $_POST['password']) || 
                $admins->login($_POST['email'], $_POST['password'])) {
                header("Location: ".BASE_URL);
                exit;
            }
            
            $params['error'] = true;
            $params['msg'] = "Email and / or password incorrect";
        }
        
        $this->loadView("login", $params);
    }
    
    /**
     * Registers a new student.
     */
    public function register()
    {
        $params = array(
            'title' => 'Learning platform - Register',
            'error' => false,
            'msg' => ''
        );
        
        if (!empty($_POST['email'])) {
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
                
                $params['error'] = true;
                $params['msg'] = "User already registered!";
            } else {
                $params['error'] = true;
                $params['msg'] = "Fill in all fields!";
            }
            
        }
        
        $this->loadView("register", $params);
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
}
