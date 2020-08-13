<?php
namespace controllers;

use core\Controller;
use database\pdo\MySqlPDODatabase;
use models\Student;
use models\dao\ClassesDAO;
use models\dao\CoursesDAO;
use models\dao\NotificationsDAO;
use models\dao\HistoricDAO;
use models\dao\BundlesDAO;
use models\enum\BundleOrderTypeEnum;
use models\enum\OrderDirectionEnum;


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
	    
	    $bundlesDAO = new BundlesDAO($dbConnection);
	    $coursesDAO = new CoursesDAO($dbConnection);
	    
	    $header = array(
	        'title' => 'Home - Learning Platform',
	        'styles' => array('gallery', 'searchBar'),
	        'stylesPHP' => array('home'),
	        'description' => "Start learning today",
	        'keywords' => array('learning platform', 'home'),
	        'robots' => 'index'
	    );
	    
	    $viewArgs = array(
	        'header' => $header,
	        'scripts' => array('gallery', 'home'),
	        'total_bundles' => $bundlesDAO->getTotal(),
	        'total_courses' => $coursesDAO->getTotal(),
	        'total_length' => number_format(ClassesDAO::getTotal($dbConnection)['total_length'] / 60, 2)
	    );

	    if (Student::isLogged()) {
	        $student = Student::getLoggedIn($dbConnection);
	        $notificationsDAO = new NotificationsDAO($dbConnection, $student->getId());
	        
	        $viewArgs['username'] = $student->getName();
	        $viewArgs['notifications'] = array(
	            'notifications' => $notificationsDAO->getNotifications(10),
	            'total_unread' => $notificationsDAO->countUnreadNotification());
	        $viewArgs['bundles'] = $bundlesDAO->getAll(
	            $student->getId(), -1, '',
	            new BundleOrderTypeEnum(BundleOrderTypeEnum::SALES),
	            new OrderDirectionEnum(OrderDirectionEnum::DESCENDING)
	            );
	    }
	    else {
	        $viewArgs['bundles'] = $bundlesDAO->getAll(
	            -1, -1, '',
	            new BundleOrderTypeEnum(BundleOrderTypeEnum::SALES),
	            new OrderDirectionEnum(OrderDirectionEnum::DESCENDING)
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
