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
        
        $params = array(
            'title' => 'Learning platform - Support',
            'studentName' => $student->getName()          
        );
        
        $this->loadTemplate("support", $params);
    }
    
    /**
     * Opens a topic from support.
     */
    public function open()
    {
        $students = new Students($_SESSION['s_login']);
        $student = $students->get($_SESSION['s_login']);
        
        $params = array(
            'title' => 'Learning platform - Support',
            'username' => $student->getName()
        );
        
        $this->loadTemplate("support_content", $params);
    }
    
    /**
     * Creates a new topic.
     */
    public function new()
    {
        $students = new Students($_SESSION['s_login']);
        $student = $students->get($_SESSION['s_login']);
        
        $params = array(
            'title' => 'Learning platform - Support - New',
            'username' => $student->getName()
        );
        
        $this->loadTemplate("support_new", $params);
    }
}
