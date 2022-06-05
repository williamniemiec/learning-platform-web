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
use models\dao\NotificationsDAO;


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
            'styles' => array('MyCoursesStyle', 'searchBar', 'NotebookStyle'),
            'description' => "Start learning today",
            'keywords' => array('learning platform', 'courses'),
            'robots' => 'noindex'
        );
        
        $student = Student::getLoggedIn($dbConnection);
        $coursesDAO = new CoursesDAO($dbConnection);
        $notebookDAO = new NotebookDAO($dbConnection, $student->getId());
        $notificationsDAO = new NotificationsDAO($dbConnection, $student->getId());
        $courses = $coursesDAO->getMyCourses($student->getId());
        $notes = $notebookDAO->getAll(4);
        $totNotes = $notebookDAO->count();
        
		$viewArgs = array(
		    'username' => $student->getName(),
		    'courses' => $courses,
		    'totalCourses' => count($courses),
		    'header' => $header,
		    'notifications' => array(
		        'notifications' => $notificationsDAO->getNotifications(10),
		        'total_unread' => $notificationsDAO->countUnreadNotification()),
		    'scripts' => array('ProgressChart'),
		    'scriptsModule' => array('MyCoursesScript'),
		    'notebook' => $notes,
		    'totalPages' => ceil($totNotes / 4)
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
        $notificationsDAO = new NotificationsDAO($dbConnection, $student->getId());
        
        
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
            
        // Gets class to be opened
        if (empty($class))
            $class = $courses->getFirstClassFromFirstModule($id_course);
        
        // Gets information about current course
        $course = $courses->get($id_course);

        // Gets class information
        if (empty($class)) {
            $name = 'No classes';
            //$class['type'] = "noClasses";
            $classContent = array(
                'message' => 'There are no registered classes'
            );
            $view = "noClasses";
        } 
        else {
            if ($class instanceof Video) {
                $commentsDAO = new CommentsDAO($dbConnection);
                $videosDAO = new VideosDAO($dbConnection);
                $notebookDAO = new NotebookDAO($dbConnection, $student->getId());
  
                $name = $class->getTitle();
                $limit = 2;
                
                $classContent = array(
                    'id_course' => $id_course,
                    'class' => $class,
                    'comments' => $commentsDAO->getComments(
                        $class->getModuleId(), 
                        $class->getClassOrder()
                    ),
                    'watched' => $videosDAO->wasWatched(
                        $student->getId(), $class->getModuleId(),
                        $class->getClassOrder()
                    ),
                    'notebook' => $notebookDAO->getAllFromClass(
                        $class->getModuleId(),
                        $class->getClassOrder(),
                        $limit
                    ),
                    'totalPages' => floor($notebookDAO->countAllFromClass(
                        $class->getModuleId(),
                        $class->getClassOrder()
                    ) / $limit)
                );
                
                $view = "class_video";
            }
            else {
                $questionnairesDAO = new QuestionnairesDAO($dbConnection);
                
                $name = "Questionnaire";
                
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
            'styles' => array('courses', 'mobile_menu_button', 'NotebookStyle'),
            'description' => $name,
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'header' => $header,
            'scripts' => array('ClassScript'),
            'scriptsModule' => array('ClassNotebookScript'),
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
        }
        
        $viewArgs['classContent'] = $classContent;
        $viewArgs['notifications'] = array(
            'notifications' => $notificationsDAO->getNotifications(10),
            'total_unread' => $notificationsDAO->countUnreadNotification()
        );
        
        $this->loadTemplate("class/course", $viewArgs);
    }
    
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
    /**
     * Searches for courses.
     * 
     * @param       string $_POST['text'] Name to be seearched
     * 
     * @return      string Courses with the specified name
     * 
     * @apiNote     Must be called using POST request method
     */
    public function search()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        $dbConnection = new MySqlPDODatabase();
            
        $coursesDAO = new CoursesDAO($dbConnection);
        
        echo json_encode($coursesDAO->getMyCourses(
            Student::getLoggedIn($dbConnection)->getId(), 
            $_POST['text']
        ));
    }
}
