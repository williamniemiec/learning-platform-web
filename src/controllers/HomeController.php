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
        if (!Students::isLogged() && !Admins::isLogged()){
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
	    $students = new Students($_SESSION['s_login']);
	    $courses = new Courses($_SESSION['s_login']);
	    
		$params = array(
			'title' => 'Learning platform - home',
		    'studentName' => $students->getName(),
		    'courses' => $courses->getMyCourses(),
		    'totalCourses' => $courses->countCourses()
		);

		$this->loadTemplate("home", $params);
	}
	
	public function logout()
	{
	    unset($_SESSION['s_login']);
	    header("Location: ".BASE_URL."login");
	}
	
	public function settings()
	{
	    $params = array(
	        'title' => 'Learning platform - home',
	        'studentName' => $students->getName(),
	        'courses' => $courses->getMyCourses(),
	        'totalCourses' => $courses->countCourses()
	    );
	    
	    $this->loadTemplate("settings", $params);
	}
}
