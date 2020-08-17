<?php
namespace controllers;


use core\Controller;
use models\Admin;
use database\pdo\MySqlPDODatabase;
use models\dao\SupportTopicDAO;

/**
 * Responsible for the behavior of the view {@link support/support.php}.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class SupportController extends Controller
{
    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    /**
     * Checks whether admin is logged in. If he is not, redirects him to login 
     * page.
     */
    public function __construct()
    {
        if (!Admin::isLogged()) {
            header("Location: ".BASE_URL."login");
            exit;
        }
    }
    
    
    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
    /**
     * @Override
     */
    public function index ()
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $supportDAO = new SupportTopicDAO($dbConnection, $admin);
        
        $header = array(
            'title' => 'Support - Learning platform',
            'styles' => array('SupportStyle', 'searchBar'),
            'robots' => 'index'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'header' => $header,
            'topics' => $supportDAO->getAllOpened(),
            'categories' => $supportDAO->getCategories(),
            'scripts' => array('SupportScript')
        );
        
        $this->loadTemplate("support/SupportView", $viewArgs);
    }
    
    /**
     * Opens a support topic to read.
     */
    public function open($id_topic)
    {
        $dbConnection = new MySqlPDODatabase();
        
        $admin = Admin::getLoggedIn($dbConnection);
        $supportTopicDAO = new SupportTopicDAO($dbConnection, $admin);
        $topic = $supportTopicDAO->get((int)$id_topic);
        
        // If topic does not exist or it exists but does not belongs to the 
        // student logged in, redirects him to courses page
        if (empty($topic)) {
            header("Location: ".BASE_URL."support");
            exit;
        }
        
        // Checks whether a reply has been sent
        if (!empty($_POST['topic_message'])) {
            $supportTopicDAO->newReply($id_topic, $_POST['topic_message']);
            
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
            'username' => $admin->getName(),
            'topic' => $topic->setDatabase($dbConnection),
        );
        
        $this->loadTemplate("support/SupportContentView", $viewArgs);
    }
    
    /**
     * Opens a support topic.
     */
    public function unlock($id_topic)
    {
        $dbConnection = new MySqlPDODatabase();
        
        $admin = Admin::getLoggedIn($dbConnection);
        $supportTopicDAO = new SupportTopicDAO($dbConnection, $admin);
        $topic = $supportTopicDAO->get($id_topic);
        
        // If topic does not exist or it exists but does not belongs to the
        // student logged in, redirects him to courses page
        if (empty($topic)) {
            header("Location: ".BASE_URL."support");
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
        
        $admin = Admin::getLoggedIn($dbConnection);
        $supportTopicDAO = new SupportTopicDAO($dbConnection, $admin);
        $topic = $supportTopicDAO->get($id_topic);
        
        // If topic does not exist or it exists but does not belongs to the
        // student logged in, redirects him to courses page
        if (empty($topic)) {
            header("Location: ".BASE_URL."support");
            exit;
        }
        
        $supportTopicDAO->close($id_topic);
        
        header("Location: ".BASE_URL."support");
        exit;
    }
    
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
    /**
     * Searches support topics that a student has.
     *
     * @param		string $_POST['name'] Topic title
     * @param		string $_POST['filter']['id_category'] Topic category id
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
            Admin::getLoggedIn($dbConnection)
        );
        
        echo json_encode($supportTopicDAO->search(
            $_POST['name'],
            (int)$_POST['filter']['id_category']
        ));
    }
}
