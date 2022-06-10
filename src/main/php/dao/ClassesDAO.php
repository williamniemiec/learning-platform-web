<?php
/**
 * Copyright (c) William Niemiec.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\enum\ClassTypeEnum;


/**
 * Responsible for representing classes.
 */
abstract class ClassesDAO extends DAO
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    protected function __construct(Database $db)
    {
        parent::__construct($db);
    }
    
    
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
     * @param       int idStudent Student id
     * @param       int idModule Module id
     * @param       int classOrder Class order
     * @param       ClassTypeEnum classType Class type
     * 
     * @return      bool If class has been successfully added to student history
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function _MarkAsWatched(int $idStudent, int $idModule, int $classOrder, 
        ClassTypeEnum $classType) : bool
    {
        $this->validateStudentId($idStudent);
        $this->validateModuleId($idModule);
        $this->validateClassOrder($classOrder);
        $this->validateClassType($classType);
        $this->withQuery($this->buildMarkAsWatchedQuery($classType));
        $this->runQueryWithArguments($idStudent, $idModule, $classOrder);
        
        return $this->hasResponseQuery();
    }

    private function validateStudentId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Student id cannot be empty or".
                                                "less than or equal to zero");
        }
    }

    protected function validateModuleId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Module id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }

    protected function validateClassOrder($order)
    {
        if (empty($order) || $order <= 0) {
            throw new \InvalidArgumentException("Class order cannot be empty ".
                                                "or less than or equal to zero");
        }
    }

    private function validateClassType($type)
    {
        if (empty($type) || (empty($type->get()) && $type->get() != 0)) {
            throw new \InvalidArgumentException("Class type cannot be empty");
        }
    }

    private function buildMarkAsWatchedQuery($classType)
    {
        $classType = $classType->get() == 1 ? "b'1'" : "b'0'";
        
        return "
            INSERT INTO student_historic
            (id_student, id_module, class_order, class_type, date)
            VALUES (?, ?, ?, ".$classType.", CURDATE())
        ";
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
     * @param       int idStudent Student id
     * @param       int idModule Module id to which the class belongs
     * @param       int classOrder Class order in module
     * 
     * @return      bool If student watched the class or not
     */
    public abstract function wasWatched(int $idStudent, int $idModule, int $classOrder) : bool;
    
    /**
     * Removes watched class markup from a class.
     * 
     * @param       int idStudent Student id
     * @param       int idModule Module id
     * @param       int classOrder Class order
     * 
     * @return      bool If class has been successfully removed from student history
     * 
     * @throws      \InvalidArgumentException If student id, module id or 
     * class order is empty or less than or equal to zero
     */
    public function removeWatched(int $idStudent, int $idModule, int $classOrder) : bool
    {
        $this->validateStudentId($idStudent);
        $this->validateModuleId($idModule);
        $this->validateClassOrder($classOrder);
        $this->withQuery("
            DELETE FROM student_historic 
            WHERE id_student = ? AND id_module = ? AND class_order = ?
        ");
        $this->runQueryWithArguments($idStudent, $idModule, $classOrder);
        
        return $this->hasResponseQuery();
    }
    
    /**
     * Gets total of classes.
     *
     * @param       Database $db Database
     *
     * @return      array Total of classes and length. The returned array has
     * the following keys:
     * <ul>
     *  <li>total_classes</li>
     *  <li>total_length</li>
     * </ul>
     */
    public static function getTotal(Database $db) : array
    {
        return $db->getConnection()->query("
            SELECT  SUM(total) AS total_classes, 
                    SUM(length) AS total_length 
            FROM (
                SELECT  COUNT(*) AS total, SUM(length) as length
                FROM    videos
                UNION
                SELECT  COUNT(*) AS total, SUM(5) AS length
                FROM questionnaires
            ) AS tmp
        ")->fetch();
    }
}