<?php
namespace config;


use \controllers\NotFoundController;


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
            $controller_instance = new NotFoundController();    
            $action = 'index';
        } 
        else {
            $controller_instance = $this->buildControllerInstance($controller);
        }

        call_user_func_array(array($controller_instance, $action), $params);
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

        $url_terms = $this->extractUrlTerms($url);
        
        return $url_terms[0]."Controller";
    }

    private function extractUrlTerms($url) 
    {
        $url_terms = explode("/", $url);
        array_shift($url_terms); // Removes first item from array (it is null)

        return $url_terms;
    }

    private function extractActionFrom($url)
    {
        if ($url == '/') {
            return 'index';
        }

        $action = 'index';
        $url_terms = $this->extractUrlTerms($url);
        array_shift($url_terms);

        if (isset($url_terms[0]) && !$this->isEmptyUrl($url_terms[0])) {
            $action = $url_terms[0];
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
        $url_terms = $this->extractUrlTerms($url);
        array_shift($url_terms);

        if (isset($url_terms[0]) && !$this->isEmptyUrl($url_terms[0])) {
            array_shift($url_terms);
        }

        if (isset($url_terms[0]) && !$this->isEmptyUrl($url_terms[0])) {
            $params = $url_terms;
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
        $normalized_name = ucfirst($name);
        
        return file_exists('src/main/php/controllers/'.$normalized_name.'.php');
    }

    private function hasControllerAnActionWithName($name, $controller)
    {
        $controller_name = $this->buildControllerClassPath($controller);
        
        return method_exists($controller_name, $name);
    }

    private function buildControllerClassPath($name)
    {
        $normalized_name = ucfirst($name);

        return '\\controllers\\'.$normalized_name;
    }

    private function buildControllerInstance($name)
    {
        $controller_class_path = $this->buildControllerClassPath($name);
        
        return new $controller_class_path;
    }
}
