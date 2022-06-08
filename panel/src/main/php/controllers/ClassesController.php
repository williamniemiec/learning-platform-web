<?php
namespace panel\controllers;


use panel\config\Controller;
use panel\repositories\pdo\MySqlPDODatabase;
use panel\domain\Admin;
use panel\domain\ClassType;
use panel\domain\Video;
use panel\dao\QuestionnairesDAO;
use panel\dao\VideosDAO;
use panel\dao\ModulesDAO;
use panel\domain\Questionnaire;


/**
 * Responsible for the behavior of the ClassesManagerView.
 */
class ClassesController extends Controller
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
        $classes = array();
        $videosDAO = new VideosDAO($dbConnection);
        $questionnairesDAO = new QuestionnairesDAO($dbConnection);
        $limit = 10;
        $limitVideos = $limit/2;
        $limitQuestionnaires = $limit/2;
        $index = 1;
        $totalClasses = $videosDAO->getTotal($dbConnection)['total_classes'];
        
        // Checks whether an index has been sent
        if (!empty($_GET['index'])) {
            $index = (int)$_GET['index'];
        }
        
        foreach ($videosDAO->getAll($limitVideos, $limitVideos * ($index - 1)) as $class) {
            $classes[$class->getTitle()] = $class;
        }
        
        foreach ($questionnairesDAO->getAll($limitQuestionnaires, $limitQuestionnaires * ($index - 1)) as $class) {
            $classes[$class->getQuestion()] = $class;
        }
        
        usort($classes, function($c1, $c2) {
            return $c1->getModule()->getName() > $c2->getModule()->getName();
        });
        
        $header = array(
            'title' => 'Classes manager - Learning platform',
            'styles' => array('ManagerStyle'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'classes' => $classes,
            'header' => $header,
            'totalPages' => ceil($totalClasses / $limit),
            'currentIndex' => $index
        );
        
        $this->loadTemplate("classesManager/ClassesManagerView", $viewArgs);
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
            'styles' => array('ManagerStyle', 'ClassesManagerStyle'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'header' => $header,
            'modules' => $modulesDAO->getAll(),
            'error' => false,
            'msg' => '',
            'scripts' => array('ClassesManagerScript')
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
        
        $this->loadTemplate("classesManager/ClassesManagerNewView", $viewArgs);
    }

    private function hasFormBeenSent()
    {
        return !empty($_POST['type']);
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
            'styles' => array('ManagerStyle', 'ClassesManagerStyle'),
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'header' => $header,
            'modules' => $modulesDAO->getAll(),
            'class' => $class,
            'error' => false,
            'msg' => '',
            'scripts' => array('ClassesManagerScript')
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
            $this->loadTemplate("classesManager/ClassesManagerEditVideoView", $viewArgs);
        else
            $this->loadTemplate("classesManager/ClassesManagerEditQuestionnaireView", $viewArgs);
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
