<?php
namespace models\obj;


/**
 * Responsible for representing a comment.
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
     * @param       string $date Comment posting date
     * @param       string $text Comment content
     * @param       Message[] $replies [Optional] Comment replies
     */
    public function __construct($id_comment, $student, $date, $text, $replies = array())
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
    public function getCommentId()
    {
        return $this->id_comment;
    }
    
    /**
     * Gets comment creator.
     *
     * @return      Student Student who created the comment
     */
    public function getCreator()
    {
        return $this->student;
    }
    
    /**
     * Gets comment creation date.
     *
     * @return      string Comment creation date
     */
    public function getCreationDate()
    {
        return $this->date;
    }
    
    /**
     * Gets comment content.
     *
     * @return      string Comment content
     */
    public function getContent()
    {
        return $this->text;
    }
    
    /**
     * Gets comment replies.
     *
     * @return      Message[] Comment replies or empty array if there are no
     * replies
     */
    public function getReplies()
    {
        return $this->replies;
    }
}