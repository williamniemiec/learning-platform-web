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
        $this->redirectToRoot();
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
        if ($this->getHttpRequestMethod() != 'POST') {
            $this->redirectToRoot();
        }

        $dbConnection = new MySqlPDODatabase();
        $questionnaireDao = new QuestionnairesDAO($dbConnection);

        echo $questionnaireDao->getAnswer($_POST['id_module'], $_POST['class_order']);
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
    public function markWatched()
    {
        if ($this->getHttpRequestMethod() != 'POST') {
            $this->redirectToRoot();
        }

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
    public function removeWatched()
    {
        // Checks if it is an ajax request
        if ($this->getHttpRequestMethod() != 'POST') {
            $this->redirectToRoot();
        }

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
    public function newComment()
    {
        // Checks if it is an ajax request
        if ($this->getHttpRequestMethod() != 'POST') {
            $this->redirectToRoot();
        }
            
        $dbConnection = new MySqlPDODatabase();
        
        $commentsDao = new CommentsDAO($dbConnection);
        echo $commentsDao->newComment(
            Student::getLoggedIn($dbConnection)->getId(), 
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
    public function deleteComment()
    {
        if ($this->getHttpRequestMethod() != 'POST') {
           $this->redirectToRoot();
        }
    
        $dbConnection = new MySqlPDODatabase();
        $commentsDao = new CommentsDAO($dbConnection);
        
        $commentsDao->deleteComment(
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
    public function addReply()
    {
        if ($this->getHttpRequestMethod() != 'POST') {
            $this->redirectToRoot();
        }

        $dbConnection = new MySqlPDODatabase();
        $commentsDao = new CommentsDAO($dbConnection);
        
        echo $commentsDao->newReply(
            Student::getLoggedIn($dbConnection)->getId(),
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
    public function removeReply()
    {
        if ($this->getHttpRequestMethod() != 'POST') {
            $this->redirectToRoot();
        }
            
        $dbConnection = new MySqlPDODatabase();
        $commentsDao = new CommentsDAO($dbConnection);
        
        $commentsDao->deleteReply(
            $_POST['id_reply'],
            Student::getLoggedIn($dbConnection)->getId()
        );
    }
}
