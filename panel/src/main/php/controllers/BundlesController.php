<?php
namespace panel\controllers;


use panel\config\Controller;
use panel\repositories\pdo\MySqlPDODatabase;
use panel\domain\Admin;
use panel\domain\Bundle;
use panel\domain\util\FileUtil;
use panel\domain\util\IllegalAccessException;
use panel\domain\enum\BundleOrderTypeEnum;
use panel\domain\enum\OrderDirectionEnum;
use panel\dao\BundlesDAO;
use panel\dao\CoursesDAO;


/**
 * Responsible for the behavior of the BundlesView.
 */
class BundlesController extends Controller
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
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $bundlesDAO = new BundlesDAO($dbConnection);
        $selectedOrderBy = 'courses';
        $selectedOrderByDirection = 'asc';
        $limit = 10;
        $index = 1;
        
        // Checks whether an index has been sent
        if (!empty($_GET['index'])) {
            $index = (int)$_GET['index'];
        }
        
        $offset = $limit * ($index - 1);
        
        if (isset($_GET['order-by']) && isset($_GET['order-by-direction'])) {
            $bundles = $bundlesDAO->getAll(
                $limit, $offset, '', 
                new BundleOrderTypeEnum($_GET['order-by']), 
                new OrderDirectionEnum($_GET['order-by-direction'])
            );
            $selectedOrderBy = $_GET['order-by'];
            $selectedOrderByDirection = $_GET['order-by-direction'];
        }
        else {
            $bundles = $bundlesDAO->getAll($limit, $offset);
        }
        
        $header = array(
            'title' => 'Bundles - Learning platform',
            'styles' => array('CoursesManagerStyle', 'ManagerStyle'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'bundles' => $bundles,
            'header' => $header,
            'selectedOrderBy' => $selectedOrderBy,
            'selectedOrderByDirection' => $selectedOrderByDirection,
            'totalPages' => ceil($bundlesDAO->count() / $limit),
            'currentIndex' => $index
        );
        
        $this->loadTemplate("bundlesManager/BundlesManagerView", $viewArgs);
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
     * Creates a new bundle.
     */
    public function new()
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $bundlesDAO = new BundlesDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        
        $header = array(
            'title' => 'New bundle - Learning platform',
            'styles' => array('CoursesManagerStyle'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'header' => $header,
            'error' => false,
            'msg' => '',
            'scripts' => array('BundlesManagerScript')
        );
        
        // Checks if the new bundle has been successfully added
        if (!empty($_POST['name'])) {
            $description = empty($_POST['description']) ? null : $_POST['description'];
            $logo = null;

            // Parses logo
            if (!empty($_FILES['logo']['tmp_name'])) {
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
                        (float)preg_replace("/,/", "", $_POST['price']),
                        $logo,
                        $description
                    ));
                }
                catch (\InvalidArgumentException | IllegalAccessException $e) {
                    if (!empty($logo))
                        unlink("../assets/img/logos/bundles/".$logo);
                }
                
                
                if ($response) {
                    $this->redirectTo("bundles");
                }
                
                // If an error occurred, display it
                $viewArgs['error'] = true;
                $viewArgs['msg'] = "The bundle could not be added!";
            }
        }
        
        $this->loadTemplate("bundlesManager/BundlesManagerNewView", $viewArgs);
    }

    private function hasFormBeenSent()
    {
        return !empty($_POST['name']);
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
            'title' => 'Edit bundle - Learning platform',
            'styles' => array('bundlesManager', 'ManagerStyle'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'bundle' => $bundle,
            'courses' => $bundle->getCourses($dbConnection),
            'header' => $header,
            'scripts' => array('BundlesManagerScript'),
            'error' => false,
            'msg' => ''
        );
        
        // Checks if the new bundle has been successfully updated
        if (!empty($_POST['name'])) {
            $description = empty($_POST['description']) ? null : $_POST['description'];
            $logo = null;
            
            // Parses logo
            if (!empty($_FILES['logo']['tmp_name'])) {
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
                        (float)preg_replace("/,/", "", $_POST['price']),
                        $logo,
                        $description
                    ));
                }
                catch (\InvalidArgumentException | IllegalAccessException $e) {
                    if (!empty($logo))
                        unlink("../assets/img/logos/bundles/".$logo);
                }
                
                if ($response) {
                    $this->redirectTo("bundles");
                }
                
                // If an error occurred, display it
                $viewArgs['error'] = true;
                $viewArgs['msg'] = "The bundle could not be updated!";
            }
        }
        
        $this->loadTemplate("bundlesManager/BundlesManagerEditView", $viewArgs);
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
        
        $this->redirectTo("bundles");
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

        $this->redirectTo("bundles/edit/".$bundle->getId());
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
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            return;
        }
        
        $dbConnection = new MySqlPDODatabase();
        $coursesDao = new CoursesDAO($dbConnection);
        
        echo json_encode($coursesDao->getFromBundle((int) $_GET['id_bundle']));
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
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return;
        }
            
        $dbConnection = new MySqlPDODatabase();
        $bundlesDao = new BundlesDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        $bundlesDao->deleteAllCourses((int) $_POST['id_bundle']);
        
        foreach ($_POST['courseIds'] as $id_course) {
            $bundlesDao->addCourse(
                (int) $_POST['id_bundle'], 
                (int) $id_course
            );
        }
    }
    
    /**
     * Gets all registered bundles.
     * 
     * @return      string Json containing all registered bundles
     * 
     * @apiNote     Must be called using GET request method
     */
    public function getAll()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            return;
        }
            
        $dbConnection = new MySqlPDODatabase();
        $bundlesDao = new BundlesDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        
        echo json_encode($bundlesDao->getAll());
    }
}
