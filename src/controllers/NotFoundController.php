<?php
namespace controllers;

use core\Controller;
use models\Student;
use database\pdo\MySqlPDODatabase;


/**
 * It will be responsible for site's page not found behavior.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
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
	    $dbConnection = new MySqlPDODatabase();
	    
	    $header = array(
	        'title' => 'Page not found - Learning platform',
            'description' => "Page not found",
	        'robots' => 'noindex'
	    );
	    
	    $viewArgs = array(
	        'header' => $header
	    );
	    
	    $student = Student::getLoggedIn($dbConnection);
	    
	    if (empty($student))
	        $this->loadTemplate('errors/404', $viewArgs, false);
	        
        $viewArgs['username'] = $student->getName();
        
        $this->loadTemplate('errors/404', $viewArgs, true);
	}
}
