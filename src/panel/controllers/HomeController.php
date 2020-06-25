<?php
namespace controllers;

use core\Controller;
use models\Admins;
use models\Courses;


/**
 * Main controller. It will be responsible for admin's main page behavior.
 */
class HomeController extends Controller 
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * It will check if admin is logged; otherwise, redirects him to login 
     * page.
     */
    public function __construct()
    {
        if (!Admins::isLogged()) {
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
	    $admins = new Admins($_SESSION['a_login']);
	    $courses = new Courses();
	    
	    $header = array(
	        'title' => 'Admin area - Learning platform',
	        'styles' => array('coursesManager'),
	        'robots' => 'noindex'
	    );
	    
		$viewArgs = array(
		    'username' => $admins->getName(),
		    'courses' => $courses->getCourses(),
		    'header' => $header
		);

		$this->loadTemplate("coursesManager/courses_manager", $viewArgs);
	}
	
	/**
	 * Logout current admin and redirects him to login page. 
	 */
	public function logout()
	{
	    unset($_SESSION['a_login']);
	    header("Location: ".BASE_URL."login");
	}
}
