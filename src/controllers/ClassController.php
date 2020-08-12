<?php
namespace controllers;


use core\Controller;
use database\pdo\MySqlPDODatabase;
use models\Student;
use models\dao\NotificationsDAO;
use models\dao\QuestionnairesDAO;
use models\dao\VideosDAO;


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
     * @param       int $_POST['id_module'] Module id to which the class belongs.
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
     * @param       int $_POST['id_module'] Module id to which the class belongs.
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
     * @param       int $_POST['id_module'] Module id to which the class belongs.
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
     * Removes a comment from a class.
     *
     * @param       int $_POST['id_comment'] Comment id to be deleted
     *
     * @apiNote     Must be called using POST request method
     */
        //     public function class_remove_comment()
        //     {
        //         // Checks if it is an ajax request
        //         if ($_SERVER['REQUEST_METHOD'] != 'POST')
            //             header("Location: ".BASE_URL);
        
            //         if (empty($_POST['id_comment']))
                //             return;
            
            
                //         $doubts = new Doubts();
                //         $doubts->delete($_POST['id_comment']);
                //     }
                            
    /**
     * Adds a reply to a class comment.
     *
     * @param       int $_POST['id_doubt'] Doubt id to be replied
     * @param       int $_POST['id_user'] User id that will reply the comment
     * @param       int $_POST['text'] Reply text
     *
     * @return      bool If the reply was successfully added
     *
     * @apiNote     Must be called using POST request method
     */
        //     public function class_add_reply()
        //     {
        //         // Checks if it is an ajax request
        //         if ($_SERVER['REQUEST_METHOD'] != 'POST')
            //             header("Location: ".BASE_URL);
        
            //         if (empty($_POST['id_doubt']) || $_POST['id_doubt'] <= 0)   { return false; }
            //         if (empty($_POST['id_user']) || $_POST['id_user'] <= 0)     { return false; }
            //         if (empty($_POST['text']))                                  { return false; }
        
            //         $doubts = new Doubts();
        
        
            //         echo $doubts->addReply($_POST['id_doubt'], $_POST['id_user'], $_POST['text']);
            //     }
                                
        /**
         * Gets student name.
         *
         * @param       int $_POST['id_student'] Student id
         *
         * @return      string Student name
         *
         * @apiNote     Must be called using POST request method
         */
            //     public function get_student_name()
            //     {
            //         // Checks if it is an ajax request
            //         if ($_SERVER['REQUEST_METHOD'] != 'POST')
                //             header("Location: ".BASE_URL);
            
                //         if (empty($_POST['id_student']) || $_POST['id_student'] <= 0)
                    //             echo "";
                
                    //         $students = new Students();
                
                
                    //         echo $students->get($_POST['id_student'])->getName();
                    //     }
                                        
    /**
     * Removes reply from a class comment.
     *
     * @param       int $_POST['id_reply'] Reply id
     *
     * @apiNote     Must be called using POST request method
     */
        //     public function class_remove_reply()
        //     {
        //         // Checks if it is an ajax request
        //         if ($_SERVER['REQUEST_METHOD'] != 'POST')
            //             header("Location: ".BASE_URL);
        
            //         if (empty($_POST['id_reply']) || $_POST['id_reply'] <= 0)
                //             return;
            
                //         $doubts = new Doubts();
            
            
                //         $doubts->deleteReply($_POST['id_reply']);
                //     }
}
