<?php
/**
 * Copyright (c) William Niemiec.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

namespace panel\controllers;


use panel\config\Controller;
use panel\repositories\pdo\MySqlPDODatabase;
use panel\domain\Admin;
use panel\domain\Bundle;
use panel\domain\enum\BundleOrderTypeEnum;
use panel\domain\enum\OrderDirectionEnum;
use panel\dao\BundlesDAO;
use panel\dao\CoursesDAO;
use panel\util\FileUtil;
use panel\util\IllegalAccessException;


/**
 * Responsible for the behavior of the BundlesView.
 */
class BundlesController extends Controller
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private const IMAGES_LOCATION = "../../../../../src/main/webapp/images/logos/bundles/";


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
        $bundlesDao = new BundlesDAO($dbConnection);
        $selectedOrderBy = 'courses';
        $selectedOrderByDirection = 'asc';
        $index = $this->getIndex();
        $limit = 10;
        $offset = $limit * ($index - 1);
        
        if ($this->hasFilterBeenSent()) {
            $bundles = $bundlesDao->getAll(
                $limit, $offset, '', 
                new BundleOrderTypeEnum($_GET['order-by']), 
                new OrderDirectionEnum($_GET['order-by-direction'])
            );
            $selectedOrderBy = $_GET['order-by'];
            $selectedOrderByDirection = $_GET['order-by-direction'];
        }
        else {
            $bundles = $bundlesDao->getAll($limit, $offset);
        }
        
        $header = array(
            'title' => 'Bundles - Learning platform',
            'styles' => array('CoursesManagerStyle', 'ManagerStyle'),
            'robots' => 'noindex'
        );
        $viewArgs = array(
            'header' => $header,
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'bundles' => $bundles,
            'selectedOrderBy' => $selectedOrderBy,
            'selectedOrderByDirection' => $selectedOrderByDirection,
            'totalPages' => ceil($bundlesDao->count() / $limit),
            'currentIndex' => $index
        );
        
        $this->loadTemplate("bundlesManager/BundlesManagerView", $viewArgs);
    }

    private function hasFilterBeenSent()
    {
        return isset($_GET['order-by']) && isset($_GET['order-by-direction']);
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
        $bundlesDao = new BundlesDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        $header = array(
            'title' => 'New bundle - Learning platform',
            'styles' => array('CoursesManagerStyle'),
            'robots' => 'noindex'
        );
        $viewArgs = array(
            'header' => $header,
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'error' => false,
            'msg' => '',
            'scripts' => array('BundlesManagerScript')
        );
        
        if ($this->hasFormBeenSent()) {
            if ($this->hasPhotoBeenSent()) {
                $logo = $this->storePhoto();

                if ($logo == null) {
                    $viewArgs['error'] = true;
                    $viewArgs['msg'] = 'Invalid photo';
                }
            }
            
            if (!$viewArgs['error']) {
                if ($this->storeNewBundle($bundlesDao, $logo)) {
                    $this->redirectTo("bundles");
                }
                
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

    private function hasPhotoBeenSent()
    {
        return !empty($_FILES['logo']['tmp_name']);
    }

    private function storePhoto()
    {
        try {
            return FileUtil::storePhoto(
                $_FILES['logo'], 
                CoursesController::IMAGES_LOCATION
            );
        }
        catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    private function storeNewBundle($bundlesDao, $logo)
    {
        $success = false;

        try {
            $success = $bundlesDao->new(new Bundle(
                -1,
                $_POST['name'],
                $this->normalizePrice($_POST['price']),
                $logo,
                empty($_POST['description']) ? null : $_POST['description']
            ));
        }
        catch (\InvalidArgumentException | IllegalAccessException $e) {
            if (!empty($logo)) {
                unlink(BundlesController::IMAGES_LOCATION.$logo);
            }
        }

        return $success;
    }

    private function normalizePrice($value)
    {
        return (float) preg_replace("/,/", "", $value);
    }
    
    /**
     * Updates a bundle.
     * 
     * @param       int idBundle Bundle id to be updated
     */
    public function edit($idBundle)
    {
        $dbConnection = new MySqlPDODatabase();
        
        $admin = Admin::getLoggedIn($dbConnection);
        $bundlesDao = new BundlesDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        $bundle = $bundlesDao->get((int)$idBundle);
        $header = array(
            'title' => 'Edit bundle - Learning platform',
            'styles' => array('bundlesManager', 'ManagerStyle'),
            'robots' => 'noindex'
        );
        $viewArgs = array(
            'header' => $header,
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'bundle' => $bundle,
            'courses' => $bundle->getCourses($dbConnection),
            'scripts' => array('BundlesManagerScript'),
            'error' => false,
            'msg' => ''
        );
        
        if ($this->hasFormBeenSent()) {
            $logo = null;
            
            if ($this->hasPhotoBeenSent()) {
                $logo = $this->storePhoto();

                if ($logo == null) {
                    $viewArgs['error'] = true;
                    $viewArgs['msg'] = 'Invalid photo';
                }
                else {
                    $this->removeCurrentBundleLogo($bundle);
                }
            }
            
            if (!$viewArgs['error']) {
                if ($this->updateBundle($bundlesDao, $logo, $idBundle)) {
                    $this->redirectTo("bundles");
                }
                
                $viewArgs['error'] = true;
                $viewArgs['msg'] = "The bundle could not be updated!";
            }
        }
        
        $this->loadTemplate("bundlesManager/BundlesManagerEditView", $viewArgs);
    }

    private function removeCurrentBundleLogo($bundle)
    {
        if (empty($bundle->getLogo())) {
            return;
        }
        
        unlink(BundlesController::IMAGES_LOCATION.$bundle->getLogo());
    }

    private function updateBundle($bundlesDao, $logo, $bundleId)
    {
        $success = false;

        try {
            $success = $bundlesDao->update(new Bundle(
                (int) $bundleId,
                $_POST['name'],
                $this->normalizePrice($_POST['price']),
                $logo,
                empty($_POST['description']) ? null : $_POST['description']
            ));
        }
        catch (\InvalidArgumentException | IllegalAccessException $e) {
            if (!empty($logo)) {
                unlink(BundlesController::IMAGES_LOCATION.$logo);
            }
        }

        return $success;
    }
    
    /**
     * Removes a bundle.
     * 
     * @param       int $idBundle Bundle id to be removed
     */
    public function delete($idBundle)
    {
        $dbConnection = new MySqlPDODatabase();
        $bundlesDao = new BundlesDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        $bundle = $bundlesDao->get((int) $idBundle);
        
        $bundlesDao->remove((int) $idBundle);
        $this->removeCurrentBundleLogo($bundle);
        $this->redirectTo("bundles");
    }
    
    /**
     * Removes logo from a bundle.
     *
     * @param       int idBundle Bundle id
     */
    public function deleteLogo($idBundle)
    {
        $dbConnection = new MySqlPDODatabase();
        $bundlesDao = new BundlesDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        $bundle = $bundlesDao->get((int) $idBundle);
        
        if ($bundlesDao->removeLogo((int) $idBundle)) {  
            $this->removeCurrentBundleLogo($bundle);
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
        if ($this->getHttpRequestMethod() != 'GET') {
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
        if ($this->getHttpRequestMethod() != 'POST') {
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
        if ($this->getHttpRequestMethod() != 'GET') {
            return;
        }
            
        $dbConnection = new MySqlPDODatabase();
        $bundlesDao = new BundlesDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        
        echo json_encode($bundlesDao->getAll());
    }
}
