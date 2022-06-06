<?php
declare (strict_types=1);

namespace domain;


use repositories\Database;
use DateTime;
use dao\CommentsDAO;


/**
 * Responsible for representing comments.
 */
class Comment
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $idComment;
    private $idCourse;
    private $idModule;
    private $classOrder;
    private $student;
    private $date;
    private $text;
    private $replies;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a comment.
     *
     * @param       int idComment Comment id
     * @param       int idCourse Course id to which the comment was made
     * @param       int idModule module id of the class for which the comment was made
     * @param       int classOrder Class order in module of the class for 
     * which the comment was made
     * @param       Student $student Student who wrote the comment
     * @param       DateTime $date Comment posting date
     * @param       string $text Comment content
     * @param       Message[] $replies [Optional] Comment replies
     */
    public function __construct(
        int $idComment, 
        int $idCourse, 
        int $idModule, 
        int $classOrder, 
        ?Student $student, 
        DateTime $date, 
        string $text, 
        ?array $replies = array())
    {
        $this->idComment = $idComment;
        $this->idCourse = $idCourse;
        $this->idModule = $idModule;
        $this->classOrder = $classOrder;
        $this->student = $student;
        $this->date = $date;
        $this->text = $text;
        $this->replies = empty($replies) ? array() : $replies;
    }


    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    private function fetchReplies($db)
    {
        $commentsDao = new CommentsDAO($db);
            
        return $commentsDao->getReplies($this->idComment);
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets comment id.
     * 
     * @return      int Comment id
     */
    public function getId() : int
    {
        return $this->idComment;
    }
    
    /**
     * Gets course id to which the comment was made.
     * 
     * @return      int Course id
     */
    public function getCourseId() : int
    {
        return $this->idCourse;
    }
    
    /**
     * Gets module id of the class for which the comment was made.
     *
     * @return      int Module id
     */
    public function getModuleId() : int
    {
        return $this->idModule;
    }
    
    /**
     * Gets class order in module of the class for which the comment was made.
     * 
     * @return      int Class order
     */
    public function getClassOrder() : int
    {
        return $this->classOrder;
    }
    
    /**
     * Gets comment creator.
     *
     * @return      Student Student who created the comment
     */
    public function getCreator() : ?Student
    {
        return $this->student;
    }
    
    /**
     * Gets comment creation date.
     *
     * @return      DateTime Comment creation date
     */
    public function getCreationDate() : DateTime
    {
        return $this->date;
    }
    
    /**
     * Gets comment content.
     *
     * @return      string Comment content
     */
    public function getContent() : string
    {
        return $this->text;
    }
    
    /**
     * Gets comment replies.
     * 
     * @param       Database $db Database
     * 
     * @return      Message[] Comment replies or empty array if there are no
     * replies
     */
    public function getReplies(Database $db) : array
    {
        if (empty($this->replies)) {
            $this->replies = $this->fetchReplies($db);
        }
        
        return $this->replies;
    }
}