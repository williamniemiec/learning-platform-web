<?php
namespace models;

use core\Model;


/**
 *
 */
class Historic extends Model
{
    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    public function __construct()
    {
        parent::__construct();
    }
    
    
    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
    public function getWatchedClasses($id_student, $id_course)
    {
        $classes = new Classes();
        $classIds = $classes->getClassesInCourse($id_course);
        if (count($classIds) == 0) { return 0; }
        
        $sql = "
            SELECT
                COUNT(*) AS watchedClasses
            FROM historic
            WHERE
                id_student = ? AND 
                id_class IN (".implode(",", $classIds).")
        ";
        
        $sql = $this->db->prepare($sql);
        $sql->execute(array($id_student));
        
        return $sql->fetch()['watchedClasses'];
    }
}