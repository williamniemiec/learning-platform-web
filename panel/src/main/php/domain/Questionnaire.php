<?php
declare (strict_types=1);

namespace panel\domain;


/**
 * Responsible for representing questionnaire-type classes.
 */
class Questionnaire extends ClassType
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $question;
    private $q1;
    private $q2;
    private $q3;
    private $q4;
    private $answer;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a questionnaire-type class.
     * 
     * @param       int idModule Module id that the class belongs to
     * @param       int classOrder Class order inside the module to which the
     * class belongs
     * @param       string $question Questionnaire question
     * @param       string $q1 First response option
     * @param       string $q2 Second response option
     * @param       string $q3 Third response option
     * @param       string $q4 Fourth response option
     * @param       int $answer Questionnaire answer (number between [1;4])
     */
    public function __construct(
        int $idModule, 
        int $classOrder, 
        string $question, 
        string $q1, 
        string $q2, 
        string $q3, 
        string $q4, 
        int $answer
    )
    {
        $this->idModule = $idModule;
        $this->classOrder = $classOrder;
        $this->question = $question;
        $this->q1 = $q1;
        $this->q2 = $q2;
        $this->q3 = $q3;
        $this->q4 = $q4;
        $this->answer = $answer;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets questionnaire question.
     * 
     * @return      string Questionnaire question
     */
    public function getQuestion() : string
    {
        return $this->question;
    }
    
    /**
     * Gets first response option.
     * 
     * @return      string First response option
     */
    public function getQ1() : string
    {
        return $this->q1;
    }
    
    /**
     * Gets second response option.
     *
     * @return      string Second response option
     */
    public function getQ2() : string
    {
        return $this->q2;
    }
    
    /**
     * Gets third response option.
     *
     * @return      string Third response option
     */
    public function getQ3() : string
    {
        return $this->q3;
    }
    
    /**
     * Gets fourth response option.
     *
     * @return      string Fourth response option
     */
    public function getQ4() : string
    {
        return $this->q4;
    }
    
    /**
     * Gets questionnaire answer.
     *
     * @return      int Questionnaire answer (number between [1;4])
     */
    public function getAnswer() : int
    {
        return $this->answer;
    }
    

    //-------------------------------------------------------------------------
    //        Serialization
    //-------------------------------------------------------------------------
    /**
     * {@inheritDoc}
     * @see \JsonSerializable::jsonSerialize()
     *
     * @Override
     */
    public function jsonSerialize(): array
    {
        $json = parent::jsonSerialize();
        $json['type'] = 'questionnaire';
        $json['question'] = $this->question;
        $json['q1'] = $this->q1;
        $json['q2'] = $this->q2;
        $json['q3'] = $this->q3;
        $json['q4'] = $this->q4;
        $json['answer'] = $this->answer;
        
        return $json;
    }
}