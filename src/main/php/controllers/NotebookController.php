<?php
namespace controllers;


use config\Controller;
use repositories\pdo\MySqlPDODatabase;
use domain\Student;
use domain\Note;
use dao\NotebookDAO;
use dao\NotificationsDAO;


/**
 * Responsible for the behavior of the NotebookView.
 */
class NotebookController extends Controller
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
        $this->redirectTo("login");
    }
    
    public function open($idNote)
    {   
        $db_connection = new MySqlPDODatabase();
        
        $student = Student::getLoggedIn($db_connection);
        $notificationsDao = new NotificationsDAO($db_connection, $student->getId());
        $notebookDao = new NotebookDAO($db_connection, $student->getId());
        $note = $notebookDao->get($idNote);
        
        // If does not exist an note with the provided id or if it exists but
        // does not belongs to student logged in, redirects him to courses page
        if (empty($note)) {
            $this->redirectTo("courses");
        }
        
        $header = array(
            'title' => 'Notebook - Learning platform',
            'styles' => array('message'),
            'description' => "Notebook",
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'header' => $header,
            'username' => $student->getName(),
            'note' => $note,
            'notifications' => array(
                'notifications' => $notificationsDao->getNotifications(10),
                'total_unread' => $notificationsDao->countUnreadNotification()),
        );
        
        $this->loadTemplate("notebook/NotebookContentView", $viewArgs);
    }
    
    /**
     * Updates a note.
     * 
     * @param       int idNote Note id
     */
    public function edit($idNote)
    {
        $dbConnection = new MySqlPDODatabase();
        
        $student = Student::getLoggedIn($dbConnection);
        $notebookDao = new NotebookDAO($dbConnection, $student->getId());
        $notificationsDao = new NotificationsDAO($dbConnection, $student->getId());
        $note = $notebookDao->get($idNote);
        
        // If does not exist an note with the provided id or if it exists but 
        // does not belongs to student logged in, redirects him to courses page
        if (empty($note)) {
            $this->redirectTo("courses");
        }
        
        // Checks if form has been sent
        if (!empty($_POST['note_title']) && !empty($_POST['note_content'])) {
            $notebookDao->update(new Note(
                $note->getId(), 
                $_POST['note_title'], 
                $_POST['note_content'], 
                $note->getCreationDate(), 
                $note->getClass()
            ));
            
            $this->redirectTo("courses");
        }
        
        $header = array(
            'title' => 'Notebook - Learning platform',
            'styles' => array('message', 'NotebookStyle'),
            'description' => "Notebook",
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'header' => $header,
            'username' => $student->getName(),
            'note' => $note,
            'notifications' => array(
                'notifications' => $notificationsDao->getNotifications(10),
                'total_unread' => $notificationsDao->countUnreadNotification()),
        );
        
        $this->loadTemplate("notebook/NotebookEditView", $viewArgs);
    }
    
    /**
     * Removes a note.
     *
     * @param       int idNote Note id
     */
    public function delete($idNote)
    {
        $dbConnection = new MySqlPDODatabase();
        
        $student = Student::getLoggedIn($dbConnection);
        $notebookDAO = new NotebookDAO($dbConnection, $student->getId());
        $note = $notebookDAO->get($idNote);
        
        // If does not exist an note with the provided id or if it exists but
        // does not belongs to student logged in, redirects him to courses page
        if (empty($note)) {
            $this->redirectTo("courses");
        }
        
        $notebookDAO->delete($idNote);
        
        $this->redirectTo("courses");
    }
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
    /**
     * Creates a new note.
     *
     * @param       int $_POST['id_module'] Module id to which the class belongs
     * @param       int $_POST['class_order'] Class order in module
     * @param       int $_POST['title'] Note's title
     * @param       int $_POST['content'] Note's content
     *
     * @return      int Note id or -1 if note has not been created
     *
     * @apiNote     Must be called using POST request method
     */
    public function new()
    {
        // Checks if it is a POST request
        if ($this->getHttpRequestMethod() != 'POST') {
            $this->redirectToRoot();
        }
            
        if (empty($_POST['title']) || empty($_POST['content']) || 
                empty($_POST['id_module']) || empty($_POST['class_order']) || 
                $_POST['id_module'] <= 0 || $_POST['class_order'] <= 0) {
            return;
        }
        
        $dbConnection = new MySqlPDODatabase();
        $notebookDao = new NotebookDAO($dbConnection, Student::getLoggedIn($dbConnection)->getId());
        
        echo $notebookDao->new(
            (int)$_POST['id_module'], 
            (int)$_POST['class_order'],
            $_POST['title'], 
            $_POST['content']
        );
    }
    
    /**
     * Gets user notes.
     * 
     * @param       int $_GET['index'] Pagination index
     * @param       int $_GET['limit'] Maximum of annotations displayed on the
     * screen
     * 
     * @return      string Notes
     * 
     * @apiNote     Must be called using GET request method
     */
    public function getAll()
    {
        if ($this->getHttpRequestMethod() != 'GET') {
            $this->redirectToRoot();
        }
        
        $dbConnection = new MySqlPDODatabase();
        $notebookDao = new NotebookDAO($dbConnection, Student::getLoggedIn($dbConnection)->getId());
        $offset = $_GET['limit'] * ($_GET['index'] - 1);
        
        echo json_encode(
            $notebookDao->getAll((int) $_GET['limit'], (int) $offset)
        );
    }
    
    /**
     * Gets user notes that belongs to a class.
     * 
     * @param       int $_GET['id_module'] Module id to which the class belongs
     * @param       int $_GET['class_order'] Class order in module
     * @param       int $_GET['index'] Pagination index
     * @param       int $_GET['limit'] Maximum of annotations displayed on the
     * screen
     *
     * @return      string Notes
     *
     * @apiNote     Must be called using GET request method
     */
    public function getAllFromClass()
    {
        // Checks if it is a GET request
        if ($this->getHttpRequestMethod() != 'GET') {
            $this->redirectToRoot();
        }
            
        $dbConnection = new MySqlPDODatabase();
        $notebookDao = new NotebookDAO($dbConnection, Student::getLoggedIn($dbConnection)->getId());
        $offset = $_GET['limit'] * ($_GET['index'] - 1);
        
        echo json_encode(
            $notebookDao->getAllFromClass(
                $_GET['id_module'],
                $_GET['class_order'],
                (int) $_GET['limit'],
                (int) $offset
            )
        );
    }
}
