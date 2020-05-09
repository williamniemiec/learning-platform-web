<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Admins;
use models\Courses;


/**

 */
class SettingsController extends Controller
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
            'title' => 'Learning platform - home',
            'studentName' => $student->getName(),
            'genre' => $student->getGenre(),
            'birthdate' => explode(" ", $student->getBirthdate())[0],
            'email' => $student->getEmail(),
            'courses' => $courses->getMyCourses(),
            'totalCourses' => $courses->countCourses()
        );
        
        $this->loadTemplate("settings", $params);
    }
    
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
