<?php
namespace controllers;


use config\Controller;
use repositories\pdo\MySqlPDODatabase;
use domain\Student;
use dao\NotificationsDAO;


/**
 * Responsible for handling ajax requests for notifications.
 */
class NotificationController extends Controller
{
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * @Override
     */
    public function index ()
    {
        $this->redirect_to_root();
    }
    
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
    /**
     * Marks a notification as read.
     *
     * @param       int $_POST['id_notification'] Notification id
     *
     * @apiNote     Must be called using POST request method
     */
    public function read()
    {
        // Checks if it is an ajax request
        if ($this->get_http_request_method() != 'POST') {
            $this->redirect_to_root();
        }
            
        if (empty($_POST['id_notification'])) {
            return;
        }
        
        $db_connection = new MySqlPDODatabase();
        
        $notifications_dao = new NotificationsDAO($db_connection, Student::get_logged_in($db_connection)->get_id());
        
        $notifications_dao->mark_as_read((int) $_POST['id_notification']);
    }
    
    /**
     * Marks a notification as unread.
     *
     * @param       int $_POST['id_notification'] Notification id
     *
     * @apiNote     Must be called using POST request method
     */
    public function unread()
    {
        // Checks if it is an ajax request
        if ($this->get_http_request_method() != 'POST') {
            $this->redirect_to_root();
        }
            
        if (empty($_POST['id_notification'])) {
            return;
        }
        
        $db_connection = new MySqlPDODatabase();
        
        $notifications_dao = new NotificationsDAO($db_connection, Student::get_logged_in($db_connection)->get_id());
        
        $notifications_dao->markAsUnread((int)$_POST['id_notification']);
    }
    
    /**
     * Deletes a module from a course.
     *
     * @param       int $_POST['id_notification'] Notification id to be deleted
     *
     * @apiNote     Must be called using POST request method
     */
    public function delete()
    {
        // Checks if it is an ajax request
        if ($this->get_http_request_method() != 'POST') {
            $this->redirect_to_root();
        }
            
        if (empty($_POST['id_notification'])) { 
            return; 
        }
        
        $db_connection = new MySqlPDODatabase();
        
        $notifications_dao = new NotificationsDAO($db_connection, Student::get_logged_in($db_connection)->get_id());
        
        $notifications_dao->delete((int)$_POST['id_notification']);
    }
}
