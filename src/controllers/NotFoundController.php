<?php
namespace controllers;

use core\Controller;
use models\Students;


/**
 * It will be responsible for site's page not found behavior.
 */
class NotFoundController extends Controller 
{
    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
    /**
     * @Override
     */
	public function index()
	{
        $params = array(
            'title' => "Learning platform - Page not found",
        );
	    
        if (empty($_SESSION['s_login'])) {
		  $this->loadTemplate('404_noLogged', $params);
            
        }        
           
	    $students = new Students($_SESSION['s_login']);
		$params['studentName'] = $students->getName();
		
		$this->loadTemplate('404_logged', $params);
	}
}
