<?php
namespace controllers;


use config\Controller;
use repositories\pdo\MySqlPDODatabase;
use domain\Student;
use domain\Video;
use dao\CommentsDAO;
use dao\CoursesDAO;
use dao\HistoricDAO;
use dao\StudentsDAO;
use dao\VideosDAO;
use dao\QuestionnairesDAO;
use dao\NotebookDAO;
use dao\NotificationsDAO;


/**
 * Responsible for the behavior of the CoursesView.
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
        if (!Student::isLogged()) {
            $this->redirectTo("login");
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
        $student = Student::getLoggedIn($dbConnection);
        $coursesDao = new CoursesDAO($dbConnection);
        $notebookDao = new NotebookDAO($dbConnection, $student->getId());
        $notificationsDao = new NotificationsDAO($dbConnection, $student->getId());
        $courses = $coursesDao->getMyCourses($student->getId());
        $notes = $notebookDao->getAll(4);
        $total_notes = $notebookDao->count();
        $header = array(
            'title' => 'My courses - Learning Platform',
            'styles' => array('MyCoursesStyle', 'searchBar', 'NotebookStyle'),
            'description' => "Start learning today",
            'keywords' => array('learning platform', 'courses'),
            'robots' => 'noindex'
        );
		$viewArgs = array(
		    'header' => $header,
		    'username' => $student->getName(),
		    'courses' => $courses,
		    'totalCourses' => count($courses),
		    'notifications' => array(
		        'notifications' => $notificationsDao->getNotifications(10),
		        'total_unread' => $notificationsDao->countUnreadNotification()),
		    'scripts' => array('ProgressChart'),
		    'scriptsModule' => array('MyCoursesScript'),
		    'notebook' => $notes,
		    'totalPages' => ceil($total_notes / 4)
		);

		if ($this->isStudentBirthday($student)) {
		    $studentsDao = new StudentsDAO($dbConnection);
		    $historicInfo = $studentsDao->getTotalWatchedClasses();
		    $viewArgs['totalWatchedVideos'] = $historicInfo['total_classes_watched'];
		    $viewArgs['totalWatchedLength'] = $historicInfo['total_length_watched'];
		}
        
        $this->loadTemplate("MyCoursesView", $viewArgs, Student::isLogged());
    }

    private function isStudentBirthday($student)
    {
        $birthday = $student->getBirthdate()->format("m-d");
        $today = (new \DateTime())->format("m-d");
        
        return $birthday == $today;
    }
    
    /**
     * Opens a course. The class that will be displayed it will be the last
     * watched by the student. If he never watched one, the first class from
     * the first module will open.
     * 
     * @param       int idCourse Course id
     * @param       int idModule [Optional] Module id to which the class 
     * belongs
     * @param       int classOrder [Optional] Class order in module
     */
    public function open(int $idCourse, int $idModule = -1, int $classOrder = -1) : void
    {
        // TODO: Refactor this method
        $dbConnection = new MySqlPDODatabase();
        $student = Student::getLoggedIn($dbConnection);
        $students = new StudentsDAO($dbConnection, $student->getId());
        $courses = new CoursesDAO($dbConnection);
        $historic = new HistoricDAO($dbConnection, $student->getId());
        $notificationsDao = new NotificationsDAO($dbConnection, $student->getId());
        
        if (!$this->isStudentEnrolledInTheCourse($idCourse, $courses, $student)) {
            $this->redirectToRoot();
        }
        
        if ($idModule > 0 && $classOrder > 0) {
            $videosDao = new VideosDAO($dbConnection);
            $class = $videosDao->get($idModule, $classOrder);
            
            if (empty($class)) {
                $questionnairesDao = new QuestionnairesDAO($dbConnection);
                $class = $questionnairesDao->get($idModule, $classOrder);
            }
        }
        else {
            $class = $students->getLastClassWatched($idCourse);
        }
        
        if (empty($class)) {
            $class = $courses->getFirstClassFromFirstModule($idCourse);
        }
        
        $course = $courses->get($idCourse);

        if (empty($class)) {
            $name = 'No classes';
            $classContent = array(
                'message' => 'There are no registered classes'
            );
            $view = "noClasses";
        } 
        else {
            if ($class instanceof Video) {
                $commentsDao = new CommentsDAO($dbConnection);
                $videosDao = new VideosDAO($dbConnection);
                $notebookDao = new NotebookDAO($dbConnection, $student->getId());
                $name = $class->getTitle();
                $limit = 2;
                $classContent = array(
                    'id_course' => $idCourse,
                    'class' => $class,
                    'comments' => $commentsDao->getComments(
                        $class->getModuleId(), 
                        $class->getClassOrder()
                    ),
                    'watched' => $videosDao->wasWatched(
                        $student->getId(), $class->getModuleId(),
                        $class->getClassOrder()
                    ),
                    'notebook' => $notebookDao->getAllFromClass(
                        $class->getModuleId(),
                        $class->getClassOrder(),
                        $limit
                    ),
                    'totalPages' => floor($notebookDao->countAllFromClass(
                        $class->getModuleId(),
                        $class->getClassOrder()
                    ) / $limit)
                );
                $view = "class_video";
            }
            else {
                $questionnairesDao = new QuestionnairesDAO($dbConnection);
                $name = "Questionnaire";
                $classContent = array(
                    'class' => $class,
                    'watched' => $questionnairesDao->wasWatched(
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
                'id_course' => $idCourse,
                'modules' => $course->getModules($dbConnection, true),
                'watched_classes' => $historic->getWatchedClassesFromCourse($idCourse),
                'logo' => $course->getLogo()
            ),
            'classContent' => $classContent,
            'notifications' => array(
                'notifications' => $notificationsDao->getNotifications(10),
                'total_unread' => $notificationsDao->countUnreadNotification()
            )
        );
        
        if (!empty($class)) {
            $viewArgs['info_course'] = array(
                'title' => $name,
                'wasWatched' => $classContent['watched']
            );
            $viewArgs['info_class'] = array(
                'class' => $class,
                'total' => $courses->countClasses($idCourse),
                'totalWatchedClasses' => $historic->countWatchedClasses($idCourse),
                'wasWatched' => $classContent['watched']
            );
        }
        
        $this->loadTemplate("class/course", $viewArgs);
    }

    private function isStudentEnrolledInTheCourse($idCourse, $courses, $student)
    {
        return $courses->hasCourse($idCourse, $student->getId());
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
        if ($this->getHttpRequestMethod() != 'POST') {
            $this->redirectToRoot();
        }
        
        $dbConnection = new MySqlPDODatabase();
        $coursesDao = new CoursesDAO($dbConnection);
        
        echo json_encode($coursesDao->getMyCourses(
            Student::getLoggedIn($dbConnection)->getId(), 
            $_POST['text']
        ));
    }
}
