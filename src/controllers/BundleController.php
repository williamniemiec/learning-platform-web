<?php
namespace controllers;


use core\Controller;
use database\pdo\MySqlPDODatabase;
use models\Student;
use models\dao\CoursesDAO;
use models\dao\NotificationsDAO;
use models\dao\BundlesDAO;


/**
 * Responsible for the behavior of the view {@link bundle.php}.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class BundleController extends Controller 
{    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * @Override
     */
	public function index ()
	{   
	    header("Location:".BASE_URL);
	    exit;
	}
	
	public function open($id_bundle)
	{
	    $dbConnection = new MySqlPDODatabase();
	    
	    $bundlesDAO = new BundlesDAO($dbConnection);
	    $bundle = $bundlesDAO->get($id_bundle);
	    
	    $header = array(
	        'title' => $bundle->getName().' - Learning Platform',
	        'styles' => array('BundleStyle', 'gallery'),
	        'description' => $bundle->getDescription(),
	        'keywords' => array('learning platform', 'bundle', $bundle->getName()),
	        'robots' => 'index'
	    );
	    
	    $viewArgs = array(
	        'header' => $header,
	        'bundle' => $bundle
	    );
	    
	    if (Student::isLogged()) {
	        $student = Student::getLoggedIn($dbConnection);
	        
	        $notificationsDAO = new NotificationsDAO($dbConnection, $student->getId());
	        
	        $viewArgs['notifications'] = array(
                'notifications' => $notificationsDAO->getNotifications(10),
                'total_unread' => $notificationsDAO->countUnreadNotification()
            );
	        
	        $viewArgs['username'] = $student->getName();
	    }
	    
	    $this->loadTemplate("BundleView", $viewArgs, Student::isLogged());
	}
}
