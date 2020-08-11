<?php
namespace controllers;

use core\Controller;
use database\pdo\MySqlPDODatabase;
use models\Student;
use models\dao\CoursesDAO;


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
	    
	    $header = array(
	        'title' => '<name_bundle> - Learning Platform',
	        'styles' => array('BundleStyle', 'gallery'),
	        'description' => "<bundle_desc>",
	        'keywords' => array('learning platform', 'bundle', '<name_bundle>'),
	        'robots' => 'index'
	    );
	    
	    if (Student::isLogged()) {
	        $notificationsDAO = new NotificationsDAO($dbConnection, $student->getId());
	        //....
	    }
	    else {
	        $viewArgs = array(
	            'header' => $header
	        );
	    }
	    
	    $this->loadTemplate("BundleView", $viewArgs, Student::isLogged());
	}
}
