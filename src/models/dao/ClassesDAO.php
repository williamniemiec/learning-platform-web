<?php
declare (strict_types=1);

namespace models\dao;


use models\enum\ClassTypeEnum;
use models\_Class;


/**
 * Responsible for representing classes.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
abstract class ClassesDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    protected $db;
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets all classes from a module.
     *
     * @param       int $id_module Module id
     *
     * @return      array Classes that belongs to the module
     *
     * @throws      \InvalidArgumentException If module id or student id is 
     * empty or less than or equal to zero
     */
    public abstract function getAllFromModule(int $id_module) : array;
    
    /**
     * Marks a class as watched by a student.
     * 
     * @param       int $id_student Student id
     * @param       int $id_module Module id
     * @param       int $class_order Class order
     * @param       ClassTypeEnum $class_type Class type
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function _markAsWatched(int $id_student, int $id_module, int $class_order, 
        ClassTypeEnum $class_type) : void
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
                
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Class order cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($class_type) || (empty($class_type->get()) && $class_type->get() != 0))
            throw new \InvalidArgumentException("Class type cannot be empty ");
                    
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO student_historic
            (id_student, id_module, class_order, class_type, date)
            VALUES (?, ?, ?, ?, CURDATE())
        ");
        
        // Executes query
        $sql->execute(array($id_student, $id_module, $class_order, $class_type->get() == 1));
    }
    
    /**
     * Gets total duration (in minutes) of all classes.
     *
     * @return      int Total duration (in minutes)
     */
    public abstract function totalLength() : int;
    
    /**
     * Checks whether a student watched a specific class.
     * 
     * @param       int $id_student Student id
     * @param       int $id_module Module id to which the class belongs
     * @param       int $class_order Class order in module
     * 
     * @return      bool If student watched the class or not
     */
    public abstract function wasWatched(int $id_student, int $id_module, int $class_order) : bool;
    
    /**
     * Removes watched class markup from a class.
     * 
     * @param       int $id_student Student id
     * @param       int $id_module Module id
     * @param       int $class_order Class order
     * 
     * @throws      \InvalidArgumentException If student id, module id or 
     * class order is empty or less than or equal to zero
     */
    public function removeWatched(int $id_student, int $id_module, int $class_order) : void
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Class order cannot be empty ".
                "or less than or equal to zero");
        
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM student_historic 
            WHERE id_student = ? AND id_module = ? AND class_order = ?
        ");
        
        // Executes query
        $sql->execute(array($id_student, $id_module, $class_order));
    }
}