<?php
namespace panel\controllers;


use panel\config\Controller;
use panel\repositories\pdo\MySqlPDODatabase;
use panel\domain\Questionnaire;
use panel\domain\Admin;
use panel\domain\Video;
use panel\dao\QuestionnairesDAO;
use panel\dao\VideosDAO;
use panel\dao\ModulesDAO;
use panel\util\IllegalAccessException;


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
        $index = $this->getIndex();
        $limit = 10;
        $header = array(
            'title' => 'Classes manager - Learning platform',
            'styles' => array('ManagerStyle'),
            'robots' => 'noindex'
        );
        $viewArgs = array(
            'header' => $header,
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'classes' => $this->fetchClasses($dbConnection, $limit, $index),
            'totalPages' => ceil($this->getTotalClasses($dbConnection) / $limit),
            'currentIndex' => $index
        );
        
        $this->loadTemplate("classesManager/ClassesManagerView", $viewArgs);
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

    private function fetchClasses($dbConnection, $limit, $index)
    {
        $classes = array();
        $videosDao = new VideosDAO($dbConnection);
        $questionnairesDao = new QuestionnairesDAO($dbConnection);
        $limitClasses = $limit/2;
        $classOffset = $limitClasses * ($index - 1);

        foreach ($videosDao->getAll($limitClasses, $classOffset) as $class) {
            $classes[] = $class;
        }
        
        foreach ($questionnairesDao->getAll($limitClasses, $classOffset) as $class) {
            $classes[] = $class;
        }

        $this->sortClassesByNameAscending($classes);

        return $classes;
    }

    private function sortClassesByNameAscending($classes)
    {
        usort($classes, function($c1, $c2) {
            return $c1->getModuleId()->getName() > $c2->getModuleId()->getName() 
                ? 1 : 
                -1;
        });
    }

    private function getTotalClasses($dbConnection)
    {
        $videosDao = new VideosDAO($dbConnection);

        return $videosDao->getTotal($dbConnection)['total_classes'];
    }
    
    /**
     * Creates new class.
     */
    public function new()
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $modulesDao = new ModulesDAO($dbConnection);
        $header = array(
            'title' => 'New class - Learning platform',
            'styles' => array('ManagerStyle', 'ClassesManagerStyle'),
            'robots' => 'noindex'
        );
        $viewArgs = array(
            'header' => $header,
            'username' => $admin->getName(),
            'authorization' => $admin->getAuthorization(),
            'modules' => $modulesDao->getAll(),
            'error' => false,
            'msg' => '',
            'scripts' => array('ClassesManagerScript')
        );
        
        if ($this->hasFormBeenSent()) {
            if ($this->hasVideoBeenSent()) {
                $message = $this->storeNewVideo($modulesDao, $dbConnection, $admin);

                if (empty($message)) {
                    $this->redirectTo("classes");
                }

                $viewArgs['error'] = true;
                $viewArgs['msg'] = $message;
            }
            else if ($this->hasQuestionnaireBeenSent()) {
                $message = $this->storeNewQuestionnaire($modulesDao, $dbConnection, $admin);

                if (empty($message)) {
                    $this->redirectTo("classes");
                }

                $viewArgs['error'] = true;
                $viewArgs['msg'] = $message;
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

    private function hasVideoBeenSent()
    {
        return ($_POST['type'] == 'v');
    }

    private function storeNewVideo($modulesDao, $dbConnection, $admin)
    {
        if (!preg_match("/[0-9A-z]{11}/", $_POST['videoID'])) {
            return "Invalid video id";
        }

        $message = null;
        $videosDao = new VideosDAO($dbConnection, $admin);

        try {
            $videosDao->add(new Video(
                $modulesDao->get((int) $_POST['id_module']),
                $modulesDao->getHighestOrderInModule((int) $_POST['id_module']) + 1,
                $_POST['title'],
                $_POST['videoID'],
                $_POST['length'],
                empty($_POST['description']) ? null : $_POST['description']
            ));
        }
        catch (\InvalidArgumentException | IllegalAccessException $e) {
            $message = $e->getMessage();
        }

        return $message;
    }

    private function hasQuestionnaireBeenSent()
    {
        return ($_POST['type'] == 'q');
    }

    private function storeNewQuestionnaire($modulesDao, $dbConnection, $admin)
    {
        $message = null;
        $questionnairesDao = new QuestionnairesDAO($dbConnection, $admin);

        try {
            $questionnairesDao->add(new Questionnaire(
                $modulesDao->get((int) $_POST['id_module']),
                $modulesDao->getHighestOrderInModule((int) $_POST['id_module']) + 1,
                $_POST['question'],
                $_POST['q1'],
                $_POST['q2'],
                $_POST['q3'],
                $_POST['q4'],
                (int) $_POST['answer']
            ));
        }
        catch (\InvalidArgumentException | IllegalAccessException $e) {
            $message = $e->getMessage();
        }

        return $message;
    }
    
    /**
     * Edits a class.
     */
    public function edit($idModule, $classOrder)
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $modulesDao = new ModulesDAO($dbConnection);
        $videosDao = new VideosDAO($dbConnection, $admin);
        $class = $videosDao->get((int) $idModule, (int) $classOrder);
        $type = 'v';
        
        if (empty($class)) {
            $questionnairesDao = new QuestionnairesDAO($dbConnection, $admin);
            $class = $questionnairesDao->get((int) $idModule, (int) $classOrder);
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
            'modules' => $modulesDao->getAll(),
            'class' => $class,
            'error' => false,
            'msg' => '',
            'scripts' => array('ClassesManagerScript')
        );
        
        if ($this->hasFormBeenSent()) {
            if ($this->hasVideoBeenSent()) {
                $message = $this->updateVideo(
                    $modulesDao, 
                    $dbConnection, 
                    $admin, 
                    $class
                );

                if (empty($message)) {
                    $this->redirectTo("classes");
                }

                $viewArgs['error'] = true;
                $viewArgs['msg'] = $message;
            }
            else if ($this->hasQuestionnaireBeenSent()) {
                $message = $this->updateQuestionnaire(
                    $modulesDao, 
                    $dbConnection, 
                    $admin, 
                    $class
                );

                if (empty($message)) {
                    $this->redirectTo("classes");
                }

                $viewArgs['error'] = true;
                $viewArgs['msg'] = $message;
            }
            else {
                $viewArgs['error'] = true;
                $viewArgs['msg'] = "Invalid class type";
            }
        }
        
        if ($type == 'v') {
            $this->loadTemplate("classesManager/ClassesManagerEditVideoView", $viewArgs);
        }
        else {
            $this->loadTemplate("classesManager/ClassesManagerEditQuestionnaireView", $viewArgs);
        }
    }

    private function updateVideo($modulesDao, $dbConnection, $admin, $class)
    {
        if (!preg_match("/[0-9A-z]{11}/", $_POST['videoID'])) {
            return "Invalid video id";
        }

        $message = null;
        $videosDao = new VideosDAO($dbConnection, $admin);

        try {
            $videosDao->update(new Video(
                $class->getModuleId(),
                $class->getClassOrder(),
                $_POST['title'],
                $_POST['videoID'],
                $_POST['length'],
                empty($_POST['description']) ? null : $_POST['description']
            ));
            
            if ($this->hasClassModuleChanged($class)) {
                $videosDao->updateModule(
                    $class, 
                    (int) $_POST['id_module'],
                    (int) $modulesDao->getHighestOrderInModule((int) $_POST['id_module']) + 1
                );
            }
        }
        catch (\InvalidArgumentException | IllegalAccessException $e) {
            $message = $e->getMessage();
        }

        return $message;
    }

    private function hasClassModuleChanged($class)
    {
        return ($class->getModuleId()->getId() != (int) $_POST['id_module']);
    }

    private function updateQuestionnaire($modulesDao, $dbConnection, $admin, $class)
    {
        $message = null;
        $questionnairesDao = new QuestionnairesDAO($dbConnection, $admin);

        try {
            $questionnairesDao->update(new Questionnaire(
                $class->getModuleId(),
                $class->getClassOrder(),
                $_POST['question'],
                $_POST['q1'],
                $_POST['q2'],
                $_POST['q3'],
                $_POST['q4'],
                (int) $_POST['answer']
            ));
            
            if ($this->hasClassModuleChanged($class)) {
                $questionnairesDao->updateModule(
                    $class,
                    (int)$_POST['id_module'],
                    (int) $modulesDao->getHighestOrderInModule((int) $_POST['id_module']) + 1
                );
            }
        }
        catch (\InvalidArgumentException | IllegalAccessException $e) {
            $message = $e->getMessage();
        }

        return $message;
    }

    /**
     * Removes a class.
     */
    public function delete($idModule, $classOrder)
    {
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        $videosDao = new VideosDAO($dbConnection, $admin);
        
        if (!$videosDao->delete((int) $idModule, (int) $classOrder)) {
            $questionnairesDao = new QuestionnairesDAO($dbConnection, $admin);
            $questionnairesDao->delete((int) $idModule, (int) $classOrder);
        }
        
        $this->redirectTo("classes");
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
        if ($this->getHttpRequestMethod() != 'GET') {
            return;
        }
            
        $classes = array();
        $dbConnection = new MySqlPDODatabase();
        $videosDao = new VideosDAO($dbConnection);
        $questionnairesDao = new QuestionnairesDAO($dbConnection);
        $classes['videos'] = $videosDao->getAll();
        $classes['questionnaires'] = $questionnairesDao->getAll();
        
        echo json_encode($classes);
    }
}
