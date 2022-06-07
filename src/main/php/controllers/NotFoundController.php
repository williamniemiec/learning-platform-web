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
	    $header = array(
	        'title' => 'Page not found - Learning platform',
            'description' => "Page not found",
	        'robots' => 'noindex'
	    );
	    $viewArgs = array(
	        'header' => $header
	    );
	    
	    if ($this->isLogged()) {
	        $dbConnection = new MySqlPDODatabase();
			$student = Student::getLoggedIn($dbConnection);
            $notificationsDao = new NotificationsDAO($dbConnection, $student->getId()); 
            $viewArgs['username'] = $student->getName();
            $viewArgs['notifications'] = array(
                'notifications' => $notificationsDao->getNotifications(10),
                'total_unread' => $notificationsDao->countUnreadNotification()
            );
            
            $this->loadTemplate('error/404', $viewArgs, true);
	    }
        else {
			$this->loadTemplate('error/404', $viewArgs, false);
        }
	}

	private function isLogged()
	{
		$dbConnection = new MySqlPDODatabase();
		$student = Student::getLoggedIn($dbConnection);

		return !empty($student);
	}
}
