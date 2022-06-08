<?php
namespace panel\controllers;


use panel\config\Controller;
use panel\repositories\pdo\MySqlPDODatabase;
use panel\domain\Admin;
use panel\dao\SupportTopicDAO;


/**
 * Responsible for the behavior of the SupportView.
 */
class SupportController extends Controller
{
    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    /**
     * Checks whether admin is logged in and if he has authorization to access 
     * the page. If he is not, redirects him to login page.
     */
    public function __construct()
    {
        if (!Admin::isLogged() || !$this->hasLoggedAdminAuthorization(0, 2)) {
            $this->redirectTo("login");
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
        $supportDao = new SupportTopicDAO($dbConnection, $admin);
        $index = $this->getIndex();
        $limit = 10;
        $offset = $limit * ($index - 1);
        $header = array(
            'title' => 'Support - Learning platform',
            'styles' => array('SupportStyle', 'searchBar'),
            'robots' => 'index'
        );
        $viewArgs = array(
            'header' => $header,
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'topics' => $supportDao->getAllOpened($limit, $offset),
            'categories' => $supportDao->getCategories(),
            'scripts' => array('SupportScript'),
            'totalPages' => ceil($supportDao->count() / $limit),
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
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $supportTopicDao = new SupportTopicDAO($dbConnection, $admin);
        $topic = $supportTopicDao->get((int) $idTopic);
        
        if (empty($topic)) {
            $this->redirectTo("support");
        }

        if ($this->hasReplyBeenSent()) {
            $this->insertReply($idTopic);
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
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'topic' => $topic->setDatabase($dbConnection),
        );
        
        $this->loadTemplate("support/SupportContentView", $viewArgs);
    }

    private function hasReplyBeenSent()
    {
        return  !empty($_POST['topic_message']);
    }

    private function insertReply($idTopic)
    {
        $dbConnection = new MySqlPDODatabase();
            $admin = Admin::getLoggedIn($dbConnection);
            $supportTopicDao = new SupportTopicDAO($dbConnection, $admin);
            
            $supportTopicDao->newReply(
                $idTopic, 
                $_POST['topic_message']
            );
            
            unset($_POST['topic_message']);
    }
    
    /**
     * Opens a support topic.
     */
    public function unlock($idTopic)
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $supportTopicDao = new SupportTopicDAO($dbConnection, $admin);
        $topic = $supportTopicDao->get($idTopic);

        if (!empty($topic)) {
            $supportTopicDao->open($idTopic);
        }
        
        $this->redirectTo("support");
    }
    
    /**
     * Closes a support topic.
     */
    public function lock($idTopic)
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $supportTopicDAO = new SupportTopicDAO($dbConnection, $admin);
        $topic = $supportTopicDAO->get($idTopic);

        if (!empty($topic)) {
            $supportTopicDAO->close($idTopic);
        }

        $this->redirectTo("support");
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
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
            return;
        }
            
        $dbConnection = new MySqlPDODatabase();
        
        $supportTopicDao = new SupportTopicDAO(
            $dbConnection,
            Admin::getLoggedIn($dbConnection)
        );
        
        echo json_encode($supportTopicDao->search(
            $_POST['name'],
            (int) $_POST['filter']['id_category']
        ));
    }
}
