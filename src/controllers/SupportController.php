<?php
namespace controllers;

use core\Controller;
use models\Students;


/**
 * Responsible for the behavior of the view {@link support/support.php}.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class SupportController extends Controller
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * It will check if student is logged; otherwise, redirects him to login
     * page.
     */
    public function __construct()
    {
        if (!Students::isLogged()){
            header("Location: ".BASE_URL."login");
            exit;
        }
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * @Override
     */
    public function index ()
    {   
        $students = new Students($_SESSION['s_login']);
        $student = $students->get($_SESSION['s_login']);
        
        
        $header = array(
            'title' => 'Support - Learning platform',
            'styles' => array('support'),
            'description' => "Support page",
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'header' => $header,
            'username' => $student->getName()          
        );
        
        $this->loadTemplate("support/support", $viewArgs);
    }
    
    /**
     * Opens a topic from support.
     */
    public function open()
    {
        $students = new Students($_SESSION['s_login']);
        $student = $students->get($_SESSION['s_login']);
        
        
        $header = array(
            'title' => 'Support - Learning platform',
            'styles' => array('support'),
            'description' => "Support topic",
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'header' => $header,
            'username' => $student->getName()
        );
        
        $this->loadTemplate("support/support_content", $viewArgs);
    }
    
    /**
     * Creates a new topic.
     */
    public function new()
    {
        $students = new Students($_SESSION['s_login']);
        $student = $students->get($_SESSION['s_login']);
        
        
        $header = array(
            'title' => 'New topic - Support - Learning platform',
            'styles' => array('support'),
            'description' => "New support topic",
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'header' => $header,
            'username' => $student->getName()
        );
        
        $this->loadTemplate("support/support_new", $viewArgs);
    }
}
