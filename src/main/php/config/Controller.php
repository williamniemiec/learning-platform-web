<?php
namespace config;


/**
 * Class responsible for opening views.
 */
abstract class Controller
{
    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
    /**
     * Main method of the controller. It will be responsible for calling a view.
     */
    public abstract function index();
    
	/**
	 * Shows a view.
	 *
	 * @param string $viewName View's name
	 * @param array $viewData [optional] View's parameters
	 */
	public function loadView ($viewName, $viewData = array())
	{
		extract($viewData);				// Transforms array keys into variables
		require 'src/main/php/views/'.$viewName.'.php';
	}

	/**
	 * Shows a view inside a template.
	 *
	 * @param string $viewName View's name
	 * @param array $viewData [optional] View's parameters
	 */
	public function loadTemplate($viewName, $viewData = array(), $logged = true)
	{
	    extract($viewData);				// Transforms array keys into variables
	    
	    if ($logged)
	        require 'src/main/php/views/template/html_logged.php';
        else
            require 'src/main/php/views/template/html_no_logged.php';
	}
}
