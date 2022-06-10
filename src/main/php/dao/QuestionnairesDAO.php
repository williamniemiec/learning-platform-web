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
use domain\Questionnaire;
use domain\enum\ClassTypeEnum;


/**
 * Responsible for managing 'questionnaires' table.
 */
class QuestionnairesDAO extends ClassesDAO
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'questionnaires' table manager.
     *
     * @param       Database $db Database
     */
    public function __construct(Database $db)
    {
        parent::__construct($db);
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets questionnaire class
     *
     * @param       int idModule Module id that the class belongs to
     * @param       int classOrder Class order inside the module that it 
     * belongs to
     *
     * @return      Questionnaire Questionnaire class or null if class does not
     * exist
     * 
     * @throws      \InvalidArgumentException If module id or class order is 
     * empty or less than or equal to zero
     */
    public function get(int $idModule, int $classOrder) : Questionnaire
    {
        $this->validateModuleId($idModule);
        $this->validateClassOrder($classOrder);
        $this->withQuery("
            SELECT  * 
            FROM    questionnaires 
            WHERE   id_module = ? AND class_order = ?
        ");
        $this->runQueryWithArguments($idModule, $classOrder);
        
        return $this->parseQuestionnaireResponseQuery();
    }

    private function parseQuestionnaireResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return null;
        }
        
        $rawClass = $this->getResponseQuery();
            
        return new Questionnaire(
            (int) $rawClass['id_module'],
            (int) $rawClass['class_order'],
            $rawClass['question'],
            $rawClass['q1'],
            $rawClass['q2'],
            $rawClass['q3'],
            $rawClass['q4'],
            (int) $rawClass['answer']
        ); 
    }

    /**
     * Gets the answer from a quest.
     *
     * @param       int idModule Module id that the class belongs to
     * @param       int classOrder Class order inside the module that it 
     * belongs to
     *
     * @return      int Correct answer
     * 
     * @throws      \InvalidArgumentException If module id or class order is 
     * empty or less than or equal to zero
     */
    public function getAnswer(int $idModule, int $classOrder) : int
    {
        $this->validateModuleId($idModule);
        $this->validateClassOrder($idModule);
        $this->withQuery("
            SELECT  answer 
            FROM    questionnaires 
            WHERE   id_module = ? AND class_order = ?
        ");
        $this->runQueryWithArguments($idModule, $classOrder);
        
        return $this->parseAnswerResponseQuery();
    }

    private function parseAnswerResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return -1;
        }
        
        return ((int) $this->getResponseQuery()['answer']);
    }
    
    /**
     * Gets all questionnaire classes from a module.
     * 
     * @param       int $idModule Module id
     * 
     * @return      Questionnaire[] Classes that belongs to the module
     * 
     * @throws      \InvalidArgumentException If module id is empty or less
     * than or equal to zero
     * 
     * @Override
     */
    public function getAllFromModule(int $idModule) : array
    {
        $this->validateModuleId($idModule);
        $this->withQuery("
            SELECT  *
            FROM    questionnaires
            WHERE   id_module = ?   
        ");
        $this->runQueryWithArguments($idModule);

        return $this->parseQuestionnairesResponseQuery();
    }

    private function parseQuestionnairesResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $classes = array();
            
        foreach ($this->getAllResponseQuery() as $class) {
            $classes[] = new Questionnaire(
                (int) $class['id_module'], 
                (int) $class['class_order'], 
                $class['question'], 
                $class['q1'], 
                $class['q2'], 
                $class['q3'], 
                $class['q4'], 
                (int) $class['answer']
            ); 
        }

        return $classes;
    }
    
    /**
     * {@inheritdoc}
     * @Override
     */
    public function totalLength() : int
    {
        $this->withQuery("
            SELECT  SUM(length) AS total_length
            FROM    (SELECT 5 AS length
                    FROM   questionnaires) AS tmp
        ");
        $this->runQueryWithoutArguments();

        return $this->getResponseQuery()['total_length'];
    }
    
    /**
     * {@inheritdoc}
     * @Override
     */
    public function wasWatched(int $id_student, int $id_module, int $class_order) : bool
    {
        $this->withQuery("
            SELECT  COUNT(*) AS was_watched
            FROM    student_historic
            WHERE   class_type = 1 AND
                    id_student = ? AND
                    id_module = ? AND
                    class_order = ?
        ");
        $this->runQueryWithArguments($id_student, $id_module, $class_order);
        
        return ($this->getResponseQuery()['was_watched'] > 0);
    }
    
    /**
     * Marks a class as watched by a student.
     *
     * @param       int $id_student Student id
     * @param       int $id_module Module id
     * @param       int $class_order Class order
     *
     * @return      bool If class has been successfully added to student history
     *
     * @throws      \InvalidArgumentException If any argument is invalid
     *
     * @Override
     */
    public function markAsWatched(int $id_student, int $id_module, int $class_order) : bool
    {
        return $this->_MarkAsWatched($id_student, $id_module, $class_order, 
            new ClassTypeEnum(ClassTypeEnum::QUESTIONNAIRE));
    }
}