<?php
namespace controllers;

use core\Controller;
use models\Admins;


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
	    
        if (empty($_SESSION['a_login']))
            $this->loadTemplate('404/404_no_logged', $viewArgs);
           
	    $admins = new Admins($_SESSION['a_login']);
	    
	    $viewArgs['adminName'] = $admins->getName();
	    
	    $this->loadTemplate('404/404_logged', $viewArgs);
	}
}
