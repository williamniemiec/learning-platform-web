<?php
namespace controllers;

use core\Controller;
use database\pdo\MySqlPDODatabase;
use models\Student;
use models\dao\CoursesDAO;
use models\dao\NotificationsDAO;
use models\dao\HistoricDAO;


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
//         if (Student::isLogged()) {
//             header("Location: ".BASE_URL);
//             exit;
//         }
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
	        'styles' => array('home', 'gallery', 'searchBar'),
	        'description' => "Start learning today",
	        'keywords' => array('learning platform', 'home'),
	        'robots' => 'index'
	    );
	    
	    if (Student::isLogged()) {
	        $student = Student::getLoggedIn($dbConnection);
	        $coursesDAO = new CoursesDAO($dbConnection);
	        $notificationsDAO = new NotificationsDAO($dbConnection, $student->getId());
	        
    		$viewArgs = array(
    		    'username' => $student->getName(),
    		    'header' => $header,
    		    'notifications' => array(
    		        'notifications' => $notificationsDAO->getNotifications(10),
    		        'total_unread' => $notificationsDAO->countUnreadNotification())
    		);
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
	
	
	//-------------------------------------------------------------------------
	//        Ajax
	//-------------------------------------------------------------------------
	/**
	 * Gets student history of the last 7 days.
	 *
	 * @return      string Student historic
	 */
	public function weekly_progress()
	{
	    if ($_SERVER['REQUEST_METHOD'] != 'POST')
	        header("Location: ".BASE_URL);
	    
	    $dbConnection = new MySqlPDODatabase();
	    $historicDAO = new HistoricDAO(
	        $dbConnection, 
	        Student::getLoggedIn($dbConnection)->getId()
        );
	    
	    echo json_encode($historicDAO->getWeeklyHistory());
	}
	
	/**
	 * Gets logged in student.
	 *
	 * @return      string Student logged in
	 *
	 * @apiNote     Must be called using POST request method
	 */
	public function get_student_logged_in()
	{
	    // Checks if it is an ajax request
	    if ($_SERVER['REQUEST_METHOD'] != 'POST')
	        header("Location: ".BASE_URL);
	        
	        echo json_encode(Student::getLoggedIn(new MySqlPDODatabase()));
	}
}
