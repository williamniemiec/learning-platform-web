<?php
/**
 * Copyright (c) William Niemiec.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

namespace panel\controllers;


use panel\config\Controller;
use panel\repositories\pdo\MySqlPDODatabase;
use panel\domain\Admin;


/**
 * Responsible for the behavior of the LoginView.
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
        
        if ($this->hasFormBeenSent()) {
            if ($this->doLogin()) {
                $this->redirectToRoot();
            }
            
            $viewArgs['error'] = true;
            $viewArgs['msg'] = "Email and / or password incorrect";
        }
        
        $this->loadTemplate("LoginView", $viewArgs, false);
    }

    private function hasFormBeenSent()
    {
        return !empty($_POST['email']);
    }

    private function doLogin()
    {
        $admin = Admin::login(
            new MySqlPDODatabase(), 
            $_POST['email'], 
            $_POST['password']
        );

        return !empty($admin);
    }
}
