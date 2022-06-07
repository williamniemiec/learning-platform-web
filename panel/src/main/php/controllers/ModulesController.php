<?php
namespace controllers;


use config\Controller;
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
        $modulesDAO = new ModulesDAO($dbConnection);
        $limit = 10;
        $index = 1;
        
        // Checks whether an index has been sent
        if (!empty($_GET['index'])) {
            $index = (int)$_GET['index'];
        }
        
        $offset = $limit * ($index - 1);
        
        $modules = $modulesDAO->getAll($limit, $offset);
        
        $header = array(
            'title' => 'Modules manager - Learning platform',
            'styles' => array('ManagerStyle'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'modules' => $modules,
            'header' => $header,
            'totalPages' => ceil($modulesDAO->count() / $limit),
            'currentIndex' => $index
        );
        
        $this->loadTemplate("modulesManager/ModulesManagerView", $viewArgs);
    }
    
    /**
     * Creates new module.
     */
    public function new()
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        
        $header = array(
            'title' => 'Modules manager - New - Learning platform',
            'styles' => array('modulesManager'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'header' => $header,
            'error' => false,
            'msg' => ''
        );
        
        // Checks if the new course has been successfully added
        if (!empty($_POST['name'])) {
            $modulesDAO = new ModulesDAO($dbConnection, $admin);
            
            if ($modulesDAO->new($_POST['name']) != -1) {
                header("Location: ".BASE_URL."modules");
                exit;
            }
            
            // If an error occurred, display it
            $viewArgs['error'] = true;
            $viewArgs['msg'] = "The module could not be added!";
        }
        
        $this->loadTemplate("modulesManager/ModulesManagerNewView", $viewArgs);
    }
    
    /**
     * Edits a module.
     */
    public function edit($id_module)
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $modulesDAO = new ModulesDAO($dbConnection, $admin);
        $module = $modulesDAO->get((int)$id_module);
        
        $header = array(
            'title' => 'Modules manager - New - Learning platform',
            'styles' => array('ModulesManagerStyle'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'header' => $header,
            'module' => $module,
            'classes' => $module->getClasses($dbConnection),
            'error' => false,
            'msg' => '',
            'scripts' => array('ModulesManagerStyle')
        );
        
        // Checks if course has been successfully updated
        if (!empty($_POST['name'])) {
            $modulesDAO = new ModulesDAO($dbConnection, $admin);
            
            if ($modulesDAO->update((int)$id_module, $_POST['name']) != -1) {
                header("Location: ".BASE_URL."modules");
                exit;
            }
            
            // If an error occurred, display it
            $viewArgs['error'] = true;
            $viewArgs['msg'] = "The module could not be added!";
        }
        
        $this->loadTemplate("modulesManager/ModulesManagerEditView", $viewArgs);
    }
    
    /**
     * Removes a module.
     */
    public function delete($id_module)
    {
        $dbConnection = new MySqlPDODatabase();
        
        $modulesDAO = new ModulesDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        $modulesDAO->delete((int)$id_module);
        
        header("Location: ".BASE_URL."modules");
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
    
    /**
     * Gets informations about all classes from a module.
     * 
     * @param       int $_GET['id_module'] Module id
     * 
     * @return      array All classes from the module
     * 
     * @apiNote     Array is ordered according to the order of classes in module
     * @apiNote     Must be called using GET request method
     */
    public function getClasses()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET')
            return;
        
        $dbConnection = new MySqlPDODatabase();
        
        $modulesDAO = new ModulesDAO($dbConnection);
        
        echo json_encode($modulesDAO->getClassesFromModule((int)$_GET['id_module']));
    }
    
    /**
     * Sets classes that a module has.
     *
     * @param       int $_POST['id_module'] Module id
     * @param       array $_POST['classes'] Array of classes. Each position has
     * the following keys:
     * <ul>
     *  <li><b>id_module:</b> Module to which the class belongs to</li>
     *  <li><b>type:</b> 'video' or 'questionnaire'</li>
     *  <li><b>order_old:</b> Current class order in module</li>
     *  <li><b>order_new:</b> New class order in module</li>
     * </ul>
     
     * @apiNote     Must be called using POST request method
     */
    public function setClasses()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            return;
            
        $dbConnection = new MySqlPDODatabase();
        
        try {
            $modulesDAO = new ModulesDAO($dbConnection, Admin::getLoggedIn($dbConnection));
            $modulesDAO->addClasses((int)$_POST['id_module'], $_POST['classes']);
        }
        catch (\Exception $e) {
            header("HTTP/1.0 500 ".$e->getMessage());
            
            echo $e->getMessage();
        }
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
