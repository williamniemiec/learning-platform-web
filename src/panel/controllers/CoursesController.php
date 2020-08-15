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
    
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
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
}