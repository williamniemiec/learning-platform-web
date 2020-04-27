<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Admins;
use models\Courses;


/**
 */
class CoursesController extends Controller
{
    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    public function __construct()
    {
        if (!Admins::isLogged()) {
            header("Location: ".BASE_URL."login");
            exit;
        }
    }
    
    
    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
    /**
     * @Override
     */
    public function index ()
    { header("Location: ".BASE_URL); }
    
    public function delete($id_course)
    {
        $courses = new Courses();
        $courses->delete($id_course);
        header("Location: ".BASE_URL);
    }
    
    public function add()
    {
        $admins = new Admins($_SESSION['a_login']);
        $courses = new Courses();
        
        $params = array(
            'title' => 'Learning platform - home',
            'adminName' => $admins->getName(),
            'error' => false,
            'msg' => ''
        );
        
        if (!empty($_POST['name'])) {
            if ($courses->add($_POST['name'], $_POST['description'], $_FILES['logo'])) {
                header("Location: ".BASE_URL);
                exit;
            }
            
            $params['error'] = true;
            $params['msg'] = "The course could not be added!";
        }
        
        
        
        $this->loadTemplate("course_add", $params);
    }
    
    public function edit($id_course)
    {
        if (empty($id_course) || $id_course <= 0) { header("Location: ".BASE_URL); }
        
        $courses = new Courses();
        $course = $courses->getCourse($id_course);
        $admins = new Admins($_SESSION['a_login']);
        
        $params = array(
            'title' => 'Learning platform - '.$course['name'],
            'adminName' => $admins->getName(),
            'course' => $course,
            'modules' => $course['modules']
        );
        
        $this->loadTemplate("course_edit", $params);
    }
}
