<?php
namespace controllers;

use core\Controller;
use models\Admins;
use models\Courses;


/**
 * Responsible for the behavior of the view {@link courses_manager.php}.
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
     * It will check if admin is logged; otherwise, redirects him to login
     * page.
     */
    public function __construct()
    {
        if (!Admins::isLogged()) {
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
     * Deletes a course and redirects admin to home page.
     * 
     * @param       int $id_course Course id to be deleted
     */
    public function delete($id_course)
    {
        $courses = new Courses();
        $courses->delete($id_course);
        header("Location: ".BASE_URL);
    }
    
    /**
     * Adds a new course. If the course was successfully added, redirects
     * admin to the home page; otherwise, displays an error message.
     */
    public function add()
    {
        $admins = new Admins($_SESSION['a_login']);
        $courses = new Courses();
        
        $header = array(
            'title' => 'New course - Learning platform',
            'styles' => array('coursesEdition')
        );
        
        $viewArgs = array(
            'adminName' => $admins->getName(),
            'error' => false,
            'msg' => '',
            'header' => $header
        );
        
        // Checks if the new course was successfully added
        if (!empty($_POST['name'])) {
            if ($courses->add($_POST['name'], $_POST['description'], $_FILES['logo'])) {
                header("Location: ".BASE_URL);
                exit;
            }
            
            // If an error occurred, display it
            $viewArgs['error'] = true;
            $viewArgs['msg'] = "The course could not be added!";
        }
        
        $this->loadTemplate("coursesManager/course_add", $viewArgs);
    }
    
    /**
     * Edits a course.
     * 
     * @param       int $id_course Course id to be edited
     */
    public function edit($id_course)
    {
        if (empty($id_course) || $id_course <= 0)   { header("Location: ".BASE_URL);  }
        
        $courses = new Courses();
        $course = $courses->getCourse($id_course);
        $admins = new Admins($_SESSION['a_login']);

        $header = array(
            'title' => $course['name'].' - Learning platform',
            'styles' => array('coursesEdition')
        );
        
        $viewArgs = array(
            'adminName' => $admins->getName(),
            'course' => $course,
            'modules' => $course['modules'],
            'error' => false,
            'msg' => '',
            'header' => $header
        );
        
        // Checks if course was successfully edited
        if (!empty($_POST['name'])) {
            if ($courses->edit($id_course, $_POST['name'], $_POST['description'], $_FILES['logo'])) {
                header("Location: ".BASE_URL);
                exit;
            }
            
            // If an error occurred, display it
            $viewArgs['error'] = true;
            $viewArgs['msg'] = "Error while saving editions";
        }
        
        $this->loadTemplate("coursesManager/course_edit", $viewArgs);
    }
}
