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
use panel\domain\Course;
use panel\util\FileUtil;
use panel\util\IllegalAccessException;
use panel\domain\enum\CourseOrderByEnum;
use panel\domain\enum\OrderDirectionEnum;
use panel\dao\CoursesDAO;
use panel\dao\ModulesDAO;


/**
 * Responsible for the behavior of the CoursesManagerView.
 */
class CoursesController extends Controller
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private const IMAGES_LOCATION = "../../../../../src/main/webapp/images/logos/courses/";


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
        $limit = 10;
        $index = $this->getIndex();
        $offset = $limit * ($index - 1);
        $coursesDAO = new CoursesDAO($dbConnection);
        $courses = $coursesDAO->getAll('', $limit, $offset);
        $header = array(
            'title' => 'Courses - Learning platform',
            'styles' => array('CoursesManagerStyle', 'ManagerStyle', 'searchBar'),
            'robots' => 'noindex'
        );
        $viewArgs = array(
            'header' => $header,
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'courses' => $courses,
            'scripts' => array('CoursesHomeScript'),
            'totalPages' => ceil($coursesDAO->count() / $limit),
            'currentIndex' => $index
        );
        
        $this->loadTemplate("coursesManager/CoursesManagerView", $viewArgs);
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
    
    public function new()
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $coursesDao = new CoursesDAO($dbConnection, $admin);
        $header = array(
            'title' => 'New course - Learning platform',
            'styles' => array('CoursesManagerStyle', 'ManagerStyle'),
            'robots' => 'noindex'
        );
        $viewArgs = array(
            'header' => $header,
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'courses' => $coursesDao->getAll('', 100),
            'error' => false,
            'msg' => '',
            'scripts' => array('CoursesManagerScript')
        );
        
        if ($this->hasFormBeenSent()) {
            $logo = null;
            
            if ($this->hasPhotoBeenSent()) {
                $logo = $this->storePhoto();

                if ($logo == null) {
                    $viewArgs['error'] = true;
                    $viewArgs['msg'] = 'Invalid photo';
                }
            }
            
            if (!$viewArgs['error']) {
                if ($this->storeNewCourse($coursesDao, $logo)) {
                    $this->redirectTo("courses");
                }
                
                $viewArgs['error'] = true;
                $viewArgs['msg'] = "The course could not be added!";
            }
        }
        
        $this->loadTemplate("coursesManager/CoursesManagerNewView", $viewArgs);
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

    private function storeNewCourse($coursesDao, $logo)
    {
        $success = false;

        try {
            $success = $coursesDao->new(new Course(
                -1,
                $_POST['name'],
                $logo,
                empty($_POST['description']) ? null : $_POST['description']
            ));
        }
        catch (\InvalidArgumentException | IllegalAccessException $e) {
            if (!empty($logo)) {
                unlink(CoursesController::IMAGES_LOCATION.$logo);
            }
        }

        return $success;
    }
    
    public function edit($idCourse)
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $coursesDao = new CoursesDAO($dbConnection, $admin);
        $course = $coursesDao->get($idCourse);
        $header = array(
            'title' => 'Edit course - Learning platform',
            'styles' => array('CoursesManagerStyle', 'ManagerStyle'),
            'robots' => 'noindex'
        );
        $viewArgs = array(
            'header' => $header,
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'course' => $course,
            'modules' => $course->getModules($dbConnection),
            'error' => false,
            'msg' => '',
            'scripts' => array('CoursesManagerScript')
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
                    $this->removeCurrentCourseLogo($course);
                }
            }
            
            if (!$viewArgs['error']) {
                if ($this->updateCourse($coursesDao, $logo, $course)) {
                    $this->redirectTo("courses");
                }

                $viewArgs['error'] = true;
                $viewArgs['msg'] = "The course could not be added!";
            }
        }
        
        $this->loadTemplate("coursesManager/CoursesManagerEditView", $viewArgs);
    }

    private function removeCurrentCourseLogo($course)
    {
        if (empty($course->getLogo())) {
            return;
        }
        
        unlink(CoursesController::IMAGES_LOCATION.$course->getLogo());
    }

    private function updateCourse($coursesDao, $logo, $course)
    {
        $success = false;

        try {
            $success = $coursesDao->update(new Course(
                $course->getId(),
                $_POST['name'],
                $logo,
                empty($_POST['description']) ? null : $_POST['description']
            ));
        }
        catch (\InvalidArgumentException | IllegalAccessException $e) {
            if (!empty($logo)) {
                unlink(CoursesController::IMAGES_LOCATION.$logo);
            }
        }

        return $success;
    }
    
    /**
     * Deletes a course and redirects admin to home page.
     *
     * @param       int $idCourse Course id to be deleted
     */
    public function delete($idCourse)
    {
        $dbConnection = new MySqlPDODatabase();
        $coursesDao = new CoursesDAO(
            $dbConnection,
            Admin::getLoggedIn($dbConnection)
        );
        
        $coursesDao->delete($idCourse);
        $this->redirectTo("courses");
    }
    
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
    /**
     * Searches courses.
     *
     * @param       string $_POST['name'] Name to be searched
     * @param       string $_POST['filter']['type'] Ranking of results, which
     * can be:
     * <ul>
     *     <li>price</li>
     *     <li>sales</li>
     * </ul>
     * @param       string $_POST['filter']['order'] Sort type, which can be:
     * <ul>
     *     <li>asc (Ascending)</li>
     *     <li>desc (Descending)</li>
     * </ul>
     *
     * @return      string Json containing courses
     *
     * @apiNote     Must be called using POST request method
     */
    public function search()
    {
        if ($this->getHttpRequestMethod() != 'POST') {
            return;
        }
            
        $dbConnection = new MySqlPDODatabase();
        $coursesDao = new CoursesDAO($dbConnection);
        
        echo json_encode($coursesDao->getAll(
            $_POST['name'],
            100,
            new CourseOrderByEnum($_POST['filter']['type']),
            new OrderDirectionEnum($_POST['filter']['order'])
        ));
    }
    
    /**
     * Gets all registered courses.
     * 
     * @return      string Courses
     * 
     * @apiNote     Must be called using GET request method
     */
    public function getAll()
    {
        if ($this->getHttpRequestMethod() != 'GET') {
            return;
        }
        
        $dbConnection = new MySqlPDODatabase();
        $coursesDao = new CoursesDAO($dbConnection);
        
        echo json_encode($coursesDao->getAll());
    }
    
    /**
     * Gets all modules from a course.
     *
     * @param       int $_GET['id_course'] Course id
     *
     * @return      string Modules
     *
     * @apiNote     Must be called using GET request method
     */
    public function getModules()
    {
        if ($this->getHttpRequestMethod() != 'GET') {
            return;
        }
            
        $dbConnection = new MySqlPDODatabase();
        $modulesDao = new ModulesDAO($dbConnection);
        
        echo json_encode($modulesDao->getFromCourse((int) $_GET['id_course']));
    }
    
    /**
     * Sets modules that a course has.
     * 
     * @param       int $_POST['id_course'] Course id
     * @param       array $_POST['modules'] Array of modules. Each position has
     * the following keys:
     * <ul>
     *  <li><b>id:</b> Module id</li>
     *  <li><b>order:</b> Module order in course</li>
     * </ul>

     * @apiNote     Must be called using POST request method
     */
    public function setModules()
    {
        if ($this->getHttpRequestMethod() != 'POST') {
            return;
        }
            
        $dbConnection = new MySqlPDODatabase();
        $coursesDao = new CoursesDAO($dbConnection, Admin::getLoggedIn($dbConnection));
        $modulesDao = new ModulesDAO($dbConnection);
        $modulesBackup = $modulesDao->getFromCourse((int) $_POST['id_course']);

        try {
            $coursesDao->deleteAllModules((int) $_POST['id_course']);
            $this->insertModules($coursesDao, $_POST['modules']);
        }
        catch(\Exception $e) {
            $this->restoreModules($coursesDao, $modulesBackup);
            header("HTTP/1.0 500 Module order is conflicting");
            echo "Module order is conflicting";
        }   
    }

    private function insertModules($coursesDao, $modules)
    {
        foreach ($modules as $module) {
            $coursesDao->addModule(
                (int) $_POST['id_course'], 
                (int) $module['id'], 
                (int) $module['order']
            );
        }
    }

    private function restoreModules($coursesDao, $modulesBackup)
    {
        foreach ($modulesBackup as $module) {
            try {
                $coursesDao->addModule(
                    (int) $_POST['id_course'], 
                    $module->getId(), 
                    $module->getOrder()
                );
            }
            catch(\Exception $e) {
                // If a backup module fails, there is nothing to do.
            }
        }
    }
}