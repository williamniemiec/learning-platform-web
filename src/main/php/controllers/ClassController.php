<?php
namespace controllers;


use core\Controller;
use repositories\pdo\MySqlPDODatabase;
use domain\Student;
use dao\NotificationsDAO;
use dao\QuestionnairesDAO;
use dao\VideosDAO;
use dao\CommentsDAO;


/**
 * Responsible for handling ajax requests for classes.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
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
        header("Location: ".BASE_URL);
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
    public function getAnswer()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);

        $dbConnection = new MySqlPDODatabase();
            
        $questionnaireDAO = new QuestionnairesDAO($dbConnection);

        echo $questionnaireDAO->getAnswer($_POST['id_module'], $_POST['class_order']);
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
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);

        $dbConnection = new MySqlPDODatabase();
        
        $class = $_POST['type'] == 0 ? 
                new VideosDAO($dbConnection) : 
                new QuestionnairesDAO($dbConnection);
        
        $class->markAsWatched(
            Student::getLoggedIn($dbConnection)->getId(), 
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
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);

        $dbConnection = new MySqlPDODatabase();
        
        $class = $_POST['type'] == 0 ?
                new VideosDAO($dbConnection) :
                new QuestionnairesDAO($dbConnection);
        
        $class->removeWatched(
            Student::getLoggedIn($dbConnection)->getId(),
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
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
            
        $dbConnection = new MySqlPDODatabase();
        
        $commentsDAO = new CommentsDAO($dbConnection);
        echo $commentsDAO->newComment(
            Student::getLoggedIn($dbConnection)->getId(), 
            (int)$_POST['id_course'],
            (int)$_POST['id_module'],
            (int)$_POST['class_order'],
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
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
                header("Location: ".BASE_URL);
    
        $dbConnection = new MySqlPDODatabase();
        
        $commentsDAO = new CommentsDAO($dbConnection);
        
        $commentsDAO->deleteComment(
            $_POST['id_comment'], 
            Student::getLoggedIn($dbConnection)->getId()
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
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);

        $dbConnection = new MySqlPDODatabase();
        
        $commentsDAO = new CommentsDAO($dbConnection);
        
        echo $commentsDAO->newReply(
            Student::getLoggedIn($dbConnection)->getId(),
            (int)$_POST['id_comment'],
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
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
            
        $dbConnection = new MySqlPDODatabase();
        
        $commentsDAO = new CommentsDAO($dbConnection);
        
        $commentsDAO->deleteReply(
            $_POST['id_reply'],
            Student::getLoggedIn($dbConnection)->getId()
        );
    }
}
