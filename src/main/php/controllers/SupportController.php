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
        $index = $this->getIndex();
        $limit = 10;
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

    private function getIndex()
    {
        if (!$this->hasIndexBeenSent()) {
            return 1;
        }

        return ((int) $_GET['index']);
    }

    private function hasIndexBeenSent()
    {
        return  !empty($_GET['index']);
    }
    
    /**
     * Opens a support topic to read.
     */
    public function open($idTopic)
    {
        if (!$this->doesTheTopicBelongToTheLoggedInStudent($idTopic)) {
            $this->redirectTo("courses");
        }

        if ($this->hasReplyBeenSent()) {
            $this->insertReply($idTopic);
            $this->reload();
        }

        $dbConnection = new MySqlPDODatabase();
        $student = Student::getLoggedIn($dbConnection);
        $supportTopicDao = new SupportTopicDAO($dbConnection, $student->getId());
        $notificationsDao = new NotificationsDAO($dbConnection, $student->getId());
        $topic = $supportTopicDao->get($idTopic);
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

    private function doesTheTopicBelongToTheLoggedInStudent($idTopic)
    {
        $dbConnection = new MySqlPDODatabase();
        $student = Student::getLoggedIn($dbConnection);
        $supportTopicDao = new SupportTopicDAO($dbConnection, $student->getId());
        $topic = $supportTopicDao->get($idTopic);

        return !empty($topic);
    }

    private function hasReplyBeenSent()
    {
        return  !empty($_POST['topic_message']);
    }

    private function insertReply($idTopic)
    {
        $dbConnection = new MySqlPDODatabase();
        $student = Student::getLoggedIn($dbConnection);
        $supportTopicDao = new SupportTopicDAO($dbConnection, $student->getId());

        $supportTopicDao->newReply(
            $idTopic, $student->getId(), 
            $_POST['topic_message']
        );

        unset($_POST['topic_message']);
    }
    
    /**
     * Opens a support topic.
     */
    public function unlock($idTopic)
    {
        if (!$this->doesTheTopicBelongToTheLoggedInStudent($idTopic)) {
            $this->redirectTo("courses");
        }

        $dbConnection = new MySqlPDODatabase();
        $student = Student::getLoggedIn($dbConnection);
        $supportTopicDao = new SupportTopicDAO($dbConnection, $student->getId());
        
        $supportTopicDao->open($idTopic);
        $this->redirectTo("support");
    }
    
    /**
     * Closes a support topic.
     */
    public function lock($idTopic)
    {
        if (!$this->doesTheTopicBelongToTheLoggedInStudent($idTopic)) {
            $this->redirectTo("courses");
        }

        $dbConnection = new MySqlPDODatabase();
        $student = Student::getLoggedIn($dbConnection);
        $supportTopicDao = new SupportTopicDAO($dbConnection, $student->getId());

        $supportTopicDao->close($idTopic);
        $this->redirectTo("support");
    }
    
    /**
     * Creates a new topic.
     */
    public function new()
    {
        if ($this->hasFormBeenSent()) {
            $this->insertNewTopic();
            $this->redirectTo("support");
        }

        $dbConnection = new MySqlPDODatabase();
        $student = Student::getLoggedIn($dbConnection);
        $supportTopicDao = new SupportTopicDAO($dbConnection, $student->getId());
        $notificationsDao = new NotificationsDAO($dbConnection, $student->getId());
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

    private function hasFormBeenSent()
    {
        return  !empty($_POST['topic_title']) 
                && !empty($_POST['topic_category']) 
                && !empty($_POST['topic_message']);
    }

    private function insertNewTopic()
    {
        $dbConnection = new MySqlPDODatabase();
        $student = Student::getLoggedIn($dbConnection);
        $supportTopicDao = new SupportTopicDAO($dbConnection, $student->getId());

        $supportTopicDao->new(
            (int) $_POST['topic_category'], 
            $student->getId(), 
            $_POST['topic_title'], 
            $_POST['topic_message']
        );
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
        
        $topics = null;
        $dbConnection = new MySqlPDODatabase();
        $supportTopicDao = new SupportTopicDAO(
            $dbConnection, 
            Student::getLoggedIn($dbConnection)->getId()
        );
        
        if ($_POST['filter']['type'] == 0) {
            $topics = json_encode($supportTopicDao->search(
                $_POST['name'], 
                (int) $_POST['filter']['id_category'])
            );
        }
        else {
            $topics = json_encode($supportTopicDao->getAllAnsweredByCategory(
                $_POST['name'],
                (int) $_POST['filter']['id_category'])
            );
        }

        echo $topics;
    }
}
