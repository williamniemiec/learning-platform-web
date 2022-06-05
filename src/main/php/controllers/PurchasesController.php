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
        if (!Student::is_logged()) {
            $this->redirect_to_root();
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
        $students_dao = new StudentsDAO($db_connection, $student->get_id());
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

        $view_args = array(
            'header' => $header,
            'username' => $student->get_name(),
            'purchases' => $students_dao->getPurchases($limit, $limit * ($index - 1)),
            'notifications' => array(
                'notifications' => $notifications_dao->get_notifications(10),
                'total_unread' => $notifications_dao->count_unread_notification()),
            'totalPages' => ceil($students_dao->countPurchases() / $limit),
            'currentIndex' => $index
        );
        
        $this->load_template("PurchasesView", $view_args, Student::is_logged());
    }
}
