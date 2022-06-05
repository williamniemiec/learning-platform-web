<?php
namespace controllers;


use config\Controller;
use domain\Student;
use domain\enum\BundleOrderTypeEnum;
use domain\enum\OrderDirectionEnum;
use repositories\pdo\MySqlPDODatabase;
use dao\BundlesDAO;
use dao\ClassesDAO;
use dao\CoursesDAO;
use dao\NotificationsDAO;
use dao\HistoricDAO;


/**
 * Responsible for the behavior of the HomeView.
 */
class HomeController extends Controller 
{
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * {@inheritDoc}
     * @see Controller::index()
     * 
     * @Override
     */
	public function index ()
	{   
	    $db_connection = new MySqlPDODatabase();
	    
	    $bundles_dao = new BundlesDAO($db_connection);
	    $courses_dao = new CoursesDAO($db_connection);
	    
	    $header = array(
	        'title' => 'Home - Learning Platform',
	        'styles' => array('gallery', 'searchBar'),
	        'stylesPHP' => array('HomeStyle'),
	        'description' => "Start learning today",
	        'keywords' => array('learning platform', 'home'),
	        'robots' => 'index'
	    );
	    
	    $view_args = array(
	        'header' => $header,
	        'scripts' => array('gallery', 'HomeScript'),
	        'total_bundles' => $bundles_dao->getTotal(),
	        'total_courses' => $courses_dao->getTotal(),
	        'total_length' => number_format(ClassesDAO::getTotal($db_connection)['total_length'] / 60, 2)
	    );

	    if (Student::is_logged()) {
	        $student = Student::get_logged_in($db_connection);
	        $notifications_dao = new NotificationsDAO($db_connection, $student->get_id());
	        
	        $view_args['username'] = $student->get_name();
	        $view_args['notifications'] = array(
	            'notifications' => $notifications_dao->get_notifications(10),
	            'total_unread' => $notifications_dao->count_unread_notification());
	        $view_args['bundles'] = $bundles_dao->getAll(
	            $student->get_id(), -1, '',
	            new BundleOrderTypeEnum(BundleOrderTypeEnum::SALES),
	            new OrderDirectionEnum(OrderDirectionEnum::DESCENDING)
	            );
	    }
	    else {
	        $view_args['bundles'] = $bundles_dao->getAll(
	            -1, -1, '',
	            new BundleOrderTypeEnum(BundleOrderTypeEnum::SALES),
	            new OrderDirectionEnum(OrderDirectionEnum::DESCENDING)
            );
	    }
	    
		$this->load_template("HomeView", $view_args, Student::is_logged());
	}
	
	/**
	 * Logout current student and redirects him to login page.
	 */
	public function logout()
	{
	    Student::logout();
	    $this->redirect_to_root();
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
	    if ($this->get_http_request_method() != 'POST') {
	        $this->redirect_to_root();
		}
	    
	    $db_connection = new MySqlPDODatabase();
	    $historic_dao = new HistoricDAO(
	        $db_connection, 
	        Student::get_logged_in($db_connection)->get_id()
        );
	    
	    echo json_encode($historic_dao->get_weekly_history());
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
	    if ($this->get_http_request_method() != 'POST') {
	        $this->redirect_to_root();
		}
	        
	    echo json_encode(Student::get_logged_in(new MySqlPDODatabase()));
	}
}
