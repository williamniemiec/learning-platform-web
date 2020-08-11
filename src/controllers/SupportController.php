<?php
namespace controllers;


use core\Controller;
use models\Student;
use database\pdo\MySqlPDODatabase;
use models\dao\SupportTopicDAO;
use models\dao\NotificationsDAO;


/**
 * Responsible for the behavior of the view {@link support/support.php}.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
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
        $supportTopicDAO = new SupportTopicDAO($dbConnection, $student->getId());
        $notificationsDAO = new NotificationsDAO($dbConnection, $student->getId());
        
        $header = array(
            'title' => 'Support - Learning platform',
            'styles' => array('support'),
            'description' => "Support page",
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'header' => $header,
            'username' => $student->getName(),
            'supportTopics' => $supportTopicDAO->getAll(),
            'notifications' => array(
                'notifications' => $notificationsDAO->getNotifications(10),
                'total_unread' => $notificationsDAO->countUnreadNotification())
        );
        
        $this->loadTemplate("support/support", $viewArgs);
    }
    
    /**
     * Opens a support topic to read.
     */
    public function open($id_topic)
    {
        $dbConnection = new MySqlPDODatabase();
        
        $student = Student::getLoggedIn($dbConnection);
        $supportTopicDAO = new SupportTopicDAO($dbConnection, $student->getId());
        $notificationsDAO = new NotificationsDAO($dbConnection, $student->getId());
        $topic = $supportTopicDAO->get($id_topic);
        
        // If topic does not exist or it exists but does not belongs to the 
        // student logged in, redirects him to courses page
        if (empty($topic)) {
            header("Location: ".BASE_URL."courses");
            exit;
        }
        
        // Checks whether a reply has been sent
        if (!empty($_POST['topic_message'])) {
            $supportTopicDAO->newReply($id_topic, $student->getId(), $_POST['topic_message']);
            header("Refresh: 0");
            exit;
        }
        
        $header = array(
            'title' => 'Support - Learning platform',
            'styles' => array('support', 'message'),
            'description' => "Support topic",
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'header' => $header,
            'username' => $student->getName(),
            'topic' => $topic->setDatabase($dbConnection),
            'notifications' => array(
                'notifications' => $notificationsDAO->getNotifications(10),
                'total_unread' => $notificationsDAO->countUnreadNotification())
        );
        
        $this->loadTemplate("support/support_content", $viewArgs);
    }
    
    /**
     * Opens a support topic.
     */
    public function unlock($id_topic)
    {
        $dbConnection = new MySqlPDODatabase();
        
        $student = Student::getLoggedIn($dbConnection);
        $supportTopicDAO = new SupportTopicDAO($dbConnection, $student->getId());
        $topic = $supportTopicDAO->get($id_topic);
        
        // If topic does not exist or it exists but does not belongs to the
        // student logged in, redirects him to courses page
        if (empty($topic)) {
            header("Location: ".BASE_URL."courses");
            exit;
        }
        
        $supportTopicDAO->open($id_topic);
        
        header("Location: ".BASE_URL."support");
        exit;
    }
    
    /**
     * Closes a support topic.
     */
    public function lock($id_topic)
    {
        $dbConnection = new MySqlPDODatabase();
        
        $student = Student::getLoggedIn($dbConnection);
        $supportTopicDAO = new SupportTopicDAO($dbConnection, $student->getId());
        $topic = $supportTopicDAO->get($id_topic);
        
        // If topic does not exist or it exists but does not belongs to the
        // student logged in, redirects him to courses page
        if (empty($topic)) {
            header("Location: ".BASE_URL."courses");
            exit;
        }
        
        $supportTopicDAO->close($id_topic);
        
        header("Location: ".BASE_URL."support");
        exit;
    }
    
    /**
     * Creates a new topic.
     */
    public function new()
    {
        $dbConnection = new MySqlPDODatabase();
        
        $student = Student::getLoggedIn($dbConnection);
        $supportTopicDAO = new SupportTopicDAO($dbConnection, $student->getId());
        $notificationsDAO = new NotificationsDAO($dbConnection, $student->getId());
        
        // Checks whether form has been sent
        if (!empty($_POST['topic_title']) && !empty($_POST['topic_category']) && 
                !empty($_POST['topic_message'])) {
            $supportTopicDAO->new(
                (int)$_POST['topic_category'], 
                $student->getId(), 
                $_POST['topic_title'], 
                $_POST['topic_message']
            );
            
            header("Location: ".BASE_URL."support");
            exit;
        }
        
        $header = array(
            'title' => 'New topic - Support - Learning platform',
            'styles' => array('support'),
            'description' => "New support topic",
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'header' => $header,
            'username' => $student->getName(),
            'categories' => $supportTopicDAO->getCategories(),
            'notifications' => array(
                'notifications' => $notificationsDAO->getNotifications(10),
                'total_unread' => $notificationsDAO->countUnreadNotification())
        );
        
        $this->loadTemplate("support/support_new", $viewArgs);
    }
}
