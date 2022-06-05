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
        $this->redirect_to("login");
    }
    
    public function open($id_note)
    {   
        $db_connection = new MySqlPDODatabase();
        
        $student = Student::get_logged_in($db_connection);
        $notifications_dao = new NotificationsDAO($db_connection, $student->get_id());
        $notebook_dao = new NotebookDAO($db_connection, $student->get_id());
        $note = $notebook_dao->get($id_note);
        
        // If does not exist an note with the provided id or if it exists but
        // does not belongs to student logged in, redirects him to courses page
        if (empty($note)) {
            $this->redirect_to("courses");
        }
        
        $header = array(
            'title' => 'Notebook - Learning platform',
            'styles' => array('message'),
            'description' => "Notebook",
            'robots' => 'noindex'
        );
        
        $view_args = array(
            'header' => $header,
            'username' => $student->get_name(),
            'note' => $note,
            'notifications' => array(
                'notifications' => $notifications_dao->get_notifications(10),
                'total_unread' => $notifications_dao->count_unread_notification()),
        );
        
        $this->load_template("notebook/NotebookContentView", $view_args);
    }
    
    /**
     * Updates a note.
     * 
     * @param       int $id_note Note id
     */
    public function edit($id_note)
    {
        $db_connection = new MySqlPDODatabase();
        
        $student = Student::get_logged_in($db_connection);
        $notebook_dao = new NotebookDAO($db_connection, $student->get_id());
        $notifications_dao = new NotificationsDAO($db_connection, $student->get_id());
        $note = $notebook_dao->get($id_note);
        
        // If does not exist an note with the provided id or if it exists but 
        // does not belongs to student logged in, redirects him to courses page
        if (empty($note)) {
            $this->redirect_to("courses");
        }
        
        // Checks if form has been sent
        if (!empty($_POST['note_title']) && !empty($_POST['note_content'])) {
            $notebook_dao->update(new Note(
                $note->get_id(), 
                $_POST['note_title'], 
                $_POST['note_content'], 
                $note->get_creation_date(), 
                $note->get_class()
            ));
            
            $this->redirect_to("courses");
        }
        
        $header = array(
            'title' => 'Notebook - Learning platform',
            'styles' => array('message', 'NotebookStyle'),
            'description' => "Notebook",
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'header' => $header,
            'username' => $student->get_name(),
            'note' => $note,
            'notifications' => array(
                'notifications' => $notifications_dao->get_notifications(10),
                'total_unread' => $notifications_dao->count_unread_notification()),
        );
        
        $this->load_template("notebook/NotebookEditView", $viewArgs);
    }
    
    /**
     * Removes a note.
     *
     * @param       int $id_note Note id
     */
    public function delete($id_note)
    {
        $dbConnection = new MySqlPDODatabase();
        
        $student = Student::get_logged_in($dbConnection);
        $notebookDAO = new NotebookDAO($dbConnection, $student->get_id());
        $note = $notebookDAO->get($id_note);
        
        // If does not exist an note with the provided id or if it exists but
        // does not belongs to student logged in, redirects him to courses page
        if (empty($note)) {
            $this->redirect_to("courses");
        }
        
        $notebookDAO->delete($id_note);
        
        $this->redirect_to("courses");
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
        if ($this->get_http_request_method() != 'POST')
            $this->redirect_to_root();
            
        if (empty($_POST['title']) || empty($_POST['content']) || 
                empty($_POST['id_module']) || empty($_POST['class_order']) || 
                $_POST['id_module'] <= 0 || $_POST['class_order'] <= 0) {
            return;
        }
        
        $db_connection = new MySqlPDODatabase();
        $notebook_dao = new NotebookDAO($db_connection, Student::get_logged_in($db_connection)->get_id());
        
        echo $notebook_dao->new(
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
    public function get_all()
    {
        if ($this->get_http_request_method() != 'GET') {
            $this->redirect_to_root();
        }
        
        $db_connection = new MySqlPDODatabase();
        $notebook_dao = new NotebookDAO($db_connection, Student::get_logged_in($db_connection)->get_id());
        $offset = $_GET['limit'] * ($_GET['index'] - 1);
        
        echo json_encode(
            $notebook_dao->get_all((int) $_GET['limit'], (int) $offset)
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
    public function get_all_from_class()
    {
        // Checks if it is a GET request
        if ($this->get_http_request_method() != 'GET') {
            $this->redirect_to_root();
        }
            
        $db_connection = new MySqlPDODatabase();
        $notebook_dao = new NotebookDAO($db_connection, Student::get_logged_in($db_connection)->get_id());
        $offset = $_GET['limit'] * ($_GET['index'] - 1);
        
        echo json_encode(
            $notebook_dao->get_all_from_class(
                $_GET['id_module'],
                $_GET['class_order'],
                (int) $_GET['limit'],
                (int) $offset
            )
        );
    }
}
