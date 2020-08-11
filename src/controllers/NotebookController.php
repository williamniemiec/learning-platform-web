<?php
namespace controllers;


use core\Controller;
use models\Student;
use database\pdo\MySqlPDODatabase;
use models\dao\NotebookDAO;
use models\dao\NotificationsDAO;
use models\Note;


/**
 * Responsible for the behavior of the view {@link notebook_content.php}.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
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
        header("Location: ".BASE_URL."login");
        exit;
    }
    
    public function open($id_note)
    {   
        $dbConnection = new MySqlPDODatabase();
        
        $student = Student::getLoggedIn($dbConnection);
        $notificationsDAO = new NotificationsDAO($dbConnection, $student->getId());
        $notebookDAO = new NotebookDAO($dbConnection, $student->getId());
        $note = $notebookDAO->get($id_note);
        
        // If does not exist an note with the provided id or if it exists but
        // does not belongs to student logged in, redirects him to courses page
        if (empty($note)) {
            header("Location: ".BASE_URL."courses");
            exit;
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
                'notifications' => $notificationsDAO->getNotifications(10),
                'total_unread' => $notificationsDAO->countUnreadNotification()),
        );
        
        $this->loadTemplate("notebook_content", $viewArgs);
    }
    
    /**
     * Updates a note.
     * 
     * @param       int $id_note Note id
     */
    public function edit($id_note)
    {
        $dbConnection = new MySqlPDODatabase();
        
        $student = Student::getLoggedIn($dbConnection);
        $notebookDAO = new NotebookDAO($dbConnection, $student->getId());
        $notificationsDAO = new NotificationsDAO($dbConnection, $student->getId());
        $note = $notebookDAO->get($id_note);
        
        // If does not exist an note with the provided id or if it exists but 
        // does not belongs to student logged in, redirects him to courses page
        if (empty($note)) {
            header("Location: ".BASE_URL."courses");
            exit;
        }
        
        // Checks if form has been sent
        if (!empty($_POST['note_title']) && !empty($_POST['note_content'])) {
            $notebookDAO->update(new Note(
                $note->getId(), 
                $_POST['note_title'], 
                $_POST['note_content'], 
                $note->getCreationDate(), 
                $note->getClass()
            ));
            
            // Redirects student to courses page
            header("Location: ".BASE_URL."courses");
            exit;
        }
        
        $header = array(
            'title' => 'Notebook - Learning platform',
            'styles' => array('message', 'notebook'),
            'description' => "Notebook",
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'header' => $header,
            'username' => $student->getName(),
            'note' => $note,
            'notifications' => array(
                'notifications' => $notificationsDAO->getNotifications(10),
                'total_unread' => $notificationsDAO->countUnreadNotification()),
        );
        
        $this->loadTemplate("notebook_edit", $viewArgs);
    }
    
    /**
     * Removes a note.
     *
     * @param       int $id_note Note id
     */
    public function delete($id_note)
    {
        $dbConnection = new MySqlPDODatabase();
        
        $student = Student::getLoggedIn($dbConnection);
        $notebookDAO = new NotebookDAO($dbConnection, $student->getId());
        $note = $notebookDAO->get($id_note);
        
        // If does not exist an note with the provided id or if it exists but
        // does not belongs to student logged in, redirects him to courses page
        if (empty($note)) {
            header("Location: ".BASE_URL."courses");
            exit;
        }
        
        $notebookDAO->delete($id_note);
        
        header("Location: ".BASE_URL."courses");
        exit;
    }
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
    /**
     * Creates a new note.
     *
     * @param       int $_POST['id_module'] Note's title
     * @param       int $_POST['class_order'] Note's title
     * @param       int $_POST['title'] Note's title
     * @param       int $_POST['content'] Note's content
     *
     * @return      int Note id or -1 if note has not been created
     *
     * @apiNote     Must be called using POST request method
     */
    public function new()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
            
            if (empty($_POST['title']) || empty($_POST['content']) || 
                empty($_POST['id_module']) || empty($_POST['class_order']) || 
                $_POST['id_module'] <= 0 || $_POST['class_order'] <= 0) {
            return;
        }
        
        $dbConnection = new MySqlPDODatabase();
        $notebookDAO = new NotebookDAO($dbConnection, Student::getLoggedIn($dbConnection)->getId());
        
        echo $notebookDAO->new(
            (int)$_POST['id_module'], 
            (int)$_POST['class_order'],
            $_POST['title'], 
            $_POST['content']
        );
    }
}
