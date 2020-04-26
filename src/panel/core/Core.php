<?php
namespace core;

use \controllers\NotFoundController;


/**
 * Class responsible for opening controllers.
 */
class Core 
{
    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
	/**
	 * Analyzes the URL to determine which controller to call and what action to pass to it
	 */
	public function run()
	{
		$params = array();

		// Gets root url
		$url = '/';						// Url default is '/' (if nothing has been sent)
		if (isset($_GET['url'])) {
			$url .= $_GET['url'];
		}
		
		// Gets controller and action
		if ($url != '/') {				// Checks if something has been sent
			$url = explode("/", $url);
			array_shift($url);			// Removes first item from array (it is null)
			
			// Gets controller
			$currentController = $url[0]."Controller";
			array_shift($url);

			// Gets action (if there is one) (also avoids '/'; ex: ../controller/)
			if (isset($url[0]) && !empty($url[0]) && $url[0] != '/') {
				$currentAction = $url[0];	// If there is an action, gets it

				array_shift($url);
			} else {						// If there is no action, sets default
				$currentAction = 'index'; 
			}

			// Gets parameters (if there are any)
			if (isset($url[0]) && !empty($url[0]) && $url[0] != '/') {
				$params = $url;
			}

		} else {	// If nothing was sent, sets controller and action default
			$currentController = 'HomeController'; 
			$currentAction = 'index';
		}
		
		$controllerName = $currentController;
		$currentController = ucfirst($currentController);
		$currentController = '\\controllers\\'.$currentController;

		// If controller does not exist, set notFoundController as current controller
		if( !file_exists('controllers/'.$controllerName.'.php') || 
			!method_exists($currentController, $currentAction)) {
			$c = new NotFoundController();	
			$currentAction = 'index';
		} else {
			$c = new $currentController();
		}

		// Instanciates controller and action
		call_user_func_array(array($c, $currentAction), $params);	// $c->$currentAction($params);
	}
}