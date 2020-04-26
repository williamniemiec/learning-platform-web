<?php
namespace controllers;

use core\Controller;
use models\Admins;


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
	    
        if (empty($_SESSION['a_login'])) {
		  $this->loadTemplate('404_noLogged', $params);
            
        }        
           
	    $admins = new Admins($_SESSION['a_login']);
	    
	    $params['adminName'] = $admins->getName();
	    
		$this->loadTemplate('404_logged', $params);
	}
}
