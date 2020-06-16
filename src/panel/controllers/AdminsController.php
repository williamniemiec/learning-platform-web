<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Admins;
use models\Courses;


/**

*/
class AdminsController extends Controller
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
            'title' => 'Learning platform - Admins',
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
        
        $this->loadTemplate("admins_manager", $params);
    }
}
