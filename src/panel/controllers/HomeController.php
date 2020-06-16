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
	    
	    $header = array(
	        'title' => 'Learning platform - home',
	        'styles' => array('style')
	        //'description' => "A website made using MVC-in-PHP framework",
	        //'keywords' => array('home', 'mvc-in-php'),
	        //'robots' => 'index'
	    );
	    
		$params = array(
		    'adminName' => $admins->getName(),
		    'courses' => $courses->getCourses(),
		    'header' => $header,
		    'scripts' => array('script')
		);

		$this->loadTemplate("coursesManager/courses_manager", $params);
	}
	
	public function logout()
	{
	    unset($_SESSION['a_login']);
	    header("Location: ".BASE_URL."login");
	}
}
