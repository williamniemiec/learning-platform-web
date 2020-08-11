<?php
namespace controllers;


use core\Controller;
use database\pdo\MySqlPDODatabase;
use models\Student;
use models\dao\NotificationsDAO;


/**
 * Responsible for the behavior of the view {@link PurchasesView.php}.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class PurchasesController extends Controller
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * It will check if student is logged; otherwise, redirects him to home
     * page.
     */
    public function __construct()
    {
            if (!Student::isLogged()) {
                header("Location: ".BASE_URL);
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
            'title' => 'Purchases - Learning Platform',
            
            //'styles' => array('home', 'gallery'),
            'description' => "Student purchases",
            //'keywords' => array('learning platform', 'home'),
            'robots' => 'noindex'
        );

        $viewArgs = array(
            'header' => $header,
            'username' => $student->getName(),
            'notifications' => array(
                'notifications' => $notificationsDAO->getNotifications(10),
                'total_unread' => $notificationsDAO->countUnreadNotification())
        );
        
        $this->loadTemplate("PurchasesView", $viewArgs, Student::isLogged());
    }
}
