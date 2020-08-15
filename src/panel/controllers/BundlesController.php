<?php
namespace controllers;


use core\Controller;
use models\Admin;
use database\pdo\MySqlPDODatabase;
use models\dao\BundlesDAO;
use models\Bundle;
use models\util\FileUtil;
use models\util\IllegalAccessException;
use models\dao\CoursesDAO;


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
            'styles' => array('coursesManager', 'manager'),
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
    public function new()
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $bundlesDAO = new BundlesDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        
        $header = array(
            'title' => 'Admin area - Learning platform',
            'styles' => array('coursesManager'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'header' => $header,
            'error' => false,
            'msg' => ''
        );
        
        // Checks if the new bundle has been successfully added
        if (!empty($_POST['name'])) {
            $description = empty($_POST['description']) ? null : $_POST['description'];
            $logo = null;
            
            // Parses logo
            if (!empty($_FILES['logo'])) {
                try {
                    $logo = FileUtil::storePhoto($_FILES['logo'], "../assets/img/logos/bundles/");
                }
                catch (\InvalidArgumentException $e) {
                    $viewArgs['error'] = true;
                    $viewArgs['msg'] = 'Invalid photo';
                }
            }
            
            if (!$viewArgs['error']) {
                $response = false;
                
                // Tries create new bundle. If an error occurs, removes stored
                // logo
                try {
                    $response = $bundlesDAO->new(new Bundle(
                        -1,
                        $_POST['name'],
                        (float)$_POST['price'],
                        $logo,
                        $description
                    ));
                }
                catch (\InvalidArgumentException | IllegalAccessException $e) {
                    if (!empty($logo))
                        unlink("../assets/img/logos/bundles/".$logo);
                }
                
                
                if ($response) {
                    header("Location: ".BASE_URL."bundles");
                    exit;
                }
                
                // If an error occurred, display it
                $viewArgs['error'] = true;
                $viewArgs['msg'] = "The bundle could not be added!";
            }
        }
        
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
        $bundlesDAO = new BundlesDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        $bundle = $bundlesDAO->get((int)$id_bundle);
        
        $header = array(
            'title' => 'Admin area - Learning platform',
            'styles' => array('bundlesManager', 'manager'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'bundle' => $bundle,
            'courses' => $bundle->getCourses($dbConnection),
            'header' => $header,
            'scripts' => array('bundlesManager'),
            'error' => false,
            'msg' => ''
        );
        
        // Checks if the new bundle has been successfully updated
        if (!empty($_POST['name'])) {
            $description = empty($_POST['description']) ? null : $_POST['description'];
            $logo = null;
            
            // Parses logo
            if (!empty($_FILES['logo'])) {
                try {
                    $logo = FileUtil::storePhoto($_FILES['logo'], "../assets/img/logos/bundles/");
                    
                    // Removes old logo
                    if (!empty($bundle->getLogo()))
                        unlink("../assets/img/logos/bundles/".$bundle->getLogo());
                }
                catch (\InvalidArgumentException $e) {
                    $viewArgs['error'] = true;
                    $viewArgs['msg'] = 'Invalid photo';
                }
            }
            
            if (!$viewArgs['error']) {
                $response = false;
                
                // Tries update the bundle. If an error occurs, removes stored
                // logo
                try {
                    $response = $bundlesDAO->update(new Bundle(
                        (int)$id_bundle,
                        $_POST['name'],
                        (float)$_POST['price'],
                        $logo,
                        $description
                    ));
                }
                catch (\InvalidArgumentException | IllegalAccessException $e) {
                    if (!empty($logo))
                        unlink("../assets/img/logos/bundles/".$logo);
                }
                
                if ($response) {
                    header("Location: ".BASE_URL."bundles");
                    exit;
                }
                
                // If an error occurred, display it
                $viewArgs['error'] = true;
                $viewArgs['msg'] = "The bundle could not be updated!";
            }
        }
        
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
        
        $bundlesDAO = new BundlesDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        $bundle = $bundlesDAO->get((int)$id_bundle);
        $bundlesDAO->remove((int)$id_bundle);
        
        if (!empty($bundle->getLogo()))
            unlink("../assets/img/logos/bundles/".$bundle->getLogo());
        
        header("Location: ".BASE_URL."bundles");
        exit;
    }
    
    /**
     * Removes logo from a bundle.
     *
     * @param       int $id_bundle Bundle id
     */
    public function deleteLogo($id_bundle)
    {
        $dbConnection = new MySqlPDODatabase();
        
        $bundlesDAO = new BundlesDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        $bundle = $bundlesDAO->get((int)$id_bundle);
        
        if ($bundlesDAO->removeLogo((int)$id_bundle)) {    
            if (!empty($bundle->getLogo()))
                unlink("../assets/img/logos/bundles/".$bundle->getLogo());
        }
        
        header("Location: ".BASE_URL."bundles/edit/".$bundle->getId());
        exit;
    }
    
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
    /**
     * Gets all courses that a bundle has.
     * 
     * @param       int $_GET['id_bundle'] Bundle id
     * 
     * @return      string Courses
     * 
     * @apiNote     Must be called using GET request method
     */
    public function getCourses()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET')
            return;
        
        $dbConnection = new MySqlPDODatabase();
        
        $coursesDAO = new CoursesDAO($dbConnection);
        
        echo json_encode($coursesDAO->getFromBundle((int)$_GET['id_bundle']));
    }
    
    /**
     * Sets courses that a bundle has.
     *
     * @param       int $_POST['id_bundle'] Bundle id
     * @param       array $_POST['courseIds'] Course ids
     *
     * @return      string Courses
     *
     * @apiNote     Must be called using POST request method
     */
    public function setCourses()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
            return;
            
        $dbConnection = new MySqlPDODatabase();
        
        $bundlesDAO = new BundlesDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        
        $bundlesDAO->deleteAllCourses((int)$_POST['id_bundle']);
        
        foreach ($_POST['courseIds'] as $id_course) {
            $bundlesDAO->addCourse((int)$_POST['id_bundle'], (int)$id_course);
        }
    }
}
