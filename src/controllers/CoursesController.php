<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Courses;
use models\Classes;
use models\Doubts;
use models\Historic;
use models\Questionnaires;


/**
 * Responsible for the behavior of the view {@link course.php}.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class CoursesController extends Controller
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
        if (!Students::isLogged()){
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
        header("Location: ".BASE_URL); 
    }
    
    /**
     * Opens a course. The class that will be displayed it will be the last
     * watched by the student. If he never watched one, the first one will be
     * open.
     * 
     * @param       int $id_course Course id
     * @param       int $id_class [Optional] Class id to be opened
     */
    public function open($id_course, $id_class = -1)
    {
        $students = new Students($_SESSION['s_login']);
        $courses = new Courses($_SESSION['s_login']);
        $classes = new Classes();
        $historic = new Historic();
        
        
        // If student is not enrolled in the course, redirects it to home page
        if (!$courses->isEnrolled($id_course))
            header("Location: ".BASE_URL);
        
        if ($id_class == -1)
            $id_class = $students->getLastClassWatched($id_course);
        
        // Checks if a comment was sent
        if (!empty($_POST['question'])) {
            $doubts = new Doubts();
            
            
            $doubts->sendDoubt($_SESSION['s_login'], $id_class, $_POST['question']);
            header("Refresh:0");
        }
            
        // Gets class to be opened
        if ($id_class == -1)
            $class = $classes->getFirstClassFromFirstModule($id_course);
        else
            $class = $classes->getClass($id_class, $_SESSION['s_login']);
        
        // Gets information about current course
        $course = $courses->getCourse($id_course);

        // Gets class information
        if (empty($class)) {
            $name = "There are no registered classes";
            $class['type'] = "noClasses";
            $classContent = "";
            $view = "noClasses";
        } 
        else {
            if ($class['type'] == 'video') {
                $doubts = new Doubts();
                
                
                $name = $class['video']['title'];
                $classContent = array(
                    'id_class' => $class['id'],
                    'video' => $class['video'],
                    'doubts' => $doubts->getDoubts($class['id']),
                    'watched' => $class['watched']
                );
                $view = "class_video";
            }
            else {
                $name = "Questionnaire";
                $classContent = array(
                    'id_class' => $class['id'],
                    'quest' => $class['quest'],
                    'watched' => $class['watched']
                );
                $view = "class_quest";
            }
        }
        
        $header = array(
            'title' => $course['name'].' - Learning platform',
            'styles' => array('courses'),
            'description' => $name,
            'keywords' => array('learning platform', 'course', $course['name']),
            'robots' => 'index'
        );
        
        $viewArgs = array(
            'header' => $header,
            'scripts' => array('course'),
            'username' => $students->getName(),
            'info_menu' => array(
                'modules' => $course['modules'],
                'logo' => $course['logo']
            ),
            'info_course' => array(
                'title' => $name,
                'wasWatched' => $class['watched']
            ),
            'info_class' => array(
                'totalClasses' => $classes->countClasses($id_course),
                'totalWatchedClasses' => $historic->getWatchedClasses($_SESSION['s_login'], $id_course),
                'wasWatched' => $class['watched'],
                'classId' => $class['id'],
                'classType' => $class['type']
            ),
            'view' => 'class/'.$view,
            'classContent' => $classContent
        );
        
        $this->loadTemplate("class/course", $viewArgs);
    }
    
    /**
     * Opens a class within a course.
     *
     * @param       int $id_class Class id
     */
    public function class($id_class)
    {
        $classes = new Classes();
        
        
        $this->open($classes->getCourseId($id_class), $id_class);
    }
    
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
    /**
     * Gets answer from a quest.
     * 
     * @param       int $_POST['id_quest'] Quest class id
     * 
     * @return      int Correct answer [1;4] or -1 if quest id is invalid
     * 
     * @apiNote     Must be called using POST request method
     */
    public function class_getAnswer()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        if (empty($_POST['id_quest']))
            echo -1;
        
        $quests = new Questionnaires();
        
        
        echo $quests->getAnswer($_POST['id_quest']);
    }
    
    /**
     * Marks a class as watched.
     * 
     * @param       int $_POST['id_class'] Class id to be added to logged 
     * student's watched class historic
     * 
     * @apiNote     Must be called using POST request method
     */
    public function class_mark_watched()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        if (empty($_POST['id_class']))
            return;
        
        $classes = new Classes();
        
        
        $classes->markAsWatched($_SESSION['s_login'], $_POST['id_class']);
    }
    
    /**
     * Marks a class as unwatched.
     * 
     * @param       int $_POST['id_class'] Class id to be removed from logged 
     * student's watched class historic
     * 
     * @apiNote     Must be called using POST request method
     */
    public function class_remove_watched()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        if (empty($_POST['id_class']))
            return;
        
        $classes = new Classes();
        
        
        $classes->removeWatched($_SESSION['s_login'], $_POST['id_class']);
    }
    
    /**
     * Removes a comment from a class.
     * 
     * @param       int $_POST['id_comment'] Comment id to be deleted
     * 
     * @apiNote     Must be called using POST request method
     */
    public function class_remove_comment()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        if (empty($_POST['id_comment']))
            return;
        
        
        $doubts = new Doubts();
        $doubts->delete($_POST['id_comment']);
    }
    
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
    public function class_add_reply()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        if (empty($_POST['id_doubt']) || $_POST['id_doubt'] <= 0)   { return false; }
        if (empty($_POST['id_user']) || $_POST['id_user'] <= 0)     { return false; }
        if (empty($_POST['text']))                                  { return false; }
        
        $doubts = new Doubts();
        
        
        echo $doubts->addReply($_POST['id_doubt'], $_POST['id_user'], $_POST['text']);
    }
    
    /**
     * Gets student name.
     * 
     * @param       int $_POST['id_student'] Student id
     * 
     * @return      string Student name
     * 
     * @apiNote     Must be called using POST request method
     */
    public function get_student_name()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        if (empty($_POST['id_student']) || $_POST['id_student'] <= 0)
            echo "";
        
        $students = new Students();
        
        
        echo $students->get($_POST['id_student'])->getName();
    }
    
    /**
     * Removes reply from a class comment.
     * 
     * @param       int $_POST['id_reply'] Reply id
     * 
     * @apiNote     Must be called using POST request method
     */
    public function class_remove_reply()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        if (empty($_POST['id_reply']) || $_POST['id_reply'] <= 0)
            return;
        
        $doubts = new Doubts();
        
        
        $doubts->deleteReply($_POST['id_reply']);
    }
}
