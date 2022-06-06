<?php
namespace controllers;


use config\Controller;
use repositories\pdo\MySqlPDODatabase;
use domain\Student;
use dao\NotificationsDAO;
use dao\StudentsDAO;


/**
 * Responsible for the behavior of the PurchasesView.
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
            $this->redirectToRoot();
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
        $notificationsDao = new NotificationsDAO($dbConnection, $student->getId());
        $studentsDao = new StudentsDAO($dbConnection, $student->getId());
        $limit = 10;
        $index = 1;
        
        // Checks whether an index has been sent
        if (!empty($_GET['index'])) {
            $index = (int)$_GET['index'];
        }
        
        $header = array(
            'title' => 'Purchases - Learning Platform',
            'description' => "Student purchases",
            'robots' => 'noindex'
        );

        $viewArgs = array(
            'header' => $header,
            'username' => $student->getName(),
            'purchases' => $studentsDao->getPurchases($limit, $limit * ($index - 1)),
            'notifications' => array(
                'notifications' => $notificationsDao->getNotifications(10),
                'total_unread' => $notificationsDao->countUnreadNotification()),
            'totalPages' => ceil($studentsDao->countPurchases() / $limit),
            'currentIndex' => $index
        );
        
        $this->loadTemplate("PurchasesView", $viewArgs, Student::isLogged());
    }
}
