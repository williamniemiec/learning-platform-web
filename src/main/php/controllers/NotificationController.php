<?php
namespace controllers;

use core\Controller;
use repositories\pdo\MySqlPDODatabase;
use domain\Student;
use dao\CoursesDAO;
use dao\NotificationsDAO;


/**
 * Responsible for handling ajax requests for notifications.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
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
        header("Location: ".BASE_URL);
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
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
            
        if (empty($_POST['id_notification'])) {
            return;
        }
        
        $dbConnection = new MySqlPDODatabase();
        
        $notificationsDAO = new NotificationsDAO($dbConnection, Student::getLoggedIn($dbConnection)->getId());
        
        $notificationsDAO->markAsRead((int)$_POST['id_notification']);
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
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
            
            if (empty($_POST['id_notification'])) {
                return;
            }
            
            $dbConnection = new MySqlPDODatabase();
            
            $notificationsDAO = new NotificationsDAO($dbConnection, Student::getLoggedIn($dbConnection)->getId());
            
            $notificationsDAO->markAsUnread((int)$_POST['id_notification']);
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
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
            
        if (empty($_POST['id_notification'])) { 
            return; 
        }
        
        $dbConnection = new MySqlPDODatabase();
        
        $notificationsDAO = new NotificationsDAO($dbConnection, Student::getLoggedIn($dbConnection)->getId());
        
        $notificationsDAO->delete((int)$_POST['id_notification']);
    }
}
