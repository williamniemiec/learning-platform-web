<?php
namespace controllers;

use \core\Controller;


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
            'title' => "Learning platform - Page not found"
        );

		$this->loadTemplate('404', $params);
	}
}
