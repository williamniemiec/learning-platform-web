<?php
declare (strict_types=1);

namespace models;


use core\Model;
use models\obj\_Class;


/**
 * Responsible for representing classes.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
abstract class Classes extends Model
{
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets the first class from the first module from a course.
     *
     * @param       int $id_course Course id
     *
     * @return      _Class First class from the first module from a course or 
     * null if there are no registered modules - classes in the course with
     * the given id
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function getFirstClassFromFirstModule(int $id_course) : _Class
    {
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Invalid id_course");
        
        $response = null;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT      id_module, class_order, class_type FROM (
                SELECT    id_module, class_order, 'questionnaire' AS class_type
                FROM        questionnaires 
                            NATURAL JOIN course_modules
                WHERE       class_order = 1 AND id_course = ?
                UNION
                SELECT      id_module, class_order, 'video' AS class_type
                FROM        videos 
                            NATURAL JOIN course_module
                WHERE       class_order = 1 AND id_course = ?
            ) AS tmp JOIN course_modules USING (id_module)
            WHERE       id_course = ?
            ORDER BY    module_order
        ");
        
        // Executes query
        $sql->execute(array($id_course, $id_course, $id_course));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $response = $sql->fetch();
            
            if ($response['class_type'] == 'video') {
                $videos = new Videos();
                
                $response = $videos->get($response['id_module'], 1);
            } else {
                $quests = new Questionnaires();
                
                $response = $quests->get($response['id_module'], 1);
            }
        }
        
        return $response;
    }

    /**
     * Marks a class as watched by a student.
     * 
     * @param       int $id_student Student id
     * @param       int $id_module Module id
     * @param       int $class_order Class order
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public abstract function markAsWatched(int $id_student, int $id_module, int $class_order) : void;
    
    /**
     * Removes watched class markup from a class.
     * 
     * @param       int $id_student Student id
     * @param       int $id_module Module id
     * @param       int $class_order Class order
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function removeWatched(int $id_student, int $id_module, int $class_order) : void
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Invalid id_student");
            
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Invalid id_module");
            
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Class order must be greater than zero");
        
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM student_historic 
            WHERE id_student = ? AND id_module = ? AND class_order = ?
        ");
        
        // Executes query
        $sql->execute(array($id_student, $id_module, $class_order));
    }
}