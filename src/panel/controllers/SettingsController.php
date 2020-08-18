<?php
namespace controllers;


use core\Controller;
use database\pdo\MySqlPDODatabase;
use models\Admin;
use models\dao\AdminsDAO;
use models\enum\GenreEnum;


/**
 * Responsible for the behavior of the view {@link settings/settings.php}.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class SettingsController extends Controller
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
            'title' => 'Settings - Learning platform',
            'styles' => array('SettingsStyle'),
            'description' => "User settings",
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'header' => $header,
            'scripts' => array("SettingsScript"),
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'user' => $admin
        );
        
        $this->loadTemplate("settings/SettingsView", $viewArgs);
    }
    
    /**
     * Edits information about current admin.
     */
    public function edit()
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        
        $header = array(
            'title' => 'Settings - Update - Learning platform',
            'styles' => array('SettingsStyle'),
            'description' => "User settings",
            'robots' => 'noindex'
        );
        
        // Checks if edition form has been sent
        if (!empty($_POST['name'])) {
            $adminsDAO = new AdminsDAO($dbConnection, $admin);

            $adminsDAO->update($_POST['name'], new GenreEnum($_POST['genre']), $_POST['birthdate']);
            
            header("Location: ".BASE_URL."settings");
            exit;
        }
        
        $viewArgs = array(
            'header' => $header,
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'user' => $admin
        );
        
        $this->loadTemplate("settings/SettingsEditView", $viewArgs);
    }
    
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
    /**
     * Updates admin password.
     *
     * @param       string $_POST['new_password'] New password
     *
     * @return      bool If password has been successfully updated
     *
     * @apiNote     Must be called using POST request method
     */
    public function update_password()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: ".BASE_URL);
            
        $dbConnection = new MySqlPDODatabase();
        
        $adminsDAO = new AdminsDAO(
            $dbConnection,
            Admin::getLoggedIn($dbConnection)
        );
        
        echo $adminsDAO->changePassword($_POST['new_password']);
    }
}
