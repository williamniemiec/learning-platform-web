<?php
namespace controllers;

use core\Controller;
use database\pdo\MySqlPDODatabase;
use models\Student;
use models\dao\CoursesDAO;


/**
 * Main controller. It will be responsible for site's main page behavior.
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
     * It will check if student is logged; otherwise, redirects him to home
     * page.
     */
    public function __construct()
    {
        if (Student::isLogged()) {
            header("Location: ".BASE_URL);
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
	    
	    $header = array(
	        'title' => 'Home - Learning Platform',
	        'styles' => array('home'),
	        'description' => "Start learning today",
	        'keywords' => array('learning platform', 'home'),
	        'robots' => 'index'
	    );
	    
	    if (Student::isLogged()) {
// 	        $student = Student::getLoggedIn($dbConnection);
// 	        $coursesDAO = new CoursesDAO($dbConnection);
	        
//     		$viewArgs = array(
//     		    'username' => $student->getName(),
//     		    'courses' => $coursesDAO->getMyCourses($student->getId()),
//     		    'totalCourses' => $student->countCourses(),
//     		    'header' => $header
//     		);
	    }
	    else {
	        $viewArgs = array(
	            'header' => $header,
	            'total_bundles' => 10,
	            'total_courses' => 100,
	            'total_length' => 100000
	        );
	    }

		$this->loadTemplate("home", $viewArgs, Student::isLogged());
	}
	
	/**
	 * Logout current student and redirects him to login page.
	 */
	public function logout()
	{
	    Student::logout();
	    header("Refresh: 0");
	}
	
	/**
	 * Opens student settings.
	 */
	/*public function settings()
	{
	    $students = new Students($_SESSION['s_login']);
	    $courses = new Courses($_SESSION['s_login']);
	    $student = $students->get($_SESSION['s_login']);
	    
	    
	    $header = array(
	        'title' => 'Home - Learning platform',
	        'styles' => array('settings'),
	        'description' => "Start learning today",
	        'keywords' => array('learning platform', 'home'),
	        'robots' => 'index'
	    );
	    
	    $viewArgs = array(
	        'username' => $student->getName(),
	        'genre' => $student->getGenre(),
	        'birthdate' => explode(" ", $student->getBirthdate())[0],
	        'email' => $student->getEmail(),
	        'courses' => $courses->getMyCourses(),
	        'totalCourses' => $courses->countCourses(),
	        'header' => $header
	    );
	    
	    $this->loadTemplate("settings", $viewArgs, true);
	}*/
}
