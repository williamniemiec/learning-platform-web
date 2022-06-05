<?php
namespace controllers;


use core\Controller;
use repositories\pdo\MySqlPDODatabase;
use domain\Student;
use dao\NotificationsDAO;
use dao\StudentsDAO;


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
        $studentsDAO = new StudentsDAO($dbConnection, $student->getId());
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
            'purchases' => $studentsDAO->getPurchases($limit, $limit * ($index - 1)),
            'notifications' => array(
                'notifications' => $notificationsDAO->getNotifications(10),
                'total_unread' => $notificationsDAO->countUnreadNotification()),
            'totalPages' => ceil($studentsDAO->countPurchases() / $limit),
            'currentIndex' => $index
        );
        
        $this->loadTemplate("PurchasesView", $viewArgs, Student::isLogged());
    }
}
