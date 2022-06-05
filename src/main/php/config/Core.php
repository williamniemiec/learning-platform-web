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
        $url = $this->get_current_url();
        $controller = $this->extract_controller_from($url);
        $action = $this->extract_action_from($url);
        $params = $this->extract_params_from($url);

        if(!$this->has_controller_with_name_and_action($controller, $action)) {
            $controller_instance = new NotFoundController();    
            $action = 'index';
        } 
        else {
            $controller_instance = $this->build_controller_instance($controller);
        }

        call_user_func_array(array($controller_instance, $action), $params);
    }

    private function get_current_url()
    {
        $url = '/';
        
        if (isset($_GET['url'])) {
            $url .= $_GET['url'];
        }

        return $url;
    }

    private function extract_controller_from($url)
    {
        if ($url == '/') {
            return 'HomeController';
        }

        $url_terms = $this->extract_url_terms($url);
        
        return $url_terms[0]."Controller";
    }

    private function extract_url_terms($url) 
    {
        $url_terms = explode("/", $url);
        array_shift($url_terms); // Removes first item from array (it is null)

        return $url_terms;
    }

    private function extract_action_from($url)
    {
        if ($url == '/') {
            return 'index';
        }

        $action = 'index';
        $url_terms = $this->extract_url_terms($url);
        array_shift($url_terms);

        if (isset($url_terms[0]) && !$this->is_empty_url($url_terms[0])) {
            $action = $url_terms[0];
        }

        return $action;
    }

    private function is_empty_url($url)
    {
        return  !isset($url) 
                || empty($url) 
                || ($url == '/');
    }

    private function extract_params_from($url)
    {
        if ($url == '/') {
            return array();
        }

        $params = array();
        $url_terms = $this->extract_url_terms($url);
        array_shift($url_terms);

        if (isset($url_terms[0]) && !$this->is_empty_url($url_terms[0])) {
            array_shift($url_terms);
        }

        if (isset($url_terms[0]) && !$this->is_empty_url($url_terms[0])) {
            $params = $url_terms;
        }

        return $params;
    }

    private function has_controller_with_name_and_action($name, $action)
    {
        return  $this->has_controller_with_name($name) 
                && $this->has_controller_an_action_with_name($action, $name);
    }

    private function has_controller_with_name($name)
    {
        $normalized_name = ucfirst($name);
        
        return file_exists('src/main/php/controllers/'.$normalized_name.'.php');
    }

    private function has_controller_an_action_with_name($name, $controller)
    {
        $controller_name = $this->build_controller_class_path($controller);
        
        return method_exists($controller_name, $name);
    }

    private function build_controller_class_path($name)
    {
        $normalized_name = ucfirst($name);

        return '\\controllers\\'.$normalized_name;
    }

    private function build_controller_instance($name)
    {
        $controller_class_path = $this->build_controller_class_path($name);
        
        return new $controller_class_path;
    }
}
