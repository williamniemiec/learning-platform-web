<?php
namespace controllers;

use core\Controller;
use models\Student;
use database\pdo\MySqlPDODatabase;
use models\dao\StudentsDAO;
use models\enum\GenreEnum;
use models\dao\NotificationsDAO;


/**
 * Responsible for the behavior of the view {@link settings/settings.php}.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
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
        if (!Student::isLogged()){
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
        $dbConnection = new MySqlPDODatabase();
        
        $student = Student::getLoggedIn($dbConnection);
        $notificationsDAO = new NotificationsDAO($dbConnection, $student->getId());
        
        $header = array(
            'title' => 'Settings - Learning platform',
            'styles' => array('settings'),
            'description' => "User settings",
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'header' => $header,
            'scripts' => array("settings"),
            'username' => $student->getName(),
            'user' => $student,
            'notifications' => array(
                'notifications' => $notificationsDAO->getNotifications(10),
                'total_unread' => $notificationsDAO->countUnreadNotification())
        );
        
        $this->loadTemplate("settings/settings", $viewArgs);
    }
    
    /**
     * Edits student settings.
     */
    public function edit()
    {
        $dbConnection = new MySqlPDODatabase();
        
        $student = Student::getLoggedIn($dbConnection);
        $notificationsDAO = new NotificationsDAO($dbConnection, $student->getId());
        
        $header = array(
            'title' => 'Settings - Update - Learning platform',
            'styles' => array('settings'),
            'description' => "User settings",
            'robots' => 'noindex'
        );
        
        // Checks if edition form has been sent
        if (!empty($_POST['name'])) {
            $studentsDAO = new StudentsDAO($dbConnection);
            $student->setGenre(new GenreEnum($_POST['genre']));
            $student->setBirthdate(new \DateTime($_POST['birthdate']));
            
            $studentsDAO->update($student);
            header("Location: ".BASE_URL."settings");
            exit;
        }
        
        $viewArgs = array(
            'header' => $header,
            'username' => $student->getName(),
            'user' => $student,
            'notifications' => array(
                'notifications' => $notificationsDAO->getNotifications(10),
                'total_unread' => $notificationsDAO->countUnreadNotification())
        );
        
        $this->loadTemplate("settings/settings_edit", $viewArgs);
    }
    
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
//     /**
//      * Updates student photo.
//      * 
//      * @param       array $_FILES['photo'] Photo information
//      * 
//      * @apiNote     Must be called using POST request method
//      */
//     public function update_profile_photo()
//     {
//         if ($_SERVER['REQUEST_METHOD'] != 'POST')
//             header("Location: ".BASE_URL);
        
//         $students = new Students($_SESSION['s_login']);
        
        
//         $students->updatePhoto($_FILES['photo']);
//     }
    
//     /**
//      * Updates student password.
//      * 
//      * @param       string $_POST['new_password'] New password
//      * @param       string $_POST['current_password'] Current password
//      * 
//      * @return      bool If password was successfully updated
//      * 
//      * @apiNote     Must be called using POST request method
//      */
//     public function update_password()
//     {
//         if ($_SERVER['REQUEST_METHOD'] != 'POST')
//             header("Location: ".BASE_URL);
        
//         if (empty($_POST['new_password']) || empty($_POST['current_password']))
//             echo false;
        
//         $students = new Students($_SESSION['s_login']);
        
        
//         echo $students->updatePassword($_POST['current_password'], $_POST['new_password']);
//     }
}
