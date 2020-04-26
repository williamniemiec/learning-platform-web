<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Admins;
use models\Courses;


/**
 * Main controller. It will be responsible for site's main page behavior.
 */
class HomeController extends Controller 
{
    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    public function __construct()
    {
        if (!Admins::isLogged()) {
            header("Location: ".BASE_URL."login");
            exit;
        }
    }
    
    
    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
    /**
     * @Override
     */
	public function index ()
	{
	    $admins = new Admins($_SESSION['a_login']);
	    $courses = new Courses();
	    
		$params = array(
			'title' => 'Learning platform - home',
		    'adminName' => $admins->getName(),
		    'courses' => $courses->getCourses()
		);

		$this->loadTemplate("home", $params);
	}
	
	public function logout()
	{
	    unset($_SESSION['a_login']);
	    header("Location: ".BASE_URL."login");
	}
}
