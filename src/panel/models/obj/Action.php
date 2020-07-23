<?php 
namespace models\obj;




class Action extends User
{
    public static function addCourse($id)
    {
        return "Course added - id=".$id;
    }
    
    public static function editCourse($id)
    {
        return "Course edited - id=".$id;
    }
    
    public static function deleteCourse($id)
    {
        return "Course deleted - id=".$id;
    }
    
    public static function addBundle($id)
    {
        return "Bundle added - id=".$id;
    }
    
    public static function editBundle($id)
    {
        return "Bundle edited - id=".$id;
    }
    
    public static function deleteBundle($id)
    {
        return "Course deleted - id=".$id;
    }
    
    public static function editStudent($id)
    {
        return "Student edited - id=".$id;
    }
    
    public static function deleteStudent($id)
    {
        return "Student removed - id=".$id;
    }
    
    public static function answerTopic($id)
    {
        return "Topic answered - id=".$id;
    }
    
    public static function openTopic($id)
    {
        return "Topic opened - id=".$id;
    }
    
    public static function closeTopic($id)
    {
        return "Topic closed - id=".$id;
    }
}