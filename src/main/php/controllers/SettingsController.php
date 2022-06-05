<?php
namespace controllers;


use config\Controller;
use repositories\pdo\MySqlPDODatabase;
use domain\Student;
use domain\enum\GenreEnum;
use dao\StudentsDAO;
use dao\NotificationsDAO;


/**
 * Responsible for the behavior of the SettingsView.
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
        if (!Student::is_logged()) {
            $this->redirect_to("login");
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
        $db_connection = new MySqlPDODatabase();
        
        $student = Student::get_logged_in($db_connection);
        $notifications_dao = new NotificationsDAO($db_connection, $student->get_id());
        
        $header = array(
            'title' => 'Settings - Learning platform',
            'styles' => array('SettingsStyle'),
            'description' => "User settings",
            'robots' => 'noindex'
        );
        
        $view_args = array(
            'header' => $header,
            'scripts' => array("SettingsScript"),
            'username' => $student->get_name(),
            'user' => $student,
            'notifications' => array(
                'notifications' => $notifications_dao->get_notifications(10),
                'total_unread' => $notifications_dao->count_unread_notification())
        );
        
        if (isset($_SESSION['cleared'])) {
            $view_args['msg'] = "Session has been successfully cleared!";
            unset($_SESSION['cleared']);
        }
        
        $this->load_template("settings/SettingsView", $view_args);
    }
    
    /**
     * Edits student settings.
     */
    public function edit()
    {
        $db_connection = new MySqlPDODatabase();
        
        $student = Student::get_logged_in($db_connection);
        $notifications_dao = new NotificationsDAO($db_connection, $student->get_id());

        $header = array(
            'title' => 'Settings - Update - Learning platform',
            'styles' => array('SettingsStyle'),
            'description' => "User settings",
            'robots' => 'noindex'
        );
        
        // Checks if edition form has been sent
        if (!empty($_POST['name'])) {
            $students_dao = new StudentsDAO($db_connection);
            $student->set_genre(new GenreEnum($_POST['genre']));
            $student->set_birthdate(new \DateTime($_POST['birthdate']));
            
            $students_dao->update($student);
            $this->redirect_to("settings");
        }
        
        $view_args = array(
            'header' => $header,
            'username' => $student->get_name(),
            'user' => $student,
            'notifications' => array(
                'notifications' => $notifications_dao->get_notifications(10),
                'total_unread' => $notifications_dao->count_unread_notification()),
            'msg' => ''
        );
        
        $this->load_template("settings/SettingsEditView", $view_args);
    }
    
    public function clear()
    {
        $db_connection = new MySqlPDODatabase();
        
        $students_dao = new StudentsDAO(
            $db_connection, 
            Student::get_logged_in($db_connection)->get_id()
        );
        
        $_SESSION['cleared'] = $students_dao->clear_history();
        
        $this->redirect_to("settings");
    }
    
    public function delete()
    {
        $db_connection = new MySqlPDODatabase();
        
        $students_dao = new StudentsDAO(
            $db_connection,
            Student::get_logged_in($db_connection)->get_id()
        );
        
        if ($students_dao->delete()) {
            $this->redirect_to_root();            
        }
        else {
            $this->redirect_to("settings");
        }
    }
    
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
    /**
     * Updates student photo.
     * 
     * @param       array $_FILES['photo'] Photo information
     * 
     * @return      bool If photo has been successfully updated
     * 
     * @apiNote     Must be called using POST request method
     */
    public function update_profile_photo()
    {
        if ($this->get_http_request_method() != 'POST') {
            $this->redirect_to_root();
        }
        
        $db_connection = new MySqlPDODatabase();
        
        $students_dao = new StudentsDAO(
            $db_connection, 
            Student::get_logged_in($db_connection)->get_id()
        );
        
        echo $students_dao->updatePhoto($_FILES['photo']);
    }
    
    /**
     * Updates student password.
     * 
     * @param       string $_POST['new_password'] New password
     * @param       string $_POST['current_password'] Current password
     * 
     * @return      bool If password has been successfully updated
     * 
     * @apiNote     Must be called using POST request method
     */
    public function update_password()
    {
        if ($this->get_http_request_method() != 'POST') {
            $this->redirect_to_root();
        }
        
        $db_connection = new MySqlPDODatabase();
        
        $students_dao = new StudentsDAO(
            $db_connection, 
            Student::get_logged_in($db_connection)->get_id()
        );
        
        echo $students_dao->updatePassword($_POST['current_password'], $_POST['new_password']);
    }
}
