<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Questionnaires;
use models\Videos;
use models\Modules;
use models\Classes;
use models\Student;
use models\Courses;

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
    
    public function get_video()
    {
        if (empty($_POST['id_video'])) { echo json_encode(array()); }
        
        $videos = new Videos();
        echo json_encode($videos->get($_POST['id_video']));
    }
    
    public function get_quest()
    {
        if (empty($_POST['id_quest'])) { echo json_encode(array()); }
        
        $quests = new Questionnaires();
        echo json_encode($quests->get($_POST['id_quest']));
    }
    
    public function edit_video()
    {
        if (empty($_POST['id_video']) || empty($_POST['title']) ||
            empty($_POST['description']) || empty($_POST['url'])) {
            echo false;
        }
        
        $videos = new Videos();
        echo $videos->edit($_POST['id_video'], $_POST['title'], $_POST['description'], $_POST['url']);
    }
    
    public function edit_quest()
    {
        if (empty($_POST['id_quest']) || empty($_POST['question']) ||
            empty($_POST['op1']) || empty($_POST['op2']) || 
            empty($_POST['op3']) || empty($_POST['op4']) || 
            empty($_POST['answer'])) {
            echo false; 
        }
        
        $quests = new Questionnaires();
        echo $quests->edit(
            $_POST['id_quest'], 
            $_POST['question'], 
            $_POST['op1'],
            $_POST['op2'], 
            $_POST['op3'], 
            $_POST['op4'], 
            $_POST['answer']
        );
    }
    
    public function add_student()
    {
        if (empty($_POST['email'])) { echo -1; }
        
        $students = new Students();
        $student = new Student($_POST['name'], $_POST['genre'], $_POST['birthdate'], $_POST['email'], $_POST['password']);
        echo $students->register($student, false);
    }
    
    public function edit_student()
    {
        if (empty($_POST['email'])) { echo -1; }
        
        $students = new Students();
        
        if (empty($_POST['passowrd']))
            $student = new Student($_POST['name'], $_POST['genre'], $_POST['birthdate'], $_POST['email'], null);
        else
            $student = new Student($_POST['name'], $_POST['genre'], $_POST['birthdate'], $_POST['email'], $_POST['password']);
        echo $students->edit($student);
    }
    
    public function get_student()
    {
        if (empty($_POST['id_student'])) { echo json_encode(array()); }
        
        $students = new Students();
        $student = $students->get($_POST['id_student']);
        $response = array(
            'name' => $student->getName(),
            'genre' => $student->getGenre(),
            'birthdate' => $student->getBirthdate(),
            'email' => $student->getEmail()
        );
        echo json_encode($response);
    }
    
    public function delete_student()
    {
        if (empty($_POST['id_student'])) { return false; }
        
        $students = new Students();
        echo $students->delete($_POST['id_student']);
    }
    
    public function get_courses()
    {
        if (empty($_POST['id_student'])) { echo false; }
        
        $courses = new Courses();
        echo json_encode($courses->getAll($_POST['id_student']));
    }
    
    public function add_student_course()
    {
        if (empty($_POST['id_student']) || empty($_POST['id_course'])) { echo false; }
        
        $students = new Students();
        echo $students->addCourse($_POST['id_student'], $_POST['id_course']);
    }
    
    public function clear_student_course()
    {
        if (empty($_POST['id_student']) || empty($_POST['id_course'])) { echo false; }
        
        $students = new Students();
        echo $students->deleteAllCourses($_POST['id_student']);
    }
}
