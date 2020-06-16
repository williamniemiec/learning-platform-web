<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Admins;


/**
 * Responsible for the behavior of the view {@link studentsManager/students_manager.php}.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class StudentsController extends Controller
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * It will check if admin is logged; otherwise, redirects him to login
     * page.
     */
    public function __construct()
    {
        if (!Admins::isLogged()) {
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
        $admins = new Admins($_SESSION['a_login']);
        $students = new Students();
        
        $header = array(
            'title' => 'Learning platform - Students manager',
            'styles' => array('studentsManager')
        );
        
        $params = array(
            'adminName' => $admins->getName(),
            'students' => $students->getAll(),
            'header' => $header
        );
        
        $this->loadTemplate("studentsManager/students_manager", $params);
    }
}
