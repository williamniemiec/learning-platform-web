<?php
namespace controllers;


use config\Controller;
use models\Admin;
use database\pdo\MySqlPDODatabase;
use models\Course;
use models\dao\CoursesDAO;
use models\util\FileUtil;
use models\util\IllegalAccessException;
use models\dao\ModulesDAO;
use models\enum\CourseOrderByEnum;
use models\enum\OrderDirectionEnum;


/**
 * Responsible for the behavior of the view {@link coursesManager/courses_manager.php}.
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
     * Checks whether admin is logged in and if he has authorization to access 
     * the page. If he is not, redirects him to login page.
     */
    public function __construct()
    {
        if (!Admin::isLogged() ||
            !(Admin::getLoggedIn(new MySqlPDODatabase())->getAuthorization()->getLevel() <= 1)) {
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
        $limit = 10;
        $index = 1;
        
        // Checks whether an index has been sent
        if (!empty($_GET['index'])) {
            $index = (int)$_GET['index'];
        }
        
        $offset = $limit * ($index - 1);
        
        $courses = $coursesDAO->getAll('', $limit, $offset);
        
        $header = array(
            'title' => 'Courses - Learning platform',
            'styles' => array('CoursesManagerStyle', 'ManagerStyle', 'searchBar'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'courses' => $courses,
            'header' => $header,
            'scripts' => array('CoursesHomeScript'),
            'totalPages' => ceil($coursesDAO->count() / $limit),
            'currentIndex' => $index
        );
        
        $this->loadTemplate("coursesManager/CoursesManagerView", $viewArgs);
    }
    
    public function new()
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $coursesDAO = new CoursesDAO($dbConnection, $admin);
        
        $header = array(
            'title' => 'New course - Learning platform',
            'styles' => array('CoursesManagerStyle', 'ManagerStyle'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'courses' => $coursesDAO->getAll('', 100),
            'header' => $header,
            'error' => false,
            'msg' => '',
            'scripts' => array('CoursesManagerScript')
        );
        
        // Checks if the new course has been successfully added
        if (!empty($_POST['name'])) {
            $description = empty($_POST['description']) ? null : $_POST['description'];
            $logo = null;
            
            // Parses logo
            if (!empty($_FILES['logo']['tmp_name'])) {
                try {
                    $logo = FileUtil::storePhoto($_FILES['logo'], "../assets/img/logos/courses/");
                }
                catch (\InvalidArgumentException $e) {
                    $viewArgs['error'] = true;
                    $viewArgs['msg'] = 'Invalid photo';
                }
            }
            
            if (!$viewArgs['error']) {
                $response = false;
                
                // Tries create new bundle. If an error occurs, removes stored
                // logo
                try {
                    $response = $coursesDAO->new(new Course(
                        -1,
                        $_POST['name'],
                        $logo,
                        $description
                    ));
                }
                catch (\InvalidArgumentException | IllegalAccessException $e) {
                    if (!empty($logo))
                        unlink("../assets/img/logos/courses/".$logo);
                }
                
                
                if ($response) {
                    header("Location: ".BASE_URL."courses");
                    exit;
                }
                
                // If an error occurred, display it
                $viewArgs['error'] = true;
                $viewArgs['msg'] = "The course could not be added!";
            }
        }
        
        $this->loadTemplate("coursesManager/CoursesManagerNewView", $viewArgs);
    }
    
    public function edit($id_course)
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $coursesDAO = new CoursesDAO($dbConnection, $admin);
        $course = $coursesDAO->get($id_course);
        
        $header = array(
            'title' => 'Edit course - Learning platform',
            'styles' => array('CoursesManagerStyle', 'ManagerStyle'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'course' => $course,
            'modules' => $course->getModules($dbConnection),
            'header' => $header,
            'error' => false,
            'msg' => '',
            'scripts' => array('CoursesManagerScript')
        );
        
        // Checks if the course has been successfully updated
        if (!empty($_POST['name'])) {
            $description = empty($_POST['description']) ? null : $_POST['description'];
            $logo = null;
            
            // Parses logo
            if (!empty($_FILES['logo']['tmp_name'])) {
                try {
                    $logo = FileUtil::storePhoto($_FILES['logo'], "../assets/img/logos/courses/");
                    
                    if (!empty($course->getLogo())) {
                        unlink("../assets/img/logos/courses/".$course->getLogo());
                    }
                        
                }
                catch (\InvalidArgumentException $e) {
                    $viewArgs['error'] = true;
                    $viewArgs['msg'] = 'Invalid photo';
                }
            }
            
            if (!$viewArgs['error']) {
                $response = false;
                
                // Tries create new bundle. If an error occurs, removes stored
                // logo
                try {
                    $response = $coursesDAO->update(new Course(
                        $course->getId(),
                        $_POST['name'],
                        $logo,
                        $description
                    ));
                }
                catch (\InvalidArgumentException | IllegalAccessException $e) {
                    if (!empty($logo))
                        unlink("../assets/img/logos/courses/".$logo);
                }
                
                
                if ($response) {
                    header("Location: ".BASE_URL."courses");
                    exit;
                }
                
                // If an error occurred, display it
                $viewArgs['error'] = true;
                $viewArgs['msg'] = "The course could not be added!";
            }
        }
        
        $this->loadTemplate("coursesManager/CoursesManagerEditView", $viewArgs);
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
            Admin::getLoggedIn($dbConnection)
        );
        
        $coursesDAO->delete($id_course);
        
        header("Location: ".BASE_URL."courses");
    }
    
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
    /**
     * Searches courses.
     *
     * @param       string $_POST['name'] Name to be searched
     * @param       string $_POST['filter']['type'] Ranking of results, which
     * can be:
     * <ul>
     *     <li>price</li>
     *     <li>sales</li>
     * </ul>
     * @param       string $_POST['filter']['order'] Sort type, which can be:
     * <ul>
     *     <li>asc (Ascending)</li>
     *     <li>desc (Descending)</li>
     * </ul>
     *
     * @return      string Json containing courses
     *
     * @apiNote     Must be called using POST request method
     */
    public function search()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            return;
            
        $dbConnection = new MySqlPDODatabase();
        
        $coursesDAO = new CoursesDAO($dbConnection);
        
        echo json_encode($coursesDAO->getAll(
            $_POST['name'],
            100,
            new CourseOrderByEnum($_POST['filter']['type']),
            new OrderDirectionEnum($_POST['filter']['order'])
        ));
    }
    
    /**
     * Gets all registered courses.
     * 
     * @return      string Courses
     * 
     * @apiNote     Must be called using GET request method
     */
    public function getAll()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET')
            return;
        
        $dbConnection = new MySqlPDODatabase();
        
        $coursesDAO = new CoursesDAO($dbConnection);
        echo json_encode($coursesDAO->getAll());
    }
    
    /**
     * Gets all modules from a course.
     *
     * @param       int $_GET['id_course'] Course id
     *
     * @return      string Modules
     *
     * @apiNote     Must be called using GET request method
     */
    public function getModules()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET')
            return;
            
        $dbConnection = new MySqlPDODatabase();
        
        $modulesDAO = new ModulesDAO($dbConnection);
        echo json_encode($modulesDAO->getFromCourse((int)$_GET['id_course']));
    }
    
    /**
     * Sets modules that a course has.
     * 
     * @param       int $_POST['id_course'] Course id
     * @param       array $_POST['modules'] Array of modules. Each position has
     * the following keys:
     * <ul>
     *  <li><b>id:</b> Module id</li>
     *  <li><b>order:</b> Module order in course</li>
     * </ul>

     * @apiNote     Must be called using POST request method
     */
    public function setModules()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            return;
            
        $dbConnection = new MySqlPDODatabase();
        
        $coursesDAO = new CoursesDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        $modulesDAO = new ModulesDAO($dbConnection);
        $modulesBackup = $modulesDAO->getFromCourse((int)$_POST['id_course']);

        try {
            $coursesDAO->deleteAllModules((int)$_POST['id_course']);
            
            foreach ($_POST['modules'] as $module) {
                $coursesDAO->addModule((int)$_POST['id_course'], (int)$module['id'], (int)$module['order']);
            }
        }
        catch(\Exception $e) {
            foreach ($modulesBackup as $module) {
                try {
                    $coursesDAO->addModule((int)$_POST['id_course'], $module->getId(), $module->getOrder());
                }
                catch(\Exception $e) {}
            }
            
            header("HTTP/1.0 500 Module order is conflicting");
            
            echo "Module order is conflicting";
        }
        
    }
}