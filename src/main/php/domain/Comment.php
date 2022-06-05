<?php
declare (strict_types=1);

namespace domain;


use repositories\Database;
use DateTime;
use dao\CommentsDAO;


/**
 * Responsible for representing comments.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Comment
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $id_comment;
    private $id_course;
    private $id_module;
    private $class_order;
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
     * @param       int $id_comment Comment id
     * @param       int $id_course Course id to which the comment was made
     * @param       int $id_module module id of the class for which the comment was made
     * @param       int $class_order Class order in module of the class for 
     * which the comment was made
     * @param       Student $student Student who wrote the comment
     * @param       DateTime $date Comment posting date
     * @param       string $text Comment content
     * @param       Message[] $replies [Optional] Comment replies
     */
    public function __construct(int $id_comment, int $id_course, int $id_module, 
        int $class_order, ?Student $student, DateTime $date, string $text, 
        ?array $replies = array())
    {
        $this->id_comment = $id_comment;
        $this->id_course = $id_course;
        $this->id_module = $id_module;
        $this->class_order = $class_order;
        $this->student = $student;
        $this->date = $date;
        $this->text = $text;
        $this->replies = empty($replies) ? array() : $replies;
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
        return $this->id_comment;
    }
    
    /**
     * Gets course id to which the comment was made.
     * 
     * @return      int Course id
     */
    public function getCourseId() : int
    {
        return $this->id_course;
    }
    
    /**
     * Gets module id of the class for which the comment was made.
     *
     * @return      int Module id
     */
    public function getModuleId() : int
    {
        return $this->id_module;
    }
    
    /**
     * Gets class order in module of the class for which the comment was made.
     * 
     * @return      int Class order
     */
    public function getClassOrder() : int
    {
        return $this->class_order;
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
            $comments = new CommentsDAO($db);
            
            $this->replies = $comments->getReplies($this->id_comment);
        }
        
        return $this->replies;
    }
}