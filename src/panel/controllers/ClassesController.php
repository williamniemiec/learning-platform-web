<?php
namespace controllers;


use core\Controller;
use models\Admin;
use models\_Class;
use database\pdo\MySqlPDODatabase;
use models\dao\VideosDAO;
use models\dao\QuestionnairesDAO;
use models\Video;
use models\dao\ModulesDAO;
use models\Questionnaire;


/**
 * Responsible for the behavior of the view {@link classesManager/classes_manager.php}.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class ClassesController extends Controller
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
        $classes = array();
        $videosDAO = new VideosDAO($dbConnection);
        $questionnairesDAO = new QuestionnairesDAO($dbConnection);
        
        foreach ($videosDAO->getAll() as $class) {
            $classes[$class->getTitle()] = $class;
        }
        
        foreach ($questionnairesDAO->getAll() as $class) {
            $classes[$class->getQuestion()] = $class;
        }
        
        usort($classes, function($c1, $c2) {
            return $c1->getModule()->getName() > $c2->getModule()->getName();
        });
        
        $header = array(
            'title' => 'Classes manager - Learning platform',
            'styles' => array('manager'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'classes' => $classes,
            'header' => $header
        );
        
        $this->loadTemplate("classesManager/classes_manager", $viewArgs);
    }
    
    /**
     * Creates new class.
     */
    public function new()
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $modulesDAO = new ModulesDAO($dbConnection);
            
        $header = array(
            'title' => 'New class - Learning platform',
            'styles' => array('manager', 'classesManager'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'header' => $header,
            'modules' => $modulesDAO->getAll(),
            'error' => false,
            'msg' => '',
            'scripts' => array('classesManager')
        );
        
        // Checks if form has been sent
        if (!empty($_POST['type'])) {
            
            if ($_POST['type'] == 'v') {
                if (preg_match("/[0-9A-z]{11}/", $_POST['videoID'])) {
                    $videosDAO = new VideosDAO($dbConnection, $admin);
                    $description = empty($_POST['description']) ? null : $_POST['description'];
                    
                    try {
                        $videosDAO->add(new Video(
                            $modulesDAO->get((int)$_POST['id_module']),
                            $modulesDAO->getHighestOrderInModule((int)$_POST['id_module']) + 1,
                            $_POST['title'],
                            $_POST['videoID'],
                            $_POST['length'],
                            $description
                        ));
                        
                        header("Location: ".BASE_URL."classes");
                        exit;
                    }
                    catch (\Exception $e) {
                        $viewArgs['error'] = true;
                        $viewArgs['msg'] = $e->getMessage();
                    }
                }
                else {
                    $viewArgs['error'] = true;
                    $viewArgs['msg'] = "Invalid video id";
                }
            }
            else if ($_POST['type'] == 'q') {
                $questionnairesDAO = new QuestionnairesDAO($dbConnection, $admin);
                
                try {
                    $questionnairesDAO->add(new Questionnaire(
                        $modulesDAO->get((int)$_POST['id_module']),
                        $modulesDAO->getHighestOrderInModule((int)$_POST['id_module']) + 1,
                        $_POST['question'],
                        $_POST['q1'],
                        $_POST['q2'],
                        $_POST['q3'],
                        $_POST['q4'],
                        (int)$_POST['answer']
                    ));
                    
                    header("Location: ".BASE_URL."classes");
                    exit;
                }
                catch (\Exception $e) {
                    $viewArgs['error'] = true;
                    $viewArgs['msg'] = $e->getMessage();
                }
            }
            else {
                $viewArgs['error'] = true;
                $viewArgs['msg'] = "Invalid class type";
            }
        }
        
        $this->loadTemplate("classesManager/classes_new", $viewArgs);
    }
    
    /**
     * Edits a class.
     */
    public function edit($id_module, $class_order)
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $modulesDAO = new ModulesDAO($dbConnection);
        $videosDAO = new VideosDAO($dbConnection, $admin);
        $class = $videosDAO->get((int)$id_module, (int)$class_order);
        $type = 'v';
        
        if (empty($class)) {
            $questionnairesDAO = new QuestionnairesDAO($dbConnection, $admin);
            $class = $questionnairesDAO->get((int)$id_module, (int)$class_order);
            $type = 'q';
        }
        
        $header = array(
            'title' => 'Edit class - Learning platform',
            'styles' => array('manager', 'classesManager'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'header' => $header,
            'modules' => $modulesDAO->getAll(),
            'class' => $class,
            'error' => false,
            'msg' => '',
            'scripts' => array('classesManager')
        );
        
        // Checks if form has been sent
        if (!empty($_POST['type'])) {
            if ($_POST['type'] == 'v') {
                if (preg_match("/[0-9A-z]{11}/", $_POST['videoID'])) {
                    $videosDAO = new VideosDAO($dbConnection, $admin);
                    $description = empty($_POST['description']) ? null : $_POST['description'];
     
                    try {
                        $videosDAO->update(new Video(
                            $class->getModule(),
                            $class->getClassOrder(),
                            $_POST['title'],
                            $_POST['videoID'],
                            $_POST['length'],
                            $description
                        ));
                        
                        // If module to which the class belongs has been 
                        // changed, updated it
                        if ($class->getModule()->getId() != (int)$_POST['id_module']) {
                            $videosDAO->updateModule(
                                $class, 
                                (int)$_POST['id_module'],
                                (int)$modulesDAO->getHighestOrderInModule((int)$_POST['id_module']) + 1
                            );
                        }
                        
                        header("Location: ".BASE_URL."classes");
                        exit;
                    }
                    catch (\Exception $e) {
                        $viewArgs['error'] = true;
                        $viewArgs['msg'] = $e->getMessage();
                    }
                }
                else {
                    $viewArgs['error'] = true;
                    $viewArgs['msg'] = "Invalid video id";
                }
            }
            else if ($_POST['type'] == 'q') {
                $questionnairesDAO = new QuestionnairesDAO($dbConnection, $admin);

                try {
                    $questionnairesDAO->update(new Questionnaire(
                        $class->getModule(),
                        $class->getClassOrder(),
                        $_POST['question'],
                        $_POST['q1'],
                        $_POST['q2'],
                        $_POST['q3'],
                        $_POST['q4'],
                        (int)$_POST['answer']
                    ));
                    
                    // If module to which the class belongs has been
                    // changed, updated it
                    if ($class->getModule()->getId() != (int)$_POST['id_module']) {
                        $questionnairesDAO->updateModule(
                            $class,
                            (int)$_POST['id_module'],
                            (int)$modulesDAO->getHighestOrderInModule((int)$_POST['id_module']) + 1
                        );
                    }
                    
                    header("Location: ".BASE_URL."classes");
                    exit;
                }
                catch (\Exception $e) {
                    $viewArgs['error'] = true;
                    $viewArgs['msg'] = $e->getMessage();
                }
            }
            else {
                $viewArgs['error'] = true;
                $viewArgs['msg'] = "Invalid class type";
            }
        }
        
        if ($type == 'v')
            $this->loadTemplate("classesManager/classes_edit_video", $viewArgs);
        else
            $this->loadTemplate("classesManager/classes_edit_questionnaire", $viewArgs);
    }
    
    /**
     * Removes a class.
     */
    public function delete($id_module, $class_order)
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        
        $videosDAO = new VideosDAO($dbConnection, $admin);
        
        if (!$videosDAO->delete((int)$id_module, (int)$class_order)) {
            $questionnairesDAO = new QuestionnairesDAO($dbConnection, $admin);
            $questionnairesDAO->delete((int)$id_module, (int)$class_order);
        }
        
        header("Location: ".BASE_URL."classes");
        exit;
    }
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
    /**
     * Gets all registered classes.
     *
     * @return      string Modules
     *
     * @apiNote     Must be called using GET request method
     */
    public function getAll()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET')
            return;
            
        $dbConnection = new MySqlPDODatabase();
        
        $classes = array();
        $videosDAO = new VideosDAO($dbConnection);
        $questionnairesDAO = new QuestionnairesDAO($dbConnection);
        
        $classes['videos'] = $videosDAO->getAll();
        $classes['questionnaires'] = $questionnairesDAO->getAll();
        
        echo json_encode($classes);
    }
}
