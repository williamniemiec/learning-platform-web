<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Questionnaires;
use models\Videos;
use models\Modules;
use models\Classes;


/**
 
 */
class AjaxController extends Controller
{
    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    public function __construct()
    {}
    
    
    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
    /**
     * @Override
     */
    public function index ()
    { header("Location: ".BASE_URL); }

    
    public function delete_module()
    {
        if (empty($_POST['id_module'])) { return; }
        
        $modules = new Modules();
        
        $modules->delete($_POST['id_module']);
    }
    
    public function delete_class()
    {
        if (empty($_POST['id_class'])) { return; }
        
        $classes = new Classes();
        
        $classes->delete($_POST['id_class']);
    }
    
    public function add_module()
    {
        if (empty($_POST['name'])) { echo -1; }
        
        $modules = new Modules();
        
        echo $modules->add($_POST['id_course'], $_POST['name']);
    }
    
    public function add_class_video()
    {
        if (empty($_POST['title'])) { echo -1; }
        
        $classes = new Classes();
        $videos = new Videos();
        
        if (empty($_POST['order']))
            $classId = $classes->add($_POST['id_module'], $_POST['id_course'], 'video');
        else
            $classId = $classes->add($_POST['id_module'], $_POST['id_course'], 'video', $_POST['order']);
        
        if ($classId != -1) {
            $response = $videos->add($classId, $_POST['title'], $_POST['description'], $_POST['url']);
            
            if (!$response) {
                $classes->delete($classId);
            }
        }
        
        echo $classId;
    }
    
    public function add_class_quest()
    {
        if (empty($_POST['title'])) { echo -1; }
        
        $classes = new Classes();
        $quests = new Questionnaires();
        
        if (empty($_POST['order']))
            $classId = $classes->add($_POST['id_module'], $_POST['id_course'], 'video');
        else
            $classId = $classes->add($_POST['id_module'], $_POST['id_course'], 'video', $_POST['order']);
           
        if ($classId != -1) {
            $response = $quests->add(
                $classId, $_POST['question'], 
                $_POST['op1'], 
                $_POST['op2'], 
                $_POST['op3'], 
                $_POST['op4'], 
                $_POST['answer']
            );
            
            if (!$response) {
                $classes->delete($classId);
            }
        }
        
        echo $classId;
    }
    
    public function edit_module()
    {
        if (empty($_POST['id_module']) || empty($_POST['name'])) { echo false; }
        
        $modules = new Modules();
        echo $modules->edit($_POST['id_module'], $_POST['name']);
    }
}
