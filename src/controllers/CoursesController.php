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
    public function open($id_course)
    {
        $students = new Students($_SESSION['s_login']);
        $courses = new Courses($_SESSION['s_login']);
        $classes = new Classes();
        
        $id_class = $students->getLastClassWatched($id_course);
        
        if (!empty($_POST['question'])) {
            $doubts = new Doubts();
            $doubts->sendDoubt($_SESSION['s_login'], $id_class, $_POST['question']);
            header("Refresh:0");
        }
        
        if ($id_class == -1) {
            $class = $classes->getFirstClassFromFirstModule($id_course);
        } else {
            $class = $classes->getClass($id_class, $_SESSION['s_login']);
        }
        
        if (!$courses->isEnrolled($id_course)) { header("Location: ".BASE_URL); }
        
        $course = $courses->getCourse($id_course);
        if (empty($class)) {
            $name = "There are no registered classes";
            $class['type'] = "noClasses";
            $embed = "";
            $view = "noClasses";
        } else {
            if ($class['type'] == 'video') {
                $doubts = new Doubts();
                
                $name = $class['video']['title'];
                $embed = array(
                    'id_class' => $class['id'],
                    'video' => $class['video'],
                    'doubts' => $doubts->getDoubts($class['id']),
                    'watched' => $class['watched']
                );
                $view = "class_video";
            } else {
                $name = "Questionnaire";
                $embed = array(
                    'id_class' => $class['id'],
                    'quest' => $class['quest'],
                    'watched' => $class['watched']
                );
                $view = "class_quest";
            }
        }
        
        $historic = new Historic();
        $viewContent = array(
            'content_title' => $name,
            'content_type' => $class['type'],
            'content_embed' => $embed,
            'totalWatchedClasses' => $historic->getWatchedClasses($_SESSION['s_login'], $id_course),
            'totalClasses' => $classes->countClasses($id_course)
        );
        
        
        $params = array(
            'title' => 'Learning platform - '.$course['name'],
            'studentName' => $students->getName(),
            'name' => $course['name'],
            'description' => $course['description'],
            'modules' => $course['modules'],
            'logo' => $course['logo'],
            'view' => $view,
            'viewContent' => $viewContent,
            'id_course' => $id_course
        );
        
        $this->loadTemplate("course", $params);
    }
    
    /**
     * Opens a class within a course.
     *
     * @param       int $id_class Class id
     */
    public function class($id_class)
    {
        if (!empty($_POST['question'])) {
            $doubts = new Doubts();
            $doubts->sendDoubt($_SESSION['s_login'], $id_class, $_POST['question']);
            header("Refresh:0");
        }
        
        $students = new Students($_SESSION['s_login']);
        $courses = new Courses($_SESSION['s_login']);
        $classes = new Classes();
        $id_course = $classes->getCourseId($id_class);
        $course = $courses->getCourse($id_course);
        
        if (!$courses->isEnrolled($id_course)) { header("Location: ".BASE_URL); }
        
        $class = $classes->getClass($id_class, $_SESSION['s_login']);
        
        if (empty($class)) {
            $name = "There are no registered classes";
            $class['type'] = "noClasses";
            $embed = "";
            $view = "noClasses";
        } else {
            if ($class['type'] == 'video') {
                $doubts = new Doubts();
                
                $name = $class['video']['title'];
                $embed = array(
                    'id_class' => $class['id'],
                    'video' => $class['video'],
                    'doubts' => $doubts->getDoubts($class['id']),
                    'watched' => $class['watched']
                );
                $view = "class_video";
            } else {
                $name = "Questionnaire";
                $embed = array(
                    'id_class' => $class['id'],
                    'quest' => $class['quest'],
                    'watched' => $class['watched']
                );
                $view = "class_quest";
            }
        }
        $historic = new Historic();
        
        $viewContent = array(
            'content_title' => $name,
            'content_type' => $class['type'],
            'content_embed' => $embed,
            'totalWatchedClasses' => $historic->getWatchedClasses($_SESSION['s_login'], $id_course),
            'totalClasses' => $classes->countClasses($id_course)
        );
        
        
        $params = array(
            'title' => 'Learning platform - '.$name,
            'studentName' => $students->getName(),
            'name' => $course['name'],
            'description' => $course['description'],
            'modules' => $course['modules'],
            'logo' => $course['logo'],
            'content_title' => $name,
            'view' => $view,
            'viewContent' => $viewContent,
            'id_course' => $id_course,
        );
        
        $this->loadTemplate("course", $params);
    }
}
