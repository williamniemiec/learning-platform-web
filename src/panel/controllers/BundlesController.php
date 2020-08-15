<?php
namespace controllers;


use core\Controller;
use models\Admin;
use database\pdo\MySqlPDODatabase;
use models\dao\BundlesDAO;
use models\Bundle;


/**
 * Responsible for the behavior of the view {@link bundlesManager/bundles_manager.php}.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class BundlesController extends Controller
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
        $bundlesDAO = new BundlesDAO($dbConnection);
        
        $header = array(
            'title' => 'Admin area - Learning platform',
            'styles' => array('coursesManager'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'bundles' => $bundlesDAO->getAll(),
            'header' => $header
        );
        
        $this->loadTemplate("bundlesManager/bundles_manager", $viewArgs);
    }
    
    
    /**
     * Creates a new bundle.
     */
    public function add()
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $bundlesDAO = new BundlesDAO($dbConnection);
        
        // Checks if the new bundle has been successfully added
        if (!empty($_POST['name'])) {
            $response = $bundlesDAO->new(new Bundle(
                -1, 
                $_POST['name'], 
                (float)$_POST['price'], 
                $_POST['description'], 
                $_POST['logo']
            ));
            
            if ($response) {
                header("Location: ".BASE_URL);
                exit;
            }
            
            // If an error occurred, display it
            $viewArgs['error'] = true;
            $viewArgs['msg'] = "The course could not be added!";
        }
        
        $header = array(
            'title' => 'Admin area - Learning platform',
            'styles' => array('coursesManager'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'bundles' => $bundlesDAO->getAll(),
            'header' => $header,
            'error' => false,
            'msg' => ''
        );
        
        $this->loadTemplate("bundlesManager/bundles_new", $viewArgs);
    }
    
    /**
     * Updates a bundle.
     * 
     * @param       int $id_bundle Bundle id to be updated
     */
    public function edit($id_bundle)
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $bundlesDAO = new BundlesDAO($dbConnection);
        
        $header = array(
            'title' => 'Admin area - Learning platform',
            'styles' => array('bundlesManager', 'manager'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'bundle' => $bundlesDAO->get((int)$id_bundle),
            'header' => $header,
            'error' => false,
            'msg' => ''
        );
        
        $this->loadTemplate("bundlesManager/bundles_edit", $viewArgs);
    }
    
    /**
     * Removes a bundle.
     * 
     * @param       int $id_bundle Bundle id to be removed
     */
    public function delete($id_bundle)
    {
        $dbConnection = new MySqlPDODatabase();
        
        $bundlesDAO = new BundlesDAO($dbConnection, Admin::getLoggedIn($dbConnection)->getId());
        $bundlesDAO->remove((int)$id_bundle);
        
        header("Location: ".BASE_URL."bundles");
        exit;
    }
}
