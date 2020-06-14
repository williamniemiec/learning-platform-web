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
        $styles = array('studentsManager', 'style');
        
        $params = array(
            'title' => 'Learning platform - Students manager',
            'adminName' => $admins->getName(),
            'students' => $students->getAll(),
            'styles' => $styles
        );
        
        $this->loadTemplate("students_manager", $params);
    }
}
