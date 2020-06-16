<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Student;
use models\Admins;
use models\Courses;


/**
 * Responsible for the behavior of the view {@link studentsManager/students_manager.php}.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class StudentsController extends Controller
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * It will check if admin is logged; otherwise, redirects him to login
     * page.
     */
    public function __construct()
    {
        if (!Admins::isLogged()) {
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
        $admins = new Admins($_SESSION['a_login']);
        $students = new Students();
        
        $header = array(
            'title' => 'Students manager - Learning platform',
            'styles' => array('studentsManager')
        );
        
        $params = array(
            'adminName' => $admins->getName(),
            'students' => $students->getAll(),
            'header' => $header,
            'scripts' => array('studentsManager')
        );
        
        $this->loadTemplate("studentsManager/students_manager", $params);
    }
    
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
    /**
     * Adds a new student.
     * 
     * @return      int Student id or -1 if an error occurred while adding the
     * student
     */
    public function add_student()
    {
        if (empty($_POST['email'])) { echo -1; }
        
        $students = new Students();
        $student = new Student($_POST['name'], $_POST['genre'], $_POST['birthdate'], $_POST['email'], $_POST['password']);
        
        echo $students->register($student, false);
    }
    
    /**
     * Edits a student.
     * 
     * @return      bool If the student was sucessfully edited
     */
    public function edit_student()
    {
        if (empty($_POST['email'])) { echo false; }
        
        $students = new Students();
        
        if (empty($_POST['passowrd']))
            $student = new Student($_POST['name'], $_POST['genre'], $_POST['birthdate'], $_POST['email'], null);
        else
            $student = new Student($_POST['name'], $_POST['genre'], $_POST['birthdate'], $_POST['email'], $_POST['password']);
        
        echo $students->edit($student);
    }
    
    /**
     * Gets informations about a student
     * 
     * @return      string JSON with student information
     */
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
    
    /**
     * Deletes a student.
     * 
     * @return      boolean If the student was successfully deleted.
     */
    public function delete_student()
    {
        if (empty($_POST['id_student'])) { echo false; }
        
        $students = new Students();
       
        echo $students->delete($_POST['id_student']);
    }
    
    /**
     * Gets informations about all courses that a student has.
     * 
     * @return      string JSON with informations about all courses that a
     * student has
     */
    public function get_courses()
    {
        if (empty($_POST['id_student'])) { echo false; }
        
        $courses = new Courses();
        
        echo json_encode($courses->getAll($_POST['id_student']));
    }
    
    /**
     * Adds a course to a student.
     * 
     * @return      boolean If the course was sucessfully added to the student
     */
    public function add_student_course()
    {
        if (empty($_POST['id_student']) || empty($_POST['id_course'])) { echo false; }
        
        $students = new Students();
        
        echo $students->addCourse($_POST['id_student'], $_POST['id_course']);
    }
    
    /**
     * Deletes all courses from a student.
     * 
     * @return      boolean If the courses were successfully deleted from the
     * student
     */
    public function clear_student_course()
    {
        if (empty($_POST['id_student']) || empty($_POST['id_course'])) { echo false; }
        
        $students = new Students();
        
        echo $students->deleteAllCourses($_POST['id_student']);
    }
}
