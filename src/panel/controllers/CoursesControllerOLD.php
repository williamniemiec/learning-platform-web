<?php
namespace controllers;


use core\Controller;
use models\Admin;
use database\pdo\MySqlPDODatabase;
use models\dao\BundlesDAO;
use models\dao\CoursesDAO;


/**
 * Responsible for the behavior of the view {@link coursesManager/courses_manager.php}.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class CoursesControllerOLD extends Controller
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
        if (!Admin::isLogged()) {
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
        $admin = Admin::getLoggedIn($dbConnection);
        $coursesDAO = new CoursesDAO($dbConnection);
        
        $header = array(
            'title' => 'Admin area - Learning platform',
            'styles' => array('coursesManager', 'manager'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'courses' => $coursesDAO->getAll(),
            'header' => $header
        );
        
        $this->loadTemplate("coursesManager/courses_manager", $viewArgs);
    }
    
    /**
     * Deletes a course and redirects admin to home page.
     * 
     * @param       int $id_course Course id to be deleted
     */
    public function delete($id_course)
    {
        $dbConnection = new MySqlPDODatabase();
        
        $coursesDAO = new CoursesDAO(
            $dbConnection, 
            Admin::getLoggedIn($dbConnection)->getId()
        );
        
        $coursesDAO->delete($id_course);
        
        header("Location: ".BASE_URL."courses");
    }
    
    /**
     * Adds a new course. If the course has been successfully added, redirects
     * admin to the home page; otherwise, displays an error message.
     */
    public function add()
    {
        $admins = new Admins($_SESSION['a_login']);
        $courses = new Courses();
        
        $header = array(
            'title' => 'New course - Learning platform',
            'styles' => array('coursesEdition'),
            'robots' => 'index'
        );
        
        $viewArgs = array(
            'username' => $admins->getName(),
            'error' => false,
            'msg' => '',
            'header' => $header,
            'scripts' => array('coursesManager')
        );
        
        // Checks if the new course has been successfully added
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
            'styles' => array('coursesEdition'),
            'robots' => 'index'
        );
        
        $viewArgs = array(
            'username' => $admins->getName(),
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
     * 
     * @param       int $_POST['id_module'] Module id to be deleted
     *
     * @apiNote     Must be called using POST request method
     */
    public function delete_module()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        if (empty($_POST['id_module'])) { return; }
        
        $modules = new Modules();
        
        $modules->delete($_POST['id_module']);
    }
    
    /**
     * Deletes a class from a course.
     * 
     * @param       int $_POST['id_class'] Class id to be deleted
     *
     * @apiNote     Must be called using POST request method
     */
    public function delete_class()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        if (empty($_POST['id_class']))  { return; }
        
        $classes = new Classes();
        
        $classes->delete($_POST['id_class']);
    }
    
    /**
     * Adds a module to a course.
     *
     * @param       int $_POST['id_course'] Course id to which the module will
     * be added
     *
     * @return      int Module id or -1 if there was an error saving the class
     *
     * @apiNote     Must be called using POST request method
     */
    public function add_module()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        if (empty($_POST['name']))  { echo -1; }
        
        $modules = new Modules();
        
        echo $modules->add($_POST['id_course'], $_POST['name']);
    }
    
    /**
     * Adds a video class to a course.
     *
     * @param       int $_POST['title'] Class title
     * @param       int $_POST['description'] Class description
     * @param       int $_POST['id_module'] Module id to which this class will
     * be added
     * @param       int $_POST['id_course'] Course id to which this class will
     * be added
     * @param       int $_POST['url'] Video url (must be from YouTube)
     * @param       int $_POST['order'] [Optional] Class order within its module
     *
     * @return      int Class id or -1 if there was an error adding the class
     *
     * @apiNote     Must be called using POST request method
     */
    public function add_class_video()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
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
     * @param       int $_POST['question'] Question title
     * @param       int $_POST['id_course'] Course id to which this class will
     * be added
     * @param       int $_POST['id_module'] Module id to which this class will
     * be added
     * @param       int $_POST['op1'] Question option 1
     * @param       int $_POST['op2'] Question option 2
     * @param       int $_POST['op3'] Question option 3
     * @param       int $_POST['op4'] Question option 4
     * @param       int $_POST['answer'] Question answer
     * @param       int $_POST['order'] [Optional] Class order within its module
     *
     * @return      int Class id or -1 if there was an error adding the class
     * 
     * @apiNote     Must be called using POST request method
     */
    public function add_class_quest()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
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
     * @param       int $_POST['name'] New module name
     * @param       int $_POST['id_module'] Module id to be edited
     *
     * @return      bool Whether edited module was successfully saved
     * 
     * @apiNote     Must be called using POST request method
     */
    public function edit_module()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        if (empty($_POST['id_module']) || empty($_POST['name']))    { echo false; }
        
        
        $modules = new Modules();
        
        echo $modules->edit($_POST['id_module'], $_POST['name']);
    }
    
    /**
     * Gets video class with the given id.
     * 
     * @param       int $_POST['id_video'] Video id
     * 
     * @return      string JSON with video class information
     * 
     * @apiNote     Must be called using POST request method
     */
    public function get_video()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        if (empty($_POST['id_video']))  { echo json_encode(array()); }
        
        $videos = new Videos();
        
        echo json_encode($videos->get($_POST['id_video']));
    }
    
    /**
     * Gets quest with the given id.
     * 
     * @param       int $_POST['id_quest'] Question class id
     * 
     * @return      string JSON with quest class information
     * 
     * @apiNote     Must be called using POST request method
     */
    public function get_quest()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
        if (empty($_POST['id_quest']))  { echo json_encode(array()); }
        
        $quests = new Questionnaires();
        
        echo json_encode($quests->get($_POST['id_quest']));
    }
    
    /**
     * Edits information about a video class.
     * 
     * @param       int $_POST['id_video'] Video id to be edited
     * @param       int $_POST['title'] New video title
     * @param       int $_POST['description'] New video description
     * @param       int $_POST['url'] New video url (must be from YouTube)
     * 
     * @return      bool If video class was successfully edited
     * 
     * @apiNote     Must be called using POST request method
     */
    public function edit_video()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
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
     * @param       int $_POST['id_quest'] Question class id to be edited
     * @param       int $_POST['question'] New question title
     * @param       int $_POST['op1'] New question option 1
     * @param       int $_POST['op2'] New question option 2
     * @param       int $_POST['op3'] New question option 3
     * @param       int $_POST['op4'] New question option 4
     * @param       int $_POST['answer'] New question answer
     * @param       int $_POST['order'] [Optional] New class order within its module
     *
     * @return      bool If quest class was successfully edited
     * 
     * @apiNote     Must be called using POST request method
     */
    public function edit_quest()
    {
        // Checks if it is an ajax request
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
        
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
