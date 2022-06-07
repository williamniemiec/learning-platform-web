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
        if (!$this->doesTheNoteBelongToTheLoggedInStudent($idNote)) {
            $this->redirectTo("courses");
        }

        $dbConnection = new MySqlPDODatabase();
        $student = Student::getLoggedIn($dbConnection);
        $notificationsDao = new NotificationsDAO($dbConnection, $student->getId());
        $notebookDao = new NotebookDAO($dbConnection, $student->getId());
        $note = $notebookDao->get($idNote);
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

    private function doesTheNoteBelongToTheLoggedInStudent($idNote)
    {
        $dbConnection = new MySqlPDODatabase();
        $student = Student::getLoggedIn($dbConnection);
        $notebookDao = new NotebookDAO($dbConnection, $student->getId());
        $note = $notebookDao->get($idNote);

        return !empty($note);
    }
    
    /**
     * Updates a note.
     * 
     * @param       int idNote Note id
     */
    public function edit($idNote)
    {
        if (!$this->doesTheNoteBelongToTheLoggedInStudent($idNote)) {
            $this->redirectTo("courses");
        }

        if ($this->hasEditBeenSent()) {
            $this->updateNote($idNote);
            $this->redirectTo("courses");
        }
        
        $header = array(
            'title' => 'Notebook - Learning platform',
            'styles' => array('message', 'NotebookStyle'),
            'description' => "Notebook",
            'robots' => 'noindex'
        );
        $dbConnection = new MySqlPDODatabase();
        $student = Student::getLoggedIn($dbConnection);
        $notificationsDao = new NotificationsDAO($dbConnection, $student->getId());
        $notebookDao = new NotebookDAO($dbConnection, $student->getId());
        $note = $notebookDao->get($idNote);
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

    private function hasEditBeenSent()
    {
        return  !empty($_POST['note_title']) 
                && !empty($_POST['note_content']);
    }

    private function updateNote($idNote)
    {
        $dbConnection = new MySqlPDODatabase();
        $student = Student::getLoggedIn($dbConnection);
        $notebookDao = new NotebookDAO($dbConnection, $student->getId());
        $note = $notebookDao->get($idNote);
        
        $notebookDao->update(new Note(
            $note->getId(), 
            $_POST['note_title'], 
            $_POST['note_content'], 
            $note->getCreationDate(), 
            $note->getClass()
        ));
    }
    
    /**
     * Removes a note.
     *
     * @param       int idNote Note id
     */
    public function delete($idNote)
    {
        if (!$this->doesTheNoteBelongToTheLoggedInStudent($idNote)) {
            $this->redirectTo("courses");
        }

        $dbConnection = new MySqlPDODatabase();
        $student = Student::getLoggedIn($dbConnection);
        $notebookDAO = new NotebookDAO($dbConnection, $student->getId());
        
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
        if ($this->getHttpRequestMethod() != 'POST') {
            $this->redirectToRoot();
        }
            
        if (!$this->hasNewNoteBeenSent()) {
            return;
        }
        
        $dbConnection = new MySqlPDODatabase();
        $notebookDao = new NotebookDAO(
            $dbConnection, 
            Student::getLoggedIn($dbConnection)->getId()
        );
        
        echo $notebookDao->new(
            (int) $_POST['id_module'], 
            (int) $_POST['class_order'],
            $_POST['title'], 
            $_POST['content']
        );
    }

    private function hasNewNoteBeenSent()
    {
        return  !empty($_POST['title']) 
                && !empty($_POST['content']) 
                && !empty($_POST['id_module']) 
                && !empty($_POST['class_order']) 
                && $_POST['id_module'] > 0 
                && $_POST['class_order'] > 0;
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
        $notebookDao = new NotebookDAO(
            $dbConnection, 
            Student::getLoggedIn($dbConnection)->getId()
        );
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
        if ($this->getHttpRequestMethod() != 'GET') {
            $this->redirectToRoot();
        }
            
        $dbConnection = new MySqlPDODatabase();
        $notebookDao = new NotebookDAO(
            $dbConnection, 
            Student::getLoggedIn($dbConnection)->getId()
        );
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
