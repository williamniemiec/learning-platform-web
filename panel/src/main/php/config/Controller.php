<?php
namespace panel\config;


use panel\repositories\pdo\MySqlPDODatabase;
use panel\domain\Admin;


/**
 * Class responsible for opening views.
 */
abstract class Controller
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private const VIEWS_PATH = "src/main/php/views";


    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Main method of the controller. It will be responsible for calling a view.
     */
    public abstract function index();
    
    /**
     * Shows a view.
     *
     * @param       string view_name View name
     * @param       array view_data [optional] View's parameters
     */
    public function loadView($viewName, $viewData = array())
    {
        extract($viewData);
        
        require Controller::VIEWS_PATH."/".$viewName.".php";
    }

    /**
     * Shows a view inside a template.
     *
     * @param       string view_name View name
     * @param       array view_data [optional] View's parameters
     * @param       bool $logged [optional] True if user is logged; false 
     * otherwise
     */
    public function loadTemplate($viewName, $viewData = array(), $logged = true)
    {
        extract($viewData);
        
        if ($logged) {
            require Controller::VIEWS_PATH."/"."template/html_logged.php";
        }
        else {
            require Controller::VIEWS_PATH."/"."template/html_no_logged.php";
        }
    }

    /**
     * Redirects the user to BASE_URL.
     */
    protected function redirectToRoot()
    {
        $this->redirectTo("");
    }

    /**
     * Redirects the user to some location from BASE_URL.
     *
     * @param       string $location Controller name
     */
    protected function redirectTo($location)
    {
        header("Location: ".BASE_URL.$location);
        exit;
    }

    /**
     * Gets HTTP request method.
     *
     * @return       string request method
     */
    protected function getHttpRequestMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Reloads current page.
     *
     * @param       int $delay [optional] Delay time 
     */
    protected function reload($delay = 0)
    {
        header("Refresh: ".$delay);
    }

    protected function hasLoggedAdminAuthorization(...$levels)
    {
        $admin = Admin::getLoggedIn(new MySqlPDODatabase());

        return in_array($admin->getAuthorization()->getLevel(), $levels);
    }
}
