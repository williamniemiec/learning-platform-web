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
        $students = new Students($_SESSION['s_login']);
        $courses = new Courses($_SESSION['s_login']);
        $student = $students->get($_SESSION['s_login']);
        
        $params = array(
            'title' => 'Learning platform - Support',
            'studentName' => $student->getName()          
        );
        
        $this->loadTemplate("support", $params);
    }
    
    public function open()
    {
        $students = new Students($_SESSION['s_login']);
        $courses = new Courses($_SESSION['s_login']);
        $student = $students->get($_SESSION['s_login']);
        
        $params = array(
            'title' => 'Learning platform - Support',
            'studentName' => $student->getName()
        );
        
        $this->loadTemplate("support_content", $params);
    }
}
