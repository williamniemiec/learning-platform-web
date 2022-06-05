<?php
namespace controllers;


use core\Controller;
use database\pdo\MySqlPDODatabase;
use models\Admin;


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
            'robots' => 'index'
        );
        
        $viewArgs = array(
            'error' => false,
            'msg' => '',
            'header' => $header
        );
        
        // Checks whether the admin credentials are correct
        if (!empty($_POST['email'])) {
            if (!empty(Admin::login(new MySqlPDODatabase(), $_POST['email'], $_POST['password']))) {
                header("Location: ".BASE_URL);
                exit;
            }
            
            // If an error occurred, display it
            $viewArgs['error'] = true;
            $viewArgs['msg'] = "Email and / or password incorrect";
        }
        
        $this->loadTemplate("LoginView", $viewArgs, false);
    }
}
