<?php
namespace controllers;


use core\Controller;
use models\Admin;
use database\pdo\MySqlPDODatabase;
use models\dao\BundlesDAO;


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
     * Checks whether admin is logged in. If he is not, redirects him to login 
     * page.
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
	    $dbConnection = new MySqlPDODatabase();
	    $admin = Admin::getLoggedIn($dbConnection);
	    $bundlesDAO = new BundlesDAO($dbConnection);
	    
	    $header = array(
	        'title' => 'Admin area - Learning platform',
	        'styles' => array('coursesManager'),
	        'robots' => 'noindex'
	    );
	    
		$viewArgs = array(
		    'username' => $admin->getName(),
		    'bundles' => $bundlesDAO->getAll(),
		    'header' => $header
		);

		$this->loadTemplate("bundlesManager/bundles_manager", $viewArgs);
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
