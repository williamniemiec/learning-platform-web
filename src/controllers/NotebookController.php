<?php
namespace controllers;


use core\Controller;
use models\Student;
use database\pdo\MySqlPDODatabase;
use models\dao\NotebookDAO;
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
            'note' => $note
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
            'note' => $note
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
}
