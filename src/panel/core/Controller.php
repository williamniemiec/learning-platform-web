<?php
namespace core;


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
	public function loadView($viewName, $viewData = array())
	{
		extract($viewData);				// Transforms array keys into variables
		require 'views/'.$viewName.'.php';
	}

	/**
	 * Shows a view inside a template.
	 *
	 * @param string $viewName View's name
	 * @param array $viewData [optional] View's parameters
	 */
	public function loadTemplate($viewName, $viewData = array())
	{
		extract($viewData);				// Transforms array keys into variables
		require 'views/template/html.php';
	}
	
// 	public function loadViewInTemplate($viewName, $viewData = array())
// 	{
// 	    var_dump($viewData);
// 	    require 'views/'.$viewName.'.php';
// 	}
}
