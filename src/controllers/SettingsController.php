<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Courses;


/**
 * Responsible for the behavior of the view {@link settings/settings.php}.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class SettingsController extends Controller
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
        $courses = new Courses($_SESSION['s_login']);
        $student = $students->get($_SESSION['s_login']);
        
        $params = array(
            'title' => 'Learning platform - home',
            'studentName' => $student->getName(),
            'profilePhoto' => $student->getPhoto(),
            'genre' => $student->getGenre(),
            'birthdate' => explode(" ", $student->getBirthdate())[0],
            'email' => $student->getEmail(),
            'courses' => $courses->getMyCourses(),
            'totalCourses' => $courses->countCourses()
        );
        
        $this->loadTemplate("settings", $params);
    }
    
    /**
     * Edits student settings.
     */
    public function edit()
    {
        $students = new Students($_SESSION['s_login']);
        $courses = new Courses($_SESSION['s_login']);
        $student = $students->get($_SESSION['s_login']);
        
        if (!empty($_POST['name'])) {
            $students->update($_POST['name'], $_POST['genre'], $_POST['birthdate']);
            header("Location: ".BASE_URL."settings");
            exit;
        }
        
        $params = array(
            'title' => 'Learning platform - Edit',
            'studentName' => $student->getName(),
            'genre' => $student->getGenre(),
            'birthdate' => explode(" ", $student->getBirthdate())[0],
            'email' => $student->getEmail(),
            'courses' => $courses->getMyCourses(),
            'totalCourses' => $courses->countCourses()
        );
        
        $this->loadTemplate("settings/settings_edit", $params);
    }
}
