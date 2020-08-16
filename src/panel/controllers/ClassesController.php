<?php
namespace controllers;


use core\Controller;
use models\Admin;
use database\pdo\MySqlPDODatabase;
use models\dao\VideosDAO;
use models\dao\QuestionnairesDAO;


/**
 * Responsible for the behavior of the view {@link modulesManager/modules_manager.php}.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class ClassesController extends Controller
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Checks whether admin is logged in. If he is not, redirects him to login
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
        
        
        $header = array(
            'title' => 'Modules manager - Learning platform',
            'styles' => array('coursesManager'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'classes' => $classes,
            'header' => $header
        );
        
        $this->loadTemplate("classesManager/classes_manager", $viewArgs);
    }
    
    /**
     * Creates new class.
     */
    public function new()
    {
        
    }
    
    /**
     * Edits a class.
     */
    public function edit($id_module, $class_order)
    {
        
    }
    
    /**
     * Removes a class.
     */
    public function delete($id_module, $class_order)
    {
        
    }
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
    /**
     * Gets all registered classes.
     *
     * @return      string Modules
     *
     * @apiNote     Must be called using GET request method
     */
    public function getAll()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET')
            return;
            
        $dbConnection = new MySqlPDODatabase();
        
        $classes = array();
        $videosDAO = new VideosDAO($dbConnection);
        $questionnairesDAO = new QuestionnairesDAO($dbConnection);
        
        $classes['videos'] = $videosDAO->getAll();
        $classes['questionnaires'] = $questionnairesDAO->getAll();
        
        echo json_encode($classes);
    }
}
