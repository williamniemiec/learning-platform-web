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
        if (!Student::isLogged()) {
            $this->redirectTo("login");
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
        $supportTopicDao = new SupportTopicDAO($dbConnection, $student->getId());
        $notificationsDao = new NotificationsDAO($dbConnection, $student->getId());
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
        
        $viewArgs = array(
            'header' => $header,
            'username' => $student->getName(),
            'scripts' => array('SupportScript'),
            'supportTopics' => $supportTopicDao->getAll($limit, $offset),
            'notifications' => array(
                'notifications' => $notificationsDao->getNotifications(10),
                'total_unread' => $notificationsDao->countUnreadNotification()),
            'categories' => $supportTopicDao->getCategories(),
            'totalPages' => ceil($supportTopicDao->count() / $limit),
            'currentIndex' => $index
        );
        
        $this->loadTemplate("support/SupportView", $viewArgs);
    }
    
    /**
     * Opens a support topic to read.
     */
    public function open($idTopic)
    {
        $dbConnection = new MySqlPDODatabase();
        
        $student = Student::getLoggedIn($dbConnection);
        $supportTopicDao = new SupportTopicDAO($dbConnection, $student->getId());
        $notificationsDao = new NotificationsDAO($dbConnection, $student->getId());
        $topic = $supportTopicDao->get($idTopic);
        
        // If topic does not exist or it exists but does not belongs to the 
        // student logged in, redirects him to courses page
        if (empty($topic)) {
            $this->redirectTo("courses");
        }
        
        // Checks whether a reply has been sent
        if (!empty($_POST['topic_message'])) {
            $supportTopicDao->newReply($idTopic, $student->getId(), $_POST['topic_message']);
            unset($_POST['topic_message']);
            
            $this->reload();
        }
        
        $header = array(
            'title' => 'Support - Learning platform',
            'styles' => array('SupportStyle', 'message'),
            'description' => "Support topic",
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'header' => $header,
            'username' => $student->getName(),
            'topic' => $topic->setDatabase($dbConnection),
            'notifications' => array(
                'notifications' => $notificationsDao->getNotifications(10),
                'total_unread' => $notificationsDao->countUnreadNotification())
        );
        
        $this->loadTemplate("support/SupportContentView", $viewArgs);
    }
    
    /**
     * Opens a support topic.
     */
    public function unlock($idTopic)
    {
        $dbConnection = new MySqlPDODatabase();
        
        $student = Student::getLoggedIn($dbConnection);
        $supportTopicDao = new SupportTopicDAO($dbConnection, $student->getId());
        $topic = $supportTopicDao->get($idTopic);
        
        // If topic does not exist or it exists but does not belongs to the
        // student logged in, redirects him to courses page
        if (empty($topic)) {
            $this->redirectTo("courses");
        }
        
        $supportTopicDao->open($idTopic);
        
        $this->redirectTo("support");
    }
    
    /**
     * Closes a support topic.
     */
    public function lock($idTopic)
    {
        $dbConnection = new MySqlPDODatabase();
        
        $student = Student::getLoggedIn($dbConnection);
        $supportTopicDao = new SupportTopicDAO($dbConnection, $student->getId());
        $topic = $supportTopicDao->get($idTopic);
        
        // If topic does not exist or it exists but does not belongs to the
        // student logged in, redirects him to courses page
        if (empty($topic)) {
            $this->redirectTo("courses");
        }
        
        $supportTopicDao->close($idTopic);
        
        $this->redirectTo("support");
    }
    
    /**
     * Creates a new topic.
     */
    public function new()
    {
        $dbConnection = new MySqlPDODatabase();
        
        $student = Student::getLoggedIn($dbConnection);
        $supportTopicDao = new SupportTopicDAO($dbConnection, $student->getId());
        $notificationsDao = new NotificationsDAO($dbConnection, $student->getId());
        
        // Checks whether form has been sent
        if (!empty($_POST['topic_title']) && !empty($_POST['topic_category']) && 
                !empty($_POST['topic_message'])) {
            $supportTopicDao->new(
                (int)$_POST['topic_category'], 
                $student->getId(), 
                $_POST['topic_title'], 
                $_POST['topic_message']
            );
            
            $this->redirectTo("support");
        }
        
        $header = array(
            'title' => 'New topic - Support - Learning platform',
            'styles' => array('SupportStyle'),
            'description' => "New support topic",
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'header' => $header,
            'username' => $student->getName(),
            'categories' => $supportTopicDao->getCategories(),
            'notifications' => array(
                'notifications' => $notificationsDao->getNotifications(10),
                'total_unread' => $notificationsDao->countUnreadNotification())
        );
        
        $this->loadTemplate("support/SupportNewView", $viewArgs);
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
        if ($this->getHttpRequestMethod() != "POST") {
            return;
        }
        
        $dbConnection = new MySqlPDODatabase();
        
        $supportTopicDao = new SupportTopicDAO(
            $dbConnection, 
            Student::getLoggedIn($dbConnection)->getId()
        );
        
        echo $_POST['filter']['type'] == 0 ?
            json_encode($supportTopicDao->search(
                $_POST['name'], 
                (int)$_POST['filter']['id_category'])) :
            json_encode($supportTopicDao->getAllAnsweredByCategory(
                $_POST['name'],
                (int)$_POST['filter']['id_category']));
    }
}
