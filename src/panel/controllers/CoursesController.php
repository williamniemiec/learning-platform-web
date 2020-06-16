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
        
        $header = array(
            'title' => 'Learning platform - home',
            'styles' => array('coursesEdition', 'style')
            //'description' => "A website made using MVC-in-PHP framework",
            //'keywords' => array('home', 'mvc-in-php'),
            //'robots' => 'index'
        );
        
        $params = array(
            'adminName' => $admins->getName(),
            'error' => false,
            'msg' => '',
            'header' => $header,
            'scripts' => array('script')
        );
        
        if (!empty($_POST['name'])) {
            if ($courses->add($_POST['name'], $_POST['description'], $_FILES['logo'])) {
                header("Location: ".BASE_URL);
                exit;
            }
            
            $params['error'] = true;
            $params['msg'] = "The course could not be added!";
        }
        
        $this->loadTemplate("coursesManager/course_add", $params);
    }
    
    public function edit($id_course)
    {
        if (empty($id_course) || $id_course <= 0) { header("Location: ".BASE_URL); }
        
        $courses = new Courses();
        $course = $courses->getCourse($id_course);
        $admins = new Admins($_SESSION['a_login']);

        $header = array(
            'title' => 'Learning platform - '.$course['name'],
            'styles' => array('coursesEdition', 'style')
            //'description' => "A website made using MVC-in-PHP framework",
            //'keywords' => array('home', 'mvc-in-php'),
            //'robots' => 'index'
        );
        
        $params = array(
            'adminName' => $admins->getName(),
            'course' => $course,
            'modules' => $course['modules'],
            'error' => false,
            'msg' => '',
            'header' => $header,
            'scripts' => array()
        );
        
        // Checks if the course was edited
        if (!empty($_POST['name'])) {
            if ($courses->edit($id_course, $_POST['name'], $_POST['description'], $_FILES['logo'])) {
                header("Location: ".BASE_URL);
                exit;
            }
            
            $params['error'] = true;
            $params['msg'] = "Error while saving editions";
        }
        
        $this->loadTemplate("coursesManager/course_edit", $params);
    }
}
