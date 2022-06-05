<?php
namespace controllers;


use config\Controller;
use repositories\pdo\MySqlPDODatabase;
use domain\Student;
use dao\SupportTopicDAO;
use dao\NotificationsDAO;


/**
 * Responsible for the behavior of the SupportView.
 */
class SupportController extends Controller
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
        $support_topic_dao = new SupportTopicDAO($db_connection, $student->get_id());
        $notifications_dao = new NotificationsDAO($db_connection, $student->get_id());
        $limit = 10;
        $index = 1;
        
        // Checks whether an index has been sent
        if (!empty($_GET['index'])) {
            $index = (int)$_GET['index'];
        }
        
        $offset = $limit * ($index - 1);
        
        $header = array(
            'title' => 'Support - Learning platform',
            'styles' => array('SupportStyle', 'searchBar'),
            'description' => "Support page",
            'robots' => 'noindex'
        );
        
        $view_args = array(
            'header' => $header,
            'username' => $student->get_name(),
            'scripts' => array('SupportScript'),
            'supportTopics' => $support_topic_dao->getAll($limit, $offset),
            'notifications' => array(
                'notifications' => $notifications_dao->get_notifications(10),
                'total_unread' => $notifications_dao->count_unread_notification()),
            'categories' => $support_topic_dao->get_categories(),
            'totalPages' => ceil($support_topic_dao->count() / $limit),
            'currentIndex' => $index
        );
        
        $this->load_template("support/SupportView", $view_args);
    }
    
    /**
     * Opens a support topic to read.
     */
    public function open($id_topic)
    {
        $db_connection = new MySqlPDODatabase();
        
        $student = Student::get_logged_in($db_connection);
        $support_topic_dao = new SupportTopicDAO($db_connection, $student->get_id());
        $notifications_dao = new NotificationsDAO($db_connection, $student->get_id());
        $topic = $support_topic_dao->get($id_topic);
        
        // If topic does not exist or it exists but does not belongs to the 
        // student logged in, redirects him to courses page
        if (empty($topic)) {
            $this->redirect_to("courses");
        }
        
        // Checks whether a reply has been sent
        if (!empty($_POST['topic_message'])) {
            $support_topic_dao->new_reply($id_topic, $student->get_id(), $_POST['topic_message']);
            unset($_POST['topic_message']);
            
            $this->reload();
        }
        
        $header = array(
            'title' => 'Support - Learning platform',
            'styles' => array('SupportStyle', 'message'),
            'description' => "Support topic",
            'robots' => 'noindex'
        );
        
        $view_args = array(
            'header' => $header,
            'username' => $student->get_name(),
            'topic' => $topic->set_database($db_connection),
            'notifications' => array(
                'notifications' => $notifications_dao->get_notifications(10),
                'total_unread' => $notifications_dao->count_unread_notification())
        );
        
        $this->load_template("support/SupportContentView", $view_args);
    }
    
    /**
     * Opens a support topic.
     */
    public function unlock($id_topic)
    {
        $db_connection = new MySqlPDODatabase();
        
        $student = Student::get_logged_in($db_connection);
        $support_topic_dao = new SupportTopicDAO($db_connection, $student->get_id());
        $topic = $support_topic_dao->get($id_topic);
        
        // If topic does not exist or it exists but does not belongs to the
        // student logged in, redirects him to courses page
        if (empty($topic)) {
            $this->redirect_to("courses");
        }
        
        $support_topic_dao->open($id_topic);
        
        $this->redirect_to("support");
    }
    
    /**
     * Closes a support topic.
     */
    public function lock($id_topic)
    {
        $db_connection = new MySqlPDODatabase();
        
        $student = Student::get_logged_in($db_connection);
        $support_topic_dao = new SupportTopicDAO($db_connection, $student->get_id());
        $topic = $support_topic_dao->get($id_topic);
        
        // If topic does not exist or it exists but does not belongs to the
        // student logged in, redirects him to courses page
        if (empty($topic)) {
            $this->redirect_to("courses");
        }
        
        $support_topic_dao->close($id_topic);
        
        $this->redirect_to("support");
    }
    
    /**
     * Creates a new topic.
     */
    public function new()
    {
        $dbConnection = new MySqlPDODatabase();
        
        $student = Student::get_logged_in($dbConnection);
        $support_topic_dao = new SupportTopicDAO($dbConnection, $student->get_id());
        $notifications_dao = new NotificationsDAO($dbConnection, $student->get_id());
        
        // Checks whether form has been sent
        if (!empty($_POST['topic_title']) && !empty($_POST['topic_category']) && 
                !empty($_POST['topic_message'])) {
            $support_topic_dao->new(
                (int)$_POST['topic_category'], 
                $student->get_id(), 
                $_POST['topic_title'], 
                $_POST['topic_message']
            );
            
            $this->redirect_to("support");
        }
        
        $header = array(
            'title' => 'New topic - Support - Learning platform',
            'styles' => array('SupportStyle'),
            'description' => "New support topic",
            'robots' => 'noindex'
        );
        
        $view_args = array(
            'header' => $header,
            'username' => $student->get_name(),
            'categories' => $support_topic_dao->get_categories(),
            'notifications' => array(
                'notifications' => $notifications_dao->get_notifications(10),
                'total_unread' => $notifications_dao->count_unread_notification())
        );
        
        $this->load_template("support/SupportNewView", $view_args);
    }
    
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
    /**
     * Searches support topics that a student has.
     *
     * @param		string $_POST['name'] Topic title
     * @param		string $_POST['filter']['type'] Topic type (0 for all and 1
     * for only those who have been answered)
     * @param		string $_POST['filter']['id_category'] Topic category
     * 
     * @return      string Support topics
     */
    public function search()
    {
        if ($this->get_http_request_method() != "POST") {
            return;
        }
        
        $db_connection = new MySqlPDODatabase();
        
        $support_topic_dao = new SupportTopicDAO(
            $db_connection, 
            Student::get_logged_in($db_connection)->get_id()
        );
        
        echo $_POST['filter']['type'] == 0 ?
            json_encode($support_topic_dao->search(
                $_POST['name'], 
                (int)$_POST['filter']['id_category'])) :
            json_encode($support_topic_dao->get_all_answered_by_category(
                $_POST['name'],
                (int)$_POST['filter']['id_category']));
    }
}
