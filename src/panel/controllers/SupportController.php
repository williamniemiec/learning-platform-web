<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Admins;
use models\Courses;


/**
 * Responsible for the behavior of the view {@link support/support.php}.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class SupportController extends Controller
{
    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    /**
     * Checks whether admin is logged in. If he is not, redirects him to login 
     * page.
     */
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
            'title' => 'Support - Learning platform',
            'styles' => array('support'),
            'robots' => 'index'
        );
        
        $viewArgs = array(
            'username' => $admins->getName(),
            'courses' => $courses->getCourses(),
            'header' => $header
        );
        
        $this->loadTemplate("support/support", $viewArgs);
    }
    
    /**
     * Opens a topic from support.
     */
    public function open()
    {
        $admins = new Admins($_SESSION['a_login']);
        $courses = new Courses();
        
        $header = array(
            'title' => 'Support - Learning platform',
            'styles' => array('support'),
            'robots' => 'index'
        );
        
        $viewArgs = array(
            'username' => $admins->getName(),
            'courses' => $courses->getCourses(),
            'header' => $header
        );
        
        $this->loadTemplate("supports/support_content", $viewArgs);
    }
}
