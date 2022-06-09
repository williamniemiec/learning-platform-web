<?php
namespace panel\controllers;


use panel\config\Controller;
use panel\repositories\pdo\MySqlPDODatabase;
use panel\domain\Admin;
use panel\domain\enum\GenreEnum;
use panel\dao\AdminsDAO;
use panel\dao\AuthorizationDAO;


/**
 * Responsible for the behavior of the AdminsManagerView.
 */
class AdminsController extends Controller
{
    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    /**
     * Checks whether admin is logged in and if he has authorization to access
     * the page. If he is not, redirects him to login page.
     */
    public function __construct()
    {
        if (!Admin::isLogged() || !$this->hasLoggedAdminAuthorization(0)) {
            $this->redirectTo("login");
        }
    }
    
    
    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
    /**
     * @Override
     */
    public function index ()
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $adminsDao = new AdminsDAO($dbConnection, $admin);
        $header = array(
            'title' => 'Admins manager - Learning platform',
            'robots' => 'index'
        );
        $viewArgs = array(
            'header' => $header,
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'admins' => $adminsDao->getAll()
        );
        
        $this->loadTemplate("adminsManager/AdminsManagerView", $viewArgs);
    }
    
    public function new()
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $authorizationsDao = new AuthorizationDAO($dbConnection);
        $header = array(
            'title' => 'New admin - Learning platform',
            'styles' => array(),
            'description' => "New admin",
            'robots' => 'noindex'
        );
        $viewArgs = array(
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'header' => $header,
            'scripts' => array(),
            'error' => false,
            'msg' => '',
            'authorizations' => $authorizationsDao->getAll()
        );
        
        if ($this->hasRegistrationFormBeenSent()) {
            if ($this->isAllFieldsFilled()) {
                if ($this->storeNewAdmin($admin, $authorizationsDao)) {
                    $this->redirectTo("admins");
                }
                
                $viewArgs['error'] = true;
                $viewArgs['msg'] = "Admin already registered!";
            }
            else {
                $viewArgs['error'] = true;
                $viewArgs['msg'] = "Fill in all fields!";
            }
            
        }
        
        $this->loadTemplate("adminsManager/AdminsManagerNewView", $viewArgs);
    }

    /**
     * Checks if registration form was sent.
     *
     * @return      boolean If registration form was sent
     */
    private function hasRegistrationFormBeenSent()
    {
        return (
            !empty($_POST['name']) ||
            !empty($_POST['genre']) ||
            !empty($_POST['birthdate']) ||
            !empty($_POST['email']) ||
            !empty($_POST['password'])
            );
    }

     /**
     * Checks if all required fields are filled. The required fields are:
     * <ul>
     *  <li>Name</li>
     *  <li>Genre</li>
     *  <li>Birthdate</li>
     *  <li>Email</li>
     *  <li>Password</li>
     * </ul>
     *
     * @return      boolean If all required fields are filled
     */
    private function isAllFieldsFilled()
    {
        return (
            isset($_POST['name']) &&
            isset($_POST['genre']) &&
            isset($_POST['birthdate']) &&
            isset($_POST['email']) &&
            isset($_POST['password'])
            );
    }

    private function storeNewAdmin($admin, $authorizationsDao)
    {
        $adminsDao = new AdminsDAO(
            new MySqlPDODatabase(), 
            $admin
        );
        $admin = new Admin(
            -1,
            $authorizationsDao->get((int) $_POST['authorization']),
            $_POST['name'],
            new GenreEnum($_POST['genre']),
            new \DateTime($_POST['birthdate']),
            $_POST['email']
        );
        
        return $adminsDao->new($admin, $_POST['password']);
    }
    
    public function edit($idAdmin)
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $authorizationsDao = new AuthorizationDAO($dbConnection);
        $adminsDao = new AdminsDAO($dbConnection, $admin);
        $header = array(
            'title' => 'New admin - Learning platform',
            'styles' => array(),
            'description' => "New admin",
            'robots' => 'noindex'
        );
        $viewArgs = array(
            'header' => $header,
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'scripts' => array(),
            'error' => false,
            'msg' => '',
            'admin' => $adminsDao->get((int) $idAdmin),
            'authorizations' => $authorizationsDao->getAll()
        );
        
        if ($this->hasFormBeenSent()) {
            if ($this->updateAdmin($idAdmin, $adminsDao)) {
                $this->redirectTo("admins");
            }
            
            $viewArgs['error'] = true;
            $viewArgs['msg'] = "Error while updating";
        }
        
        $this->loadTemplate("adminsManager/AdminsManagerEditView", $viewArgs);
    }

    private function hasFormBeenSent()
    {
        return !empty($_POST['email']) && !empty($_POST['authorization']);
    }

    private function updateAdmin($adminId, $adminsDao)
    {
        return $adminsDao->updateAdmin(
            (int) $adminId,
            (int) $_POST['authorization'],
            $_POST['email'], 
            empty($_POST['password']) ? "" : $_POST['password']
        );
    }
}
