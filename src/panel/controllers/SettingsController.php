<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Admins;
use models\Courses;


/**

*/
class SettingsController extends Controller
{
    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    public function __construct()
    {
        if (!Admins::isLogged()){
            header("Location: ".BASE_URL."login");
            exit;
        }
    }
    
    
    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
    /**
     * @Override
     */
    public function index ()
    {
        $admins = new Admins($_SESSION['a_login']);
        
        $header = array(
            'title' => 'Learning platform - Settings',
            'styles' => array('style')
            //'description' => "A website made using MVC-in-PHP framework",
            //'keywords' => array('home', 'mvc-in-php'),
            //'robots' => 'index'
        );
        
        $params = array(
            'adminName' => $admins->getName(),
            'header' => $header
        );
        
        $this->loadTemplate("settings/settings", $params);
    }
    
    
    public function edit()
    {
        $admins = new Admins($_SESSION['a_login']);
        //$styles = array('style');
        $header = array(
            'title' => 'Learning platform - Settings - Edition',
            'styles' => array('style')
            //'description' => "A website made using MVC-in-PHP framework",
            //'keywords' => array('home', 'mvc-in-php'),
            //'robots' => 'index'
        );
        
        $params = array(
            'adminName' => $admins->getName(),
            'header' => $header,
            'scripts' => array()
        );
        
        $this->loadTemplate("settings/settings_edit", $params);
    }
}
