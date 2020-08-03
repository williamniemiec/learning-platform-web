<?php
declare (strict_types=1);

namespace models;


use database\Database;
use DateTime;
use models\dao\CommentsDAO;


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
     * @param       Student $student Student who wrote the comment
     * @param       DateTime $date Comment posting date
     * @param       string $text Comment content
     * @param       Message[] $replies [Optional] Comment replies
     */
    public function __construct(int $id_comment, Student $student, DateTime $date, 
        string $text, array $replies = array())
    {
        $this->id_comment = $id_comment;
        $this->student = $student;
        $this->date = $date;
        $this->text = $text;
        $this->replies = $replies;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets comment id.
     * 
     * @return      int Comment id
     */
    public function getCommentId() : int
    {
        return $this->id_comment;
    }
    
    /**
     * Gets comment creator.
     *
     * @return      Student Student who created the comment
     */
    public function getCreator() : Student
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