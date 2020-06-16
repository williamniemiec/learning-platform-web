<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Admins;
use models\Courses;



class StudentsController extends Controller
{
    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    public function __construct()
    {
        if (!Admins::isLogged()) {
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
        $students = new Students();
        
        $header = array(
            'title' => 'Learning platform - Students manager',
            'styles' => array('studentsManager', 'style')
            //'description' => "A website made using MVC-in-PHP framework",
            //'keywords' => array('home', 'mvc-in-php'),
            //'robots' => 'index'
        );
        
        $params = array(
            'adminName' => $admins->getName(),
            'students' => $students->getAll(),
            'header' => $header,
            'scripts' => array()
        );
        
        $this->loadTemplate("studentsManager/students_manager", $params);
    }
}
