<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Courses;
use models\Classes;
use models\Doubts;
use models\Historic;


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
}
