<?php
namespace panel\controllers;


use panel\config\Controller;
use panel\repositories\pdo\MySqlPDODatabase;
use panel\domain\Admin;
use panel\dao\AdminsDAO;
use panel\domain\enum\GenreEnum;


/**
 * Responsible for the behavior of the SettingsView.
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
        
        if ($this->hasUpdateBeenSent()) {
            $this->updateAdmin($dbConnection, $admin);
            $this->redirectTo("settings");
        }
        
        $header = array(
            'title' => 'Settings - Update - Learning platform',
            'styles' => array('SettingsStyle'),
            'description' => "User settings",
            'robots' => 'noindex'
        );
        $viewArgs = array(
            'header' => $header,
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'user' => $admin
        );
        
        $this->loadTemplate("settings/SettingsEditView", $viewArgs);
    }
    
    private function hasUpdateBeenSent()
    {
        return !empty($_POST['name']);
    }

    private function updateAdmin($dbConnection, $admin)
    {
        $adminsDao = new AdminsDAO($dbConnection, $admin);

        $adminsDao->update(
            $_POST['name'], 
            new GenreEnum($_POST['genre']), 
            $_POST['birthdate']
        );
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
    public function updatePassword()
    {
        if ($this->getHttpRequestMethod() != 'POST') {
            $this->redirectToRoot();
        }
            
        $dbConnection = new MySqlPDODatabase();
        $adminsDao = new AdminsDAO(
            $dbConnection,
            Admin::getLoggedIn($dbConnection)
        );
        
        echo $adminsDao->changePassword($_POST['new_password']);
    }
}
