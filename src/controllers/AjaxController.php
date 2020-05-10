<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Admins;
use models\Courses;
use models\Questionnaires;
use models\Classes;
use models\Doubts;


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
    
    public function remove_comment()
    {
        if (empty($_POST['id_comment'])) { return; }
        
        $doubts = new Doubts();
        $doubts->delete($_POST['id_comment']);
    }
    
    public function add_reply()
    {
        if (empty($_POST['id_doubt']) || $_POST['id_doubt'] <= 0) { return; }
        if (empty($_POST['id_user']) || $_POST['id_user'] <= 0) { return; }
        if (empty($_POST['text'])) { return; }
        
        $doubts = new Doubts();
        echo $doubts->addReply($_POST['id_doubt'], $_POST['id_user'], $_POST['text']);
    }
    
    public function get_student_name()
    {
        if (empty($_POST['id_student']) || $_POST['id_student'] <= 0) { echo ""; }
        
        $students = new Students();
        echo $students->get($_POST['id_student'])->getName();
    }
    
    public function remove_reply()
    {
        if (empty($_POST['id_reply']) || $_POST['id_reply'] <= 0) { return; }
        
        $doubts = new Doubts();
        $doubts->deleteReply($_POST['id_reply']);
    }
    
    public function update_profile_photo()
    {
        $students = new Students($_SESSION['s_login']);
        $students->updatePhoto($_FILES['photo']);
    }
    
    public function update_password()
    {
        if (empty($_POST['new_password']) || empty($_POST['current_password'])) { echo false; }
        
        $students = new Students($_SESSION['s_login']);
        echo $students->updatePassword($_POST['current_password'], $_POST['new_password']);
    }
}
