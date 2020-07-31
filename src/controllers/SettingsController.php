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
        
        
        $header = array(
            'title' => 'Settings - Learning platform',
            'styles' => array('settings'),
            'description' => "Start learning today",
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'header' => $header,
            'scripts' => array("settings"),
            'username' => $student->getName(),
            'profilePhoto' => $student->getPhoto(),
            'genre' => $student->getGenre(),
            'birthdate' => explode(" ", $student->getBirthdate())[0],
            'email' => $student->getEmail(),
            'courses' => $courses->getMyCourses(),
            'totalCourses' => $courses->countCourses()
        );
        
        $this->loadTemplate("settings/settings", $viewArgs);
    }
    
    /**
     * Edits student settings.
     */
    public function edit()
    {
        $students = new Students($_SESSION['s_login']);
        $courses = new Courses($_SESSION['s_login']);
        $student = $students->get($_SESSION['s_login']);
        
        
        $header = array(
            'title' => 'Settings - Edition - Learning platform',
            'styles' => array('settings'),
            'description' => "Start learning today",
            'robots' => 'noindex'
        );
        
        // Checks if edition form has been sent
        if (!empty($_POST['name'])) {
            $students->update($_POST['name'], $_POST['genre'], $_POST['birthdate']);
            header("Location: ".BASE_URL."settings");
            exit;
        }
        
        $viewArgs = array(
            'header' => $header,
            'username' => $student->getName(),
            'genre' => $student->getGenre(),
            'birthdate' => explode(" ", $student->getBirthdate())[0],
            'email' => $student->getEmail(),
            'courses' => $courses->getMyCourses(),
            'totalCourses' => $courses->countCourses()
        );
        
        $this->loadTemplate("settings/settings_edit", $viewArgs);
    }
    
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
    /**
     * Updates student photo.
     * 
     * @param       array $_FILES['photo'] Photo information
     * 
     * @apiNote     Must be called using POST request method
     */
    public function update_profile_photo()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        $students = new Students($_SESSION['s_login']);
        
        
        $students->updatePhoto($_FILES['photo']);
    }
    
    /**
     * Updates student password.
     * 
     * @param       string $_POST['new_password'] New password
     * @param       string $_POST['current_password'] Current password
     * 
     * @return      bool If password was successfully updated
     * 
     * @apiNote     Must be called using POST request method
     */
    public function update_password()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        if (empty($_POST['new_password']) || empty($_POST['current_password']))
            echo false;
        
        $students = new Students($_SESSION['s_login']);
        
        
        echo $students->updatePassword($_POST['current_password'], $_POST['new_password']);
    }
}
