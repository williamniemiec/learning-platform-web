<?php
namespace panel\controllers;


use panel\config\Controller;
use panel\models\Admin;
use panel\database\pdo\MySqlPDODatabase;
use panel\models\dao\BundlesDAO;


/**
 * Main controller. It will be responsible for admin's main page behavior.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
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
	    // Redirects admin to bundles manager
	    header("Location: ".BASE_URL."bundles");
	}
	
	/**
	 * Logout current admin and redirects him to login page. 
	 */
	public function logout()
	{
	    Admin::logout();
	    header("Location: ".BASE_URL."login");
	}
}
