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
	    $header = array(
	        'title' => 'Learning platform - Page not found',
	        'styles' => array('style')
	        //'description' => "A website made using MVC-in-PHP framework",
	        //'keywords' => array('home', 'mvc-in-php'),
	        //'robots' => 'index'
	    );
	    
        $params = array(
            'title' => "",
            'header' => $header,
            'scripts' => array()
        );
	    
        if (empty($_SESSION['a_login'])) {
		  $this->loadTemplate('404/404_noLogged', $params);
            
        }        
           
	    $admins = new Admins($_SESSION['a_login']);
	    
	    $params['adminName'] = $admins->getName();
	    
		$this->loadTemplate('404/404_logged', $params);
	}
}
