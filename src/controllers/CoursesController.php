<?php
namespace controllers;


use core\Controller;
use database\pdo\MySqlPDODatabase;
use models\Student;
use models\Video;
use models\dao\CommentsDAO;
use models\dao\CoursesDAO;
use models\dao\HistoricDAO;
use models\dao\StudentsDAO;
use models\dao\VideosDAO;
use models\dao\QuestionnairesDAO;
use models\dao\NotebookDAO;



/**
 * Responsible for the behavior of the view {@link course.php}.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
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
        $dbConnection = new MySqlPDODatabase();
        
        $header = array(
            'title' => 'My courses - Learning Platform',
            'styles' => array('home', 'MyCoursesStyle', 'searchBar', 'notebook'),
            'description' => "Start learning today",
            'keywords' => array('learning platform', 'courses'),
            'robots' => 'noindex'
        );
        
        $student = Student::getLoggedIn($dbConnection);
        $coursesDAO = new CoursesDAO($dbConnection);
        $notebookDAO = new NotebookDAO($dbConnection, $student->getId());
        $courses = $coursesDAO->getMyCourses($student->getId());

		$viewArgs = array(
		    'username' => $student->getName(),
		    'courses' => $courses,
		    'totalCourses' => count($courses),
		    'header' => $header,
		    'scripts' => array('chart_progress'),
		    'notebook' => $notebookDAO->getAll()
		);

		// Checks if it is student's birthdate
		if ($student->getBirthdate()->format("m-d") == (new \DateTime())->format("m-d")) {
		    $studentsDAO = new StudentsDAO($dbConnection);
		    $historicInfo = $studentsDAO->getTotalWatchedClasses();
		    $viewArgs['totalWatchedVideos'] = $historicInfo['total_classes_watched'];
		    $viewArgs['totalWatchedLength'] = $historicInfo['total_length_watched'];
		}
        
        $this->loadTemplate("MyCoursesView", $viewArgs, Student::isLogged());
    }
    
    /**
     * Opens a course. The class that will be displayed it will be the last
     * watched by the student. If he never watched one, the first class from
     * the first module will open.
     * 
     * @param       int $id_course Course id
     * @param       int $id_module [Optional] Module id to which the class 
     * belongs
     * @param       int $class_order [Optional] Class order in module
     */
    public function open(int $id_course, int $id_module = -1, int $class_order = -1) : void
    {
        $dbConnection = new MySqlPDODatabase();
        
        $student = Student::getLoggedIn($dbConnection);
        $students = new StudentsDAO($dbConnection, $student->getId());
        $courses = new CoursesDAO($dbConnection);
        $historic = new HistoricDAO($dbConnection, $student->getId());
        //$classes = new Classes();
        $historic = new HistoricDAO($dbConnection, $student->getId());
        
        
        // If student is not enrolled in the course, redirects it to home page
        if (!$courses->hasCourse($id_course, $student->getId()))
            header("Location: ".BASE_URL);
        
        if ($id_module > 0 && $class_order > 0) {
            $videosDAO = new VideosDAO($dbConnection);
            $class = $videosDAO->get($id_module, $class_order);
            
            if (empty($class)) {
                $questionnairesDAO = new QuestionnairesDAO($dbConnection);
                $class = $questionnairesDAO->get($id_module, $class_order);
            }
        }
        else
            $class = $students->getLastClassWatched($id_course);
        
        // Checks if a comment was sent
//         if (!empty($_POST['question'])) {
//             $doubts = new Doubts();
            
            
//             $doubts->sendDoubt($_SESSION['s_login'], $id_class, $_POST['question']);
//             header("Refresh:0");
//         }
            
        // Gets class to be opened
        if (empty($class))
            $class = $courses->getFirstClassFromFirstModule($id_course);
        
        // Gets information about current course
        $course = $courses->get($id_course);

        // Gets class information
        if (empty($class)) {
            $name = '';
            $class['type'] = "noClasses";
            $classContent = array(
                'message' => 'There are no registered classes'
            );
            $view = "noClasses";
            $class['watched'] = false;
            $class['id'] = -1;
        } 
        else {
            if ($class instanceof Video) {
                $commentsDAO = new CommentsDAO($dbConnection);
                $videosDAO = new VideosDAO($dbConnection);
                $name = $class->getTitle();
                
//                 $classContent = array(
//                     'id_class' => $class['id'],
//                     'video' => $class['video'],
//                     'doubts' => $commentsDAO->get($class->getModuleId(), $class->getClassOrder()),
//                     'watched' => $class['watched']
//                 );
                $classContent = array(
                    'class' => $class,
                    'comments' => $commentsDAO->getComments(
                        $class->getModuleId(), 
                        $class->getClassOrder()
                    ),
                    'watched' => $videosDAO->wasWatched(
                        $student->getId(), $class->getModuleId(),
                        $class->getClassOrder()
                    )
                );
                
                $view = "class_video";
            }
            else {
                $questionnairesDAO = new QuestionnairesDAO($dbConnection);
                $name = "Questionnaire";
                
//                 $classContent = array(
//                     'id_class' => $class['id'],
//                     'quest' => $class['quest'],
//                     'watched' => $class['watched']
//                 );
                $classContent = array(
                    'class' => $class,
                    'watched' => $questionnairesDAO->wasWatched(
                        $student->getId(), $class->getModuleId(), 
                        $class->getClassOrder()
                    )
                );
                
                $view = "class_quest";
            }
        }
        
        $header = array(
            'title' => $course->getName().' - Learning platform',
            'styles' => array('courses', 'mobile_menu_button'),
            'description' => $name,
            //'keywords' => array('learning platform', 'course', $course['name']),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'header' => $header,
            'scripts' => array('course'),
            'username' => $student->getName(),
            'view' => 'class/'.$view,
            'info_menu' => array(
                'id_course' => $id_course,
                'modules' => $course->getModules($dbConnection, true),
                'watched_classes' => $historic->getWatchedClassesFromCourse($id_course),
                'logo' => $course->getLogo()
            )
        );
        
        if (!empty($class)) {
            $viewArgs['info_course'] = array(
                'title' => $name,
                'wasWatched' => $classContent['watched']
            );
            
            $viewArgs['info_class'] = array(
                'class' => $class,
                'total' => $courses->countClasses($id_course),
                'totalWatchedClasses' => $historic->countWatchedClasses($id_course),
                'wasWatched' => $classContent['watched']
            );
            
            $viewArgs['classContent'] = $classContent;
        }
        
        $this->loadTemplate("class/course", $viewArgs);
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
//     public function class_getAnswer()
//     {
//         // Checks if it is an ajax request
//         if ($_SERVER['REQUEST_METHOD'] != 'POST')
//             header("Location: ".BASE_URL);
        
//         if (empty($_POST['id_quest']))
//             echo -1;
        
//         $quests = new Questionnaires();
        
        
//         echo $quests->getAnswer($_POST['id_quest']);
//     }
    
    /**
     * Marks a class as watched.
     * 
     * @param       int $_POST['id_class'] Class id to be added to logged 
     * student's watched class historic
     * 
     * @apiNote     Must be called using POST request method
     */
//     public function class_mark_watched()
//     {
//         // Checks if it is an ajax request
//         if ($_SERVER['REQUEST_METHOD'] != 'POST')
//             header("Location: ".BASE_URL);
        
//         if (empty($_POST['id_class']))
//             return;
        
//         $classes = new Classes();
        
        
//         $classes->markAsWatched($_SESSION['s_login'], $_POST['id_class']);
//     }
    
    /**
     * Marks a class as unwatched.
     * 
     * @param       int $_POST['id_class'] Class id to be removed from logged 
     * student's watched class historic
     * 
     * @apiNote     Must be called using POST request method
     */
//     public function class_remove_watched()
//     {
//         // Checks if it is an ajax request
//         if ($_SERVER['REQUEST_METHOD'] != 'POST')
//             header("Location: ".BASE_URL);
        
//         if (empty($_POST['id_class']))
//             return;
        
//         $classes = new Classes();
        
        
//         $classes->removeWatched($_SESSION['s_login'], $_POST['id_class']);
//     }
    
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
