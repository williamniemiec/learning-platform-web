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
        $styles = array('support', 'style');
        
        $params = array(
            'title' => 'Learning platform - Support',
            'adminName' => $admins->getName(),
            'courses' => $courses->getCourses(),
            'styles' => $styles
        );
        
        $this->loadTemplate("support", $params);
    }
    
    public function open()
    {
        $admins = new Admins($_SESSION['a_login']);
        $courses = new Courses();
        $styles = array('support', 'style');
        
        $params = array(
            'title' => 'Learning platform - Support',
            'adminName' => $admins->getName(),
            'courses' => $courses->getCourses(),
            'styles' => $styles
        );
        
        $this->loadTemplate("support_content", $params);
    }
}
