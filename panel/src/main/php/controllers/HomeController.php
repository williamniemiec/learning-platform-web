<?php
/**
 * Copyright (c) William Niemiec.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

namespace panel\controllers;


use panel\config\Controller;
use panel\domain\Admin;


/**
 * Main controller. It will be responsible for admin's main page behavior.
 */
class HomeController extends Controller 
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
        if (!Admin::isLogged()) {
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
        $this->redirectTo("bundles");
	}
	
	/**
	 * Logout current admin and redirects him to login page. 
	 */
	public function logout()
	{
	    Admin::logout();
        $this->redirectTo("login");
	}
}
