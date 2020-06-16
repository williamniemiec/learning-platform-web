<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Admins;
use models\Courses;


/**

*/
class SupportController extends Controller
{
    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    public function __construct()
    {
        if (!Students::isLogged() && !Admins::isLogged()){
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
        $courses = new Courses();
        
        $header = array(
            'title' => 'Learning platform - Support',
            'styles' => array('support', 'style')
            //'description' => "A website made using MVC-in-PHP framework",
            //'keywords' => array('home', 'mvc-in-php'),
            //'robots' => 'index'
        );
        
        $params = array(
            'adminName' => $admins->getName(),
            'courses' => $courses->getCourses(),
            'header' => $header,
            'scripts' => array()
        );
        
        $this->loadTemplate("support", $params);
    }
    
    public function open()
    {
        $admins = new Admins($_SESSION['a_login']);
        $courses = new Courses();
        
        $header = array(
            'title' => 'Learning platform - Support',
            'styles' => array('support', 'style')
            //'description' => "A website made using MVC-in-PHP framework",
            //'keywords' => array('home', 'mvc-in-php'),
            //'robots' => 'index'
        );
        
        $params = array(
            'adminName' => $admins->getName(),
            'courses' => $courses->getCourses(),
            'header' => $header,
            'scripts' => array()
        );
        
        $this->loadTemplate("support_content", $params);
    }
}
