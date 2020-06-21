<?php
namespace controllers;

use core\Controller;
use models\Students;


/**
 * It will be responsible for site's page not found behavior.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
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
	        'title' => 'Learning platform - Page not found'
	    );
	    
	    $viewArgs = array(
	        'header' => $header
	    );
	    
	    if (empty($_SESSION['s_login']))
	        $this->loadTemplate('errors/404', $viewArgs, false);
	        
        $students = new Students($_SESSION['s_login']);
        $viewArgs['username'] = $students->getName();
        
        $this->loadTemplate('errors/404', $viewArgs, true);
	}
}
