<?php
namespace controllers;


use core\Controller;
use repositories\pdo\MySqlPDODatabase;
use domain\Student;
use dao\SupportTopicDAO;
use dao\NotificationsDAO;


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
            'supportTopics' => $supportTopicDAO->getAll($limit, $offset),
            'notifications' => array(
                'notifications' => $notificationsDAO->getNotifications(10),
                'total_unread' => $notificationsDAO->countUnreadNotification()),
            'categories' => $supportTopicDAO->getCategories(),
            'totalPages' => ceil($supportTopicDAO->count() / $limit),
            'currentIndex' => $index
        );
        
        $this->loadTemplate("support/SupportView", $viewArgs);
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
            unset($_POST['topic_message']);
            header("Refresh: 0");
            exit;
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
                'notifications' => $notificationsDAO->getNotifications(10),
                'total_unread' => $notificationsDAO->countUnreadNotification())
        );
        
        $this->loadTemplate("support/SupportContentView", $viewArgs);
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
            'styles' => array('SupportStyle'),
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
        if ($_SERVER['REQUEST_METHOD'] != "POST")
            return;
        
        $dbConnection = new MySqlPDODatabase();
        
        $supportTopicDAO = new SupportTopicDAO(
            $dbConnection, 
            Student::getLoggedIn($dbConnection)->getId()
        );
        
        echo $_POST['filter']['type'] == 0 ?
                json_encode($supportTopicDAO->search(
                    $_POST['name'], 
                    (int)$_POST['filter']['id_category'])) :
                json_encode($supportTopicDAO->getAllAnsweredByCategory(
                    $_POST['name'],
                    (int)$_POST['filter']['id_category']));
    }
}
