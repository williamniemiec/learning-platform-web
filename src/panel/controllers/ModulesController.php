<?php
namespace controllers;


use core\Controller;
use models\Admin;
use database\pdo\MySqlPDODatabase;
use models\dao\ModulesDAO;


/**
 * Responsible for the behavior of the view {@link modulesManager/modules_manager.php}.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class ModulesController extends Controller
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
        $modulesDAO = new ModulesDAO($dbConnection);
        
        $header = array(
            'title' => 'Modules manager - Learning platform',
            'styles' => array('coursesManager'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'modules' => $modulesDAO->getAll(),
            'header' => $header
        );
        
        $this->loadTemplate("modulesManager/modules_manager", $viewArgs);
    }
    
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
    /**
     * Gets all registered modules.
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
        
        $modulesDAO = new ModulesDAO($dbConnection);
        echo json_encode($modulesDAO->getAll());
    }
    
//     /**
//      * Gets highest module order in use.
//      *
//      * @param       int $_GET['id_course'] Course id
//      * @param       int $_GET['id_module'] Module id
//      * 
//      * @return      int Highest moddule order
//      *
//      * @apiNote     Must be called using GET request method
//      */
//     public function getMaxOrderInCourse()
//     {
//         if ($_SERVER['REQUEST_METHOD'] != 'GET')
//             return;
            
//         $dbConnection = new MySqlPDODatabase();
        
//         $modulesDAO = new ModulesDAO($dbConnection);
//         echo $modulesDAO->getHighestOrder((int)$_GET['id_course'], (int)$_GET['id_module']);
//     }
}
