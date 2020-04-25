<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Admins;
use models\Courses;
use models\Questionnaires;
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

    
    public function quests()
    {
        if (empty($_POST['id_quest'])) { echo -1; }
        
        $quests = new Questionnaires();
        
        echo $quests->getAnswer($_POST['id_quest']);
    }
    
    public function mark_class_watched()
    {
        if (empty($_POST['id_class'])) { return; }
        
        $classes = new Classes();
        $classes->markAsWatched($_SESSION['s_login'], $_POST['id_class']);
    }
    
    public function remove_watched_class()
    {
        if (empty($_POST['id_class'])) { return; }
        
        $classes = new Classes();
        $classes->removeWatched($_SESSION['s_login'], $_POST['id_class']);
    }
}
