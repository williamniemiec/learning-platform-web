<?php
namespace panel\controllers;


use panel\config\Controller;
use panel\repositories\pdo\MySqlPDODatabase;
use panel\domain\Admin;
use panel\dao\ModulesDAO;


/**
 * Responsible for the behavior of the ModulesManagerView.
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
        if (!Admin::isLogged() || !$this->hasLoggedAdminAuthorization(0, 1)) {
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
        $limit = 10;
        $index = $this->getIndex();
        $offset = $limit * ($index - 1);
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $modulesDao = new ModulesDAO($dbConnection);   
        $header = array(
            'title' => 'Modules manager - Learning platform',
            'styles' => array('ManagerStyle'),
            'robots' => 'noindex'
        );
        $viewArgs = array(
            'header' => $header,
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'modules' => $modulesDao->getAll($limit, $offset),
            'totalPages' => ceil($modulesDao->count() / $limit),
            'currentIndex' => $index
        );
        
        $this->loadTemplate("modulesManager/ModulesManagerView", $viewArgs);
    }

    private function getIndex()
    {
        if (!$this->hasIndexBeenSent()) {
            return 1;
        }

        return ((int) $_GET['index']);
    }

    private function hasIndexBeenSent()
    {
        return  !empty($_GET['index']);
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
            'header' => $header,
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'error' => false,
            'msg' => ''
        );
        
        if ($this->hasFormBeenSent()) {
            $success = $this->createNewModule($dbConnection, $admin);
            
            if ($success) {
                $this->redirectTo("modules");
            }
            
            $viewArgs['error'] = true;
            $viewArgs['msg'] = "The module could not be added!";
        }
        
        $this->loadTemplate("modulesManager/ModulesManagerNewView", $viewArgs);
    }

    private function hasFormBeenSent()
    {
        return !empty($_POST['name']);
    }

    private function createNewModule($dbConnection, $admin)
    {
        $modulesDao = new ModulesDAO($dbConnection, $admin);
            
        return ($modulesDao->new($_POST['name']) != -1);
    }
    
    /**
     * Edits a module.
     */
    public function edit($idModule)
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $modulesDao = new ModulesDAO($dbConnection, $admin);
        $module = $modulesDao->get((int) $idModule);
        $header = array(
            'title' => 'Modules manager - New - Learning platform',
            'styles' => array('ModulesManagerStyle'),
            'robots' => 'noindex'
        );
        $viewArgs = array(
            'header' => $header,
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'module' => $module,
            'classes' => $module->getClasses($dbConnection),
            'error' => false,
            'msg' => '',
            'scripts' => array('ModulesManagerScript')
        );
        
        if ($this->hasFormBeenSent()) {
            $success = $this->updateModule($dbConnection, $admin, $idModule);
            
            if ($success) {
                $this->redirectTo("modules");
            }
            
            $viewArgs['error'] = true;
            $viewArgs['msg'] = "The module could not be added!";
        }
        
        $this->loadTemplate("modulesManager/ModulesManagerEditView", $viewArgs);
    }

    private function updateModule($dbConnection, $admin, $moduleId)
    {
        $modulesDao = new ModulesDAO($dbConnection, $admin);
            
        return ($modulesDao->update((int) $moduleId, $_POST['name']) != -1);
    }
    
    /**
     * Removes a module.
     */
    public function delete($idModule)
    {
        $dbConnection = new MySqlPDODatabase();
        $modulesDao = new ModulesDAO(
            $dbConnection, 
            Admin::getLoggedIn($dbConnection)
        );
        
        $modulesDao->delete((int)$idModule);
        $this->redirectTo("modules");
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
        if ($this->getHttpRequestMethod() != 'GET') {
            return;
        }
            
        $dbConnection = new MySqlPDODatabase();
        $modulesDao = new ModulesDAO($dbConnection);
        
        echo json_encode($modulesDao->getAll());
    }
    
    /**
     * Gets information about all classes from a module.
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
        if ($this->getHttpRequestMethod() != 'GET') {
            return;
        }
        
        $dbConnection = new MySqlPDODatabase();
        $modulesDao = new ModulesDAO($dbConnection);
        
        echo json_encode($modulesDao->getClassesFromModule((int) $_GET['id_module']));
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
        if ($this->getHttpRequestMethod() != 'POST') {
            return;
        }

        try {
            $this->addClasses();
        }
        catch (\Exception $e) {
            header("HTTP/1.0 500 ".$e->getMessage());
            echo $e->getMessage();
        }
    }

    private function addClasses()
    {
        $dbConnection = new MySqlPDODatabase();
        $modulesDao = new ModulesDAO(
            $dbConnection, 
            Admin::getLoggedIn($dbConnection)
        );

        $modulesDao->addClasses(
            (int) $_POST['id_module'], 
            $_POST['classes']
        );
    }
}
