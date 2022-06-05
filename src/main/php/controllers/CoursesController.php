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
        if (!Student::is_logged()) {
            $this->redirect_to("login");
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
        $db_connection = new MySqlPDODatabase();
        
        $header = array(
            'title' => 'My courses - Learning Platform',
            'styles' => array('MyCoursesStyle', 'searchBar', 'NotebookStyle'),
            'description' => "Start learning today",
            'keywords' => array('learning platform', 'courses'),
            'robots' => 'noindex'
        );
        
        $student = Student::get_logged_in($db_connection);
        $courses_dao = new CoursesDAO($db_connection);
        $notebook_dao = new NotebookDAO($db_connection, $student->get_id());
        $notifications_dao = new NotificationsDAO($db_connection, $student->get_id());
        $courses = $courses_dao->get_my_courses($student->get_id());
        $notes = $notebook_dao->get_all(4);
        $total_notes = $notebook_dao->count();
        
		$view_args = array(
		    'username' => $student->get_name(),
		    'courses' => $courses,
		    'totalCourses' => count($courses),
		    'header' => $header,
		    'notifications' => array(
		        'notifications' => $notifications_dao->get_notifications(10),
		        'total_unread' => $notifications_dao->count_unread_notification()),
		    'scripts' => array('ProgressChart'),
		    'scriptsModule' => array('MyCoursesScript'),
		    'notebook' => $notes,
		    'totalPages' => ceil($total_notes / 4)
		);

		// Checks if it is student's birthdate
		if ($student->get_birthdate()->format("m-d") == (new \DateTime())->format("m-d")) {
		    $students_dao = new StudentsDAO($db_connection);
		    $historic_info = $students_dao->get_total_watched_classes();
		    $view_args['totalWatchedVideos'] = $historic_info['total_classes_watched'];
		    $view_args['totalWatchedLength'] = $historic_info['total_length_watched'];
		}
        
        $this->load_template("MyCoursesView", $view_args, Student::is_logged());
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
        $db_connection = new MySqlPDODatabase();
        
        $student = Student::get_logged_in($db_connection);
        $students = new StudentsDAO($db_connection, $student->get_id());
        $courses = new CoursesDAO($db_connection);
        $historic = new HistoricDAO($db_connection, $student->get_id());
        $notifications_dao = new NotificationsDAO($db_connection, $student->get_id());
        
        
        // If student is not enrolled in the course, redirects it to home page
        if (!$courses->has_course($id_course, $student->get_id())) {
            $this->redirect_to_root();
        }
        
        if ($id_module > 0 && $class_order > 0) {
            $videos_dao = new VideosDAO($db_connection);
            $class = $videos_dao->get($id_module, $class_order);
            
            if (empty($class)) {
                $questionnaires_dao = new QuestionnairesDAO($db_connection);
                $class = $questionnaires_dao->get($id_module, $class_order);
            }
        }
        else {
            $class = $students->get_last_class_watched($id_course);
        }
            
        // Gets class to be opened
        if (empty($class)) {
            $class = $courses->get_first_class_from_first_module($id_course);
        }
        
        // Gets information about current course
        $course = $courses->get($id_course);

        // Gets class information
        if (empty($class)) {
            $name = 'No classes';
            //$class['type'] = "noClasses";
            $class_content = array(
                'message' => 'There are no registered classes'
            );
            $view = "noClasses";
        } 
        else {
            if ($class instanceof Video) {
                $comments_dao = new CommentsDAO($db_connection);
                $videos_dao = new VideosDAO($db_connection);
                $notebook_dao = new NotebookDAO($db_connection, $student->get_id());
  
                $name = $class->getTitle();
                $limit = 2;
                
                $class_content = array(
                    'id_course' => $id_course,
                    'class' => $class,
                    'comments' => $comments_dao->get_comments(
                        $class->get_module_id(), 
                        $class->get_class_order()
                    ),
                    'watched' => $videos_dao->was_watched(
                        $student->get_id(), $class->get_module_id(),
                        $class->get_class_order()
                    ),
                    'notebook' => $notebook_dao->get_all_from_class(
                        $class->get_module_id(),
                        $class->get_class_order(),
                        $limit
                    ),
                    'totalPages' => floor($notebook_dao->count_all_from_class(
                        $class->get_module_id(),
                        $class->get_class_order()
                    ) / $limit)
                );
                
                $view = "class_video";
            }
            else {
                $questionnaires_dao = new QuestionnairesDAO($db_connection);
                
                $name = "Questionnaire";
                
                $class_content = array(
                    'class' => $class,
                    'watched' => $questionnaires_dao->was_watched(
                        $student->get_id(), $class->get_module_id(), 
                        $class->get_class_order()
                    )
                );
                
                $view = "class_quest";
            }
        }
        
        $header = array(
            'title' => $course->get_name().' - Learning platform',
            'styles' => array('courses', 'mobile_menu_button', 'NotebookStyle'),
            'description' => $name,
            'robots' => 'noindex'
        );
        
        $view_args = array(
            'header' => $header,
            'scripts' => array('ClassScript'),
            'scriptsModule' => array('ClassNotebookScript'),
            'username' => $student->get_name(),
            'view' => 'class/'.$view,
            'info_menu' => array(
                'id_course' => $id_course,
                'modules' => $course->get_modules($db_connection, true),
                'watched_classes' => $historic->get_watched_classes_from_course($id_course),
                'logo' => $course->get_logo()
            )
        );
        
        if (!empty($class)) {
            $view_args['info_course'] = array(
                'title' => $name,
                'wasWatched' => $class_content['watched']
            );
            
            $view_args['info_class'] = array(
                'class' => $class,
                'total' => $courses->count_classes($id_course),
                'totalWatchedClasses' => $historic->count_watched_classes($id_course),
                'wasWatched' => $class_content['watched']
            );
        }
        
        $view_args['classContent'] = $class_content;
        $view_args['notifications'] = array(
            'notifications' => $notifications_dao->get_notifications(10),
            'total_unread' => $notifications_dao->count_unread_notification()
        );
        
        $this->load_template("class/course", $view_args);
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
        if ($this->get_http_request_method() != 'POST') {
            $this->redirect_to_root();
        }
        
        $db_connection = new MySqlPDODatabase();
            
        $courses_dao = new CoursesDAO($db_connection);
        
        echo json_encode($courses_dao->get_my_courses(
            Student::get_logged_in($db_connection)->get_id(), 
            $_POST['text']
        ));
    }
}
