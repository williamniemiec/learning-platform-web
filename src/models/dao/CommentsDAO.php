<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Message;
use models\Comment;


/**
 * Responsible for managing 'comments' table.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class CommentsDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $db;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'comments' table manager.
     *
     * @param       Database $db Database
     */
    public function __construct(Database $db)
    {
        $this->db = $db->getConnection();
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Adds a new comment to a class.
     * 
     * @param       int $id_student Student id
     * @param       int $id_module Module id that the class belongs
     * @param       int $class_order Class order within the module
     * @param       string $text Comment content
     * 
     * @return      bool If the comment was successfully added
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function newComment(int $id_student, int $id_module, int $class_order, string $text) : bool
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
        
        if (empty($text))
            throw new \InvalidArgumentException("Invalid text");
        
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO comments 
            (id_student, id_module, class_order, date, text) 
            VALUES (?, ?, ?, NOW(), ?)
        ");
        
        // Executes query
        $sql->execute(array($id_student, $id_module, $class_order, $text));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Gets comments from a class.
     * 
     * @param       int $id_module Module id that the class belongs
     * @param       int $class_order Class order within the module
     * 
     * @return      Comment[] Comments from this class
     * 
     * @throws      \InvalidArgumentException If module id or class order is 
     * empty or less than or equal to zero
     */
    public function getComments(int $id_module, int $class_order) : array
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Class order cannot be empty ".
                "or less than or equal to zero");
        
        $response = array();
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  student_name, student_photo, text, date
            FROM    comments NATURAL JOIN students
            WHERE   id_module = ? AND class_order = ?
        ");
        
        // Executes query
        $sql->execute(array($id_module, $class_order));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll(\PDO::FETCH_ASSOC) as $comment) {
                $response[] = new Comment(
                    $comment['id_comment'], 
                    $comment['student'], 
                    $comment['date'], 
                    $comment['text']
                );
            }
        }
        
        return $response;
    }
    
    /**
     * Replies a comment.
     * 
     * @param       int $id_comment Comment id
     * @param       int $id_student Student id that replies the comment
     * @param       string $text Reply content
     * 
     * @return      int Reply id added or -1 if reply was not added
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function newReply(int $id_comment, int $id_student, string $text) : int 
    {
        if (empty($id_comment) || $id_comment <= 0)
            throw new \InvalidArgumentException("Comment id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($text))
            throw new \InvalidArgumentException("Invalid text");
        
        $response = -1;
        
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO comment_replies 
            (id_comment, id_student, date, text) 
            VALUES (?, ?, NOW(), ?)
        ");
        
        // Executes query
        $sql->execute(array($id_comment, $id_student, $text));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $response = $this->db->lastInsertId();
        }
        
        return $response;
    }
    
    /**
     * Gets replies from a comment.
     * 
     * @param       int $id_comment Comment id
     * 
     * @return      array Replies from this comment
     * 
     * @throws      \InvalidArgumentException If comment id is empty or less 
     * than or equal to zero
     */
    public function getReplies(int $id_comment) : array
    {
        if (empty($id_comment) || $id_comment <= 0)
            throw new \InvalidArgumentException("Comment id cannot be empty ".
                "or less than or equal to zero");
        
        $response = array();
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  student_name, student_photo, text, date
            FROM    comment_replies NATURAL JOIN students
            WHERE   id_comment = ?
        ");
        
        // Executes query
        $sql->execute(array($id_comment));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $replies = $sql->fetchAll(\PDO::FETCH_ASSOC);
            $students = new StudentsDAO($this->db);
            
            foreach ($replies as $reply) {
                $response[] = new Message(
                    $students->get($reply['id_student']),
                    $reply['date'], 
                    $reply['text']
                );                
            }
        }
        
        return $response;
    }
    
    /**
     * Deletes a comment.
     * 
     * @param       int $id_comment Doubt id
     * 
     * @return      boolean If doubt was sucessfully deleted
     * 
     * @throws      \InvalidArgumentException If comment id is empty or less 
     * than or equal to zero
     */
    public function deleteComment(int $id_comment) : bool
    {
        if (empty($id_comment) || $id_comment <= 0)
            throw new \InvalidArgumentException("Comment id cannot be empty ".
                "or less than or equal to zero");
        
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM comments 
            WHERE id_comment = ?
        ");
        
        // Executes query
        $sql->execute(array($id_comment));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Deletes a reply.
     * 
     * @param       int $id_comment Comment id that the reply belongs to
     * @param       int $id_student Student id that made the reply
     * 
     * @return      boolean If reply was sucessfully deleted
     * 
     * @throws      \InvalidArgumentException If comment id or student id is 
     * empty or less than or equal to zero
     */
    public function deleteReply(int $id_comment, int $id_student) : bool
    {
        if (empty($id_comment) || $id_comment <= 0)
            throw new \InvalidArgumentException("Comment id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM comment_replies 
            WHERE id_comment = ? AND id_student = ? 
        ");
        
        // Executes query
        $sql->execute(array($id_comment, $id_student));
        
        return $sql && $sql->rowCount() > 0;
    }
}