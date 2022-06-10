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


/**
 * Responsible for managing 'student_historic' table.
 */
class HistoricDAO extends DAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $idStudent;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'student_historic' table manager.
     *
     * @param       Database $db Database
     * 
     * @throws      \InvalidArgumentException If student id is empty or less 
     * than or equal to zero
     */
    public function __construct(Database $db, int $idStudent)
    {
        parent::__construct($db);
        $this->validateStudentId($idStudent);
        $this->idStudent = $idStudent;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    private function validateStudentId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Student id cannot be empty ".
                                                "or less than or equal to zero");
        }
    }

    /**
     * Gets total watched classes by a student in a course.
     * 
     * @param       int idCourse Course id
     * 
     * @return      int Watched classes
     * 
     * @throws      \InvalidArgumentException If course id is empty or less 
     * than or equal to zero
     */
    public function countWatchedClasses(int $idCourse) : int
    {
        $this->validateCourseId($idCourse);
        $this->withQuery("
            SELECT  COUNT(*) AS watchedClasses
            FROM    student_historic NATURAL JOIN course_modules
            WHERE   id_student = ? AND id_course = ?
        ");
        $this->runQueryWithArguments($this->idStudent, $idCourse);
        
        return ((int) $this->getResponseQuery()['watchedClasses']);
    }

    private function validateCourseId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Course id cannot be empty or".
                                                "less than or equal to zero");
        }
    }

    /**
     * Removes all student history.
     * 
     * @return      bool If historic has been successfully removed
     */
    public function clear() : bool
    {
        $this->withQuery("
            DELETE FROM student_historic
            WHERE id_student = ?
        ");
        $this->runQueryWithArguments($this->idStudent);
        
        return $this->hasResponseQuery();
    }
    
    /**
     * 
     * Gets all classes classes watched by the student.
     * 
     * @param       int idCourse Course id
     * 
     * @return      array Classes watched by the student. It will return an 
     * array in the following format:
     * <code>$returnedArray[id_module][class_order] = true</code>
     * 
     * @throws      \InvalidArgumentException If course id is empty or less 
     * than or equal to zero
     */
    public function getWatchedClassesFromCourse(int $idCourse)
    {
        $this->validateCourseId($idCourse);
        $this->withQuery("
            SELECT  id_module, class_order
            FROM    student_historic
            WHERE   id_student = ? AND
                    id_module IN (SELECT    id_module
                                  FROM      course_modules
                                  WHERE     id_course = ?)
        ");
        $this->runQueryWithArguments($this->idStudent, $idCourse);
        
        return $this->parseWatchedClassesResponseQuery();
    }

    private function parseWatchedClassesResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $watchedClasses = array();
            
        foreach ($this->getAllResponseQuery() as $class) {
            $watchedClasses[$class['id_module']][$class['class_order']] = true;
        }

        return $watchedClasses;
    }
    
    /**
     * Gets total classes that a student watched in the last 7 days.
     * 
     * @return      array Total classes that a student watched in the last 7
     * days. Each key of the returned array has the following format: 
     *  <ul>
     *      <li><b>date</b>: Date in the following format: YYYY/MM/DD</li>
     *      <li><b>total_classes_watched</b>: Total classes watched by the 
     *      student on this date</li>
     *  </ul>
     */
    public function getWeeklyHistory() : array
    {
        $this->withQuery("
            SELECT      date, COUNT(*) AS total_classes_watched
            FROM        student_historic
            WHERE       id_student = ? AND 
                        date >= DATE_ADD(CURDATE(), INTERVAL -7 DAY)
            GROUP BY    date
            ORDER BY    date DESC
            LIMIT 7
        ");
        $this->runQueryWithArguments($this->idStudent);
        
        return $this->parsHistoryResponseQuery();
    }

    private function parsHistoryResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $history = array();
        $i = 0;
            
        foreach ($this->getAllResponseQuery() as $date) {
            $history[$i]['date'] = str_replace("-", "/", $date['date']);
            $history[$i]['total_classes_watched'] = $date['total_classes_watched'];
            $i++;
        }

        return $history;
    }
}