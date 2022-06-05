<?php
namespace controllers;


use config\Controller;
use repositories\pdo\MySqlPDODatabase;
use domain\Student;
use dao\QuestionnairesDAO;
use dao\VideosDAO;
use dao\CommentsDAO;


/**
 * Responsible for handling ajax requests for classes.
 */
class ClassController extends Controller
{
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * @Override
     */
    public function index ()
    {
        $this->redirect_to_root();
    }
    
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
    /**
     * Gets answer from a questionnaire.
     *
     * @param       int $_POST['id_module'] Module id to which the class belongs
     * @param       int $_POST['class_order'] Class order in module 
     *
     * @return      int Correct answer [1;4] or -1 if questionnaire class does
     * not exist
     *
     * @apiNote     Must be called using POST request method
     */
    public function get_answer()
    {
        if ($this->get_http_request_method() != 'POST') {
            $this->redirect_to_root();
        }

        $db_connection = new MySqlPDODatabase();
        $questionnaire_dao = new QuestionnairesDAO($db_connection);

        echo $questionnaire_dao->get_answer($_POST['id_module'], $_POST['class_order']);
    }
    
    /**
     * Marks a class as watched.
     *
     * @param       int $_POST['id_module'] Module id to which the class belongs
     * @param       int $_POST['class_order'] Class order in module 
     * @param       int $_POST['type] Class type (0 for video and 1 for questionnaire)
     *
     * @apiNote     Must be called using POST request method
     */
    public function mark_watched()
    {
        if ($this->get_http_request_method() != 'POST') {
            $this->redirect_to_root();
        }

        $db_connection = new MySqlPDODatabase();
        
        $class = $_POST['type'] == 0 ? 
                new VideosDAO($db_connection) : 
                new QuestionnairesDAO($db_connection);
        
        $class->mark_as_watched(
            Student::get_logged_in($db_connection)->get_id(), 
            $_POST['id_module'], 
            $_POST['class_order']
        );
    }
        
    /**
     * Marks a class as unwatched.
     *
     * @param       int $_POST['id_module'] Module id to which the class belongs
     * @param       int $_POST['class_order'] Class order in module 
     * @param       int $_POST['type] Class type (0 for video and 1 for questionnaire)
     *
     * @apiNote     Must be called using POST request method
     */
    public function remove_watched()
    {
        // Checks if it is an ajax request
        if ($this->get_http_request_method() != 'POST') {
            $this->redirect_to_root();
        }

        $db_connection = new MySqlPDODatabase();
        
        $class = $_POST['type'] == 0 ?
                new VideosDAO($db_connection) :
                new QuestionnairesDAO($db_connection);
        
        $class->remove_watched(
            Student::get_logged_in($db_connection)->get_id(),
            $_POST['id_module'],
            $_POST['class_order']
        );
    }

    /**
     * Creates a new comment in a class.
     * 
     * @param       int $_POST['id_course'] Course id to which the class belongs
     * @param       int $_POST['id_module'] Module id to which the class belongs
     * @param       int $_POST['class_order'] Class order in module 
     * @param       int $_POST['content'] Comment content
     * 
     * @return      int Comment id or -1 if comment has not been added 
     * 
     * @apiNote     Must be called using POST request method
     */
    public function new_comment()
    {
        // Checks if it is an ajax request
        if ($this->get_http_request_method() != 'POST') {
            $this->redirect_to_root();
        }
            
        $db_connection = new MySqlPDODatabase();
        
        $comments_dao = new CommentsDAO($db_connection);
        echo $comments_dao->new_comment(
            Student::get_logged_in($db_connection)->get_id(), 
            (int) $_POST['id_course'],
            (int) $_POST['id_module'],
            (int) $_POST['class_order'],
            $_POST['content']
        );
    }
    
    /**
     * Removes a comment from a class.
     *
     * @param       int $_POST['id_comment'] Comment id to be deleted
     *
     * @apiNote     Must be called using POST request method
     */
    public function delete_comment()
    {
        if ($this->get_http_request_method() != 'POST') {
           $this->redirect_to_root();
        }
    
        $db_connection = new MySqlPDODatabase();
        $comments_dao = new CommentsDAO($db_connection);
        
        $comments_dao->delete_comment(
            $_POST['id_comment'], 
            Student::get_logged_in($db_connection)->get_id()
        );
    }
                            
    /**
     * Adds a reply to a class comment.
     *
     * @param       int $_POST['id_comment'] Comment id to be replied
     * @param       int $_POST['content'] Comment content
     * 
     * @return      int Reply id or -1 if reply has not been added 
     * 
     * @apiNote     Must be called using POST request method
     */
    public function add_reply()
    {
        if ($this->get_http_request_method() != 'POST') {
            $this->redirect_to_root();
        }

        $db_connection = new MySqlPDODatabase();
        $comments_dao = new CommentsDAO($db_connection);
        
        echo $comments_dao->new_reply(
            Student::get_logged_in($db_connection)->get_id(),
            (int) $_POST['id_comment'],
            $_POST['content']
        );
    }
                                        
    /**
     * Removes reply from a class comment.
     *
     * @param       int $_POST['id_reply'] Reply id
     *
     * @apiNote     Must be called using POST request method
     */
    public function remove_reply()
    {
        if ($this->get_http_request_method() != 'POST') {
            $this->redirect_to_root();
        }
            
        $db_connection = new MySqlPDODatabase();
        $comments_dao = new CommentsDAO($db_connection);
        
        $comments_dao->deleteReply(
            $_POST['id_reply'],
            Student::get_logged_in($db_connection)->get_id()
        );
    }
}
