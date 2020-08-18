<?php
namespace controllers;


use core\Controller;
use models\Admin;
use database\pdo\MySqlPDODatabase;
use models\dao\AdminsDAO;
use models\Authorization;
use models\dao\AuthorizationDAO;
use models\enum\GenreEnum;


/**
 * Responsible for the behavior of the view {@link adminsManager/admins_manager.php}.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
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
        if (!Admin::isLogged() || 
            !(Admin::getLoggedIn(new MySqlPDODatabase())->getAuthorization()->getLevel() == 0)) {
            header("Location: ".BASE_URL."login");
            exit;
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
        $adminsDAO = new AdminsDAO($dbConnection, $admin);
        
        $header = array(
            'title' => 'Admins manager - Learning platform',
            'robots' => 'index'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'header' => $header,
            'admins' => $adminsDAO->getAll()
        );
        
        $this->loadTemplate("adminsManager/admins_manager", $viewArgs);
    }
    
    public function new()
    {
        $dbConnection = new MySqlPDODatabase();
        
        $admin = Admin::getLoggedIn($dbConnection);
        $authorizationsDAO = new AuthorizationDAO($dbConnection);
        
        $header = array(
            'title' => 'New admin - Learning platform',
            'styles' => array(),
            'description' => "New admin",
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'header' => $header,
            'scripts' => array(),
            'error' => false,
            'msg' => '',
            'authorizations' => $authorizationsDAO->getAll()
        );
        
        // Checks if registration form has been sent
        if ($this->wasRegistrationFormSent()) {
            // Checks if all fields are filled
            if ($this->isAllFieldsFilled()) {
                $adminsDAO = new AdminsDAO(
                    new MySqlPDODatabase(), 
                    $admin
                );
                
                $admin = new Admin(
                    -1,
                    $authorizationsDAO->get((int)$_POST['authorization']),
                    $_POST['name'],
                    new GenreEnum($_POST['genre']),
                    new \DateTime($_POST['birthdate']),
                    $_POST['email']
                );
                
                if ($adminsDAO->new($admin, $_POST['password'])) {
                    header("Location: ".BASE_URL."admins");
                    exit;
                }
                
                $viewArgs['error'] = true;
                $viewArgs['msg'] = "Admin already registered!";
            }
            else {
                $viewArgs['error'] = true;
                $viewArgs['msg'] = "Fill in all fields!";
            }
            
        }
        
        $this->loadTemplate("adminsManager/admins_new", $viewArgs);
    }
    
    public function edit($id_admin)
    {
        $dbConnection = new MySqlPDODatabase();
        
        $admin = Admin::getLoggedIn($dbConnection);
        $authorizationsDAO = new AuthorizationDAO($dbConnection);
        $adminsDAO = new AdminsDAO($dbConnection, $admin);
        
        $header = array(
            'title' => 'New admin - Learning platform',
            'styles' => array(),
            'description' => "New admin",
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'header' => $header,
            'scripts' => array(),
            'error' => false,
            'msg' => '',
            'admin' => $adminsDAO->get((int)$id_admin),
            'authorizations' => $authorizationsDAO->getAll()
        );
        
        if (!empty($_POST['email']) && !empty($_POST['authorization'])) {            
            $password = empty($_POST['password']) ? "" : $_POST['password'];
            
            $response = $adminsDAO->updateAdmin(
                (int)$id_admin,
                (int)$_POST['authorization'],
                $_POST['email'], 
                $password
            );
            
            if ($response) {
                header("Location: ".BASE_URL."admins");
                exit;
            }
            
            $viewArgs['error'] = true;
            $viewArgs['msg'] = "Error while updating";
        }
        
        $this->loadTemplate("adminsManager/admins_edit", $viewArgs);
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
    
    /**
     * Checks if registration form was sent.
     *
     * @return      boolean If registration form was sent
     */
    private function wasRegistrationFormSent()
    {
        return (
            !empty($_POST['name']) ||
            !empty($_POST['genre']) ||
            !empty($_POST['birthdate']) ||
            !empty($_POST['email']) ||
            !empty($_POST['password'])
            );
    }
}
