<?php
namespace controllers;


use config\Controller;
use repositories\pdo\MySqlPDODatabase;
use domain\Student;
use dao\NotificationsDAO;


/**
 * It will be responsible for site's page not found behavior.
 */
class NotFoundController extends Controller 
{
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
	/**
	 * @Override
	 */
	public function index()
	{
	    $db_connection = new MySqlPDODatabase();
	    
	    $header = array(
	        'title' => 'Page not found - Learning platform',
            'description' => "Page not found",
	        'robots' => 'noindex'
	    );
	    
	    $view_args = array(
	        'header' => $header
	    );
	    
	    $student = Student::get_logged_in($db_connection);
	    
	    if (empty($student)) {
	        $this->load_template('error/404', $view_args, false);
	    }
        else {
            $notifications_dao = new NotificationsDAO($db_connection, $student->get_id());
    	        
            $view_args['username'] = $student->get_name();
            $view_args['notifications'] = array(
                'notifications' => $notifications_dao->get_notifications(10),
                'total_unread' => $notifications_dao->count_unread_notification()
            );
            
            $this->load_template('error/404', $view_args, true);
        }
	}
}
