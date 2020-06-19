<?php
namespace controllers;

use core\Controller;
use models\Admins;
use models\Courses;
use models\Classes;
use models\Modules;
use models\Videos;
use models\Questionnaires;


/**
 * Responsible for the behavior of the view {@link coursesManager/courses_manager.php}.
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
            'header' => $header,
            'scripts' => array('coursesManager')
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
            'header' => $header,
            'scripts' => array('coursesManager')
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
    
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
    /**
     * Deletes a module from a course.
     */
    public function delete_module()
    {
        if (empty($_POST['id_module'])) { return; }
        
        $modules = new Modules();
        
        $modules->delete($_POST['id_module']);
    }
    
    /**
     * Deletes a class from a course.
     */
    public function delete_class()
    {
        if (empty($_POST['id_class']))  { return; }
        
        $classes = new Classes();
        
        $classes->delete($_POST['id_class']);
    }
    
    /**
     * Adds a module to a course.
     *
     * @return      int Module id or -1 if there was an error saving the class
     */
    public function add_module()
    {
        if (empty($_POST['name']))  { echo -1; }
        
        $modules = new Modules();
        
        echo $modules->add($_POST['id_course'], $_POST['name']);
    }
    
    /**
     * Adds a video class to a course.
     *
     * @return      int Class id or -1 if there was an error adding the class
     */
    public function add_class_video()
    {
        if (empty($_POST['title'])) { echo -1; }
        
        $classes = new Classes();
        $videos = new Videos();
        
        // Adds class
        if (empty($_POST['order']))
            $classId = $classes->add($_POST['id_module'], $_POST['id_course'], 'video');
        else
            $classId = $classes->add($_POST['id_module'], $_POST['id_course'], 'video', $_POST['order']);
            
        // Adds video
        if ($classId != -1) {
            $response = $videos->add($classId, $_POST['title'], $_POST['description'], $_POST['url']);
            
            if (!$response) {
                $classes->delete($classId);
            }
        }
        
        echo $classId;
    }
    
    /**
     * Adds a quest class to a course.
     *
     * @return      int Class id or -1 if there was an error adding the class
     */
    public function add_class_quest()
    {
        if (empty($_POST['title'])) { echo -1; }
        
        $classes = new Classes();
        $quests = new Questionnaires();
        
        // Adds class
        if (empty($_POST['order']))
            $classId = $classes->add($_POST['id_module'], $_POST['id_course'], 'video');
        else
            $classId = $classes->add($_POST['id_module'], $_POST['id_course'], 'video', $_POST['order']);
            
        // Adds quest
        if ($classId != -1) {
            $response = $quests->add(
                $classId, $_POST['question'],
                $_POST['op1'],
                $_POST['op2'],
                $_POST['op3'],
                $_POST['op4'],
                $_POST['answer']
                );
            
            if (!$response) {
                $classes->delete($classId);
            }
        }
        
        echo $classId;
    }
    
    /**
     * Edits a module from a course.
     *
     * @return      bool Whether edited module was successfully saved
     */
    public function edit_module()
    {
        if (empty($_POST['id_module']) || empty($_POST['name']))    { echo false; }
        
        $modules = new Modules();
        
        echo $modules->edit($_POST['id_module'], $_POST['name']);
    }
    
    /**
     * Gets video class with the given id.
     * 
     * @return      string JSON with video class information
     */
    public function get_video()
    {
        if (empty($_POST['id_video']))  { echo json_encode(array()); }
        
        $videos = new Videos();
        
        echo json_encode($videos->get($_POST['id_video']));
    }
    
    /**
     * Gets quest with the given id.
     * 
     * @return      string JSON with quest class information
     */
    public function get_quest()
    {
        if (empty($_POST['id_quest']))  { echo json_encode(array()); }
        
        $quests = new Questionnaires();
        
        echo json_encode($quests->get($_POST['id_quest']));
    }
    
    /**
     * Edits information about a video class.
     *
     * @return      bool If video class was successfully edited
     */
    public function edit_video()
    {
        if (empty($_POST['id_video']) || empty($_POST['title']) ||
            empty($_POST['description']) || empty($_POST['url'])) {
                echo false;
        }
        
        $videos = new Videos();
        
        echo $videos->edit($_POST['id_video'], $_POST['title'], $_POST['description'], $_POST['url']);
    }
    
    /**
     * Edits information about a quest class.
     *
     * @return      bool If quest class was successfully edited
     */
    public function edit_quest()
    {
        if (empty($_POST['id_quest']) || empty($_POST['question']) ||
            empty($_POST['op1']) || empty($_POST['op2']) ||
            empty($_POST['op3']) || empty($_POST['op4']) ||
            empty($_POST['answer'])) {
                echo false;
            }
            
        $quests = new Questionnaires();
            
        echo $quests->edit(
            $_POST['id_quest'],
            $_POST['question'],
            $_POST['op1'],
            $_POST['op2'],
            $_POST['op3'],
            $_POST['op4'],
            $_POST['answer']
        );
    }
}
