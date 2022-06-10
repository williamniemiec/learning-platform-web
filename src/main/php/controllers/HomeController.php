<?php
/**
 * Copyright (c) William Niemiec.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

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
	    $dbConnection = new MySqlPDODatabase();
	    $bundlesDao = new BundlesDAO($dbConnection);
	    $coursesDao = new CoursesDAO($dbConnection);
	    $header = array(
	        'title' => 'Home - Learning Platform',
	        'styles' => array('gallery', 'searchBar'),
	        'stylesPHP' => array('HomeStyle'),
	        'description' => "Start learning today",
	        'keywords' => array('learning platform', 'home'),
	        'robots' => 'index'
	    );
	    $viewArgs = array(
	        'header' => $header,
	        'scripts' => array('gallery', 'HomeScript'),
	        'total_bundles' => $bundlesDao->getTotal(),
	        'total_courses' => $coursesDao->getTotal(),
	        'total_length' => $this->computeTotalLength($dbConnection)
	    );

	    if (Student::isLogged()) {
	        $student = Student::getLoggedIn($dbConnection);
	        $notificationsDao = new NotificationsDAO($dbConnection, $student->getId());
	        $viewArgs['username'] = $student->getName();
	        $viewArgs['notifications'] = array(
	            'notifications' => $notificationsDao->getNotifications(10),
	            'total_unread' => $notificationsDao->countUnreadNotification());
	        $viewArgs['bundles'] = $bundlesDao->getAll(
	            $student->getId(), -1, '',
	            new BundleOrderTypeEnum(BundleOrderTypeEnum::SALES),
	            new OrderDirectionEnum(OrderDirectionEnum::DESCENDING)
			);
	    }
	    else {
	        $viewArgs['bundles'] = $bundlesDao->getAll(
	            -1, 
				-1, 
				'',
	            new BundleOrderTypeEnum(BundleOrderTypeEnum::SALES),
	            new OrderDirectionEnum(OrderDirectionEnum::DESCENDING)
            );
	    }
	    
		$this->loadTemplate("HomeView", $viewArgs, Student::isLogged());
	}

	private function computeTotalLength($dbConnection)
	{
		$total = ClassesDAO::getTotal($dbConnection)['total_length'] / 60;
		
		return number_format($total, 2);
	}
	
	/**
	 * Logout current student and redirects him to login page.
	 */
	public function logout()
	{
	    Student::logout();
	    $this->redirectToRoot();
	}
	
	
	//-------------------------------------------------------------------------
	//        Ajax
	//-------------------------------------------------------------------------
	/**
	 * Gets student history of the last 7 days.
	 *
	 * @return      string Student historic
	 */
	public function weeklyProgress()
	{
	    if ($this->getHttpRequestMethod() != 'POST') {
	        $this->redirectToRoot();
		}
	    
	    $dbConnection = new MySqlPDODatabase();
	    $historicDao = new HistoricDAO(
	        $dbConnection, 
	        Student::getLoggedIn($dbConnection)->getId()
        );
	    
	    echo json_encode($historicDao->getWeeklyHistory());
	}
	
	/**
	 * Gets logged in student.
	 *
	 * @return      string Student logged in
	 *
	 * @apiNote     Must be called using POST request method
	 */
	public function getStudentLoggedIn()
	{
	    if ($this->getHttpRequestMethod() != 'POST') {
	        $this->redirectToRoot();
		}
	        
	    echo json_encode(Student::getLoggedIn(new MySqlPDODatabase()));
	}
}
