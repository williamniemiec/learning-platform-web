<?php
namespace panel\config;


use \panel\controllers\NotFoundController;


/**
 * Class responsible for opening controllers.
 */
class Core 
{
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Analyzes the URL to determine which controller to call and what action 
     * to pass to it.
     */
    public function run()
    {
        $url = $this->getCurrentUrl();
        $controller = $this->extractControllerFrom($url);
        $action = $this->extractActionFrom($url);
        $params = $this->extractParamsFrom($url);

        if(!$this->hasControllerWithNameAndAction($controller, $action)) {
            $controllerInstance = new NotFoundController();    
            $action = 'index';
        } 
        else {
            $controllerInstance = $this->buildControllerInstance($controller);
        }

        call_user_func_array(array($controllerInstance, $action), $params);
    }

    private function getCurrentUrl()
    {
        $url = '/';
        
        if (isset($_GET['url'])) {
            $url .= $_GET['url'];
        }

        return $url;
    }

    private function extractControllerFrom($url)
    {
        if ($url == '/') {
            return 'HomeController';
        }

        $urlTerms = $this->extractUrlTerms($url);
        $controllerName = $urlTerms[0];

        return $controllerName."Controller";
    }

    private function extractUrlTerms($url) 
    {
        $urlTerms = explode("/", $url);
        array_shift($urlTerms); // Removes first item from array (it is null)

        return $urlTerms;
    }

    private function extractActionFrom($url)
    {
        if ($url == '/') {
            return 'index';
        }

        $action = 'index';
        $urlTerms = $this->extractUrlTerms($url);
        array_shift($urlTerms);

        if (isset($urlTerms[0]) && !$this->isEmptyUrl($urlTerms[0])) {
            $action = $urlTerms[0];
        }

        return $action;
    }

    private function isEmptyUrl($url)
    {
        return  !isset($url) 
                || empty($url) 
                || ($url == '/');
    }

    private function extractParamsFrom($url)
    {
        if ($url == '/') {
            return array();
        }

        $params = array();
        $urlTerms = $this->extractUrlTerms($url);
        array_shift($urlTerms);

        if (isset($urlTerms[0]) && !$this->isEmptyUrl($urlTerms[0])) {
            array_shift($urlTerms);
        }

        if (isset($urlTerms[0]) && !$this->isEmptyUrl($urlTerms[0])) {
            $params = $urlTerms;
        }

        return $params;
    }

    private function hasControllerWithNameAndAction($name, $action)
    {
        return  $this->hasControllerWithName($name) 
                && $this->hasControllerAnActionWithName($action, $name);
    }

    private function hasControllerWithName($name)
    {
        $normalizedName = ucfirst($name);
        
        return file_exists('src/main/php/controllers/'.$normalizedName.'.php');
    }

    private function hasControllerAnActionWithName($name, $controller)
    {
        $controllerName = $this->buildControllerClassPath($controller);
        
        return method_exists($controllerName, $name);
    }

    private function buildControllerClassPath($name)
    {
        $normalizedName = ucfirst($name);

        return '\\panel\\controllers\\'.$normalizedName;
    }

    private function buildControllerInstance($name)
    {
        $controllerClassPath = $this->buildControllerClassPath($name);
        
        return new $controllerClassPath;
    }
}
