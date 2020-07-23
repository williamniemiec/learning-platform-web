<?php
namespace models;

use core\Model;
use models\obj\Message;
use models\obj\Comment;


/**
 * Responsible for managing comments table.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class Comments extends Model
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates doubts table manager.
     *
     * @apiNote     It will connect to the database when it is instantiated
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Adds a new comment to a class.
     * 
     * @param       int $id_student Student id
     * @param       int $id_class Class id
     * @param       string $text Doubt content
     * 
     * @return      boolean If the doubt was successfully added
     */
    public function newComment($id_student, $id_module, $class_order, $text)
    {
//         if (empty($id_class) && $id_class <= 0 || empty($id_student) && $id_student <= 0)
//             return false;
        
        
        //$students = new Students();
        
//         if (!$classes->exist($id_class) || !$students->exist($id_student)) { return false; }
        
        $sql = $this->db->prepare("
            INSERT INTO comments 
            (id_student, id_module, class_order, date, text) 
            VALUES (?, ?, ?, NOW(), ?)
        ");
        
        $sql->execute(array($id_student, $id_module, $class_order, $text));
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Gets comments from a class.
     * 
     * @param       int $id_class Class id
     * 
     * @return      array Doubts from this class
     */
    public function getComments($id_module, $class_order)
    {//
       // if (empty($id_class) && $id_class <= 0) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT  student_name, student_photo, text, date
            FROM    comments NATURAL JOIN students
            WHERE   id_module = ? AND class_order = ?
        ");
        
        $sql->execute(array($id_module, $class_order));
        
        if ($sql->rowCount() > 0) {
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
     * @param       int $id_comment Doubt id
     * @param       int $id_student Student id that will reply the doubt
     * @param       string $text Reply content
     * 
     * @return      boolean If reply was sucessfully added
     */
    public function newReply($id_comment, $id_student, $text) 
    {
        if (empty($id_comment) || $id_comment <= 0) { return -1; }
        if (empty($id_student) || $id_student <= 0)   { return -1; }
        if (empty($text))                       { return false; }
        
        $response = -1;
        
        $sql = $this->db->prepare("
            INSERT INTO comment_replies 
            (id_comment, id_student, date, text) 
            VALUES (?, ?, NOW(), ?)
        ");
        
        $sql->execute(array($id_comment, $id_student, $text));
        
        if ($sql->rowCount() > 0) {
            $response = $this->db->lastInsertId();
        }
        
        return $response;
    }
    
    /**
     * Gets replies from a comment.
     * 
     * @param       int $id_comment Doubt id
     * 
     * @return      array Replies from this doubt
     */
    public function getReplies($id_comment)
    {
//         if (empty($id_comment) || $id_comment <= 0) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT  student_name, student_photo, text, date
            FROM    comment_replies NATURAL JOIN students
            WHERE   id_comment = ?
        ");
        
        $sql->execute(array($id_comment));
        
        if ($sql->rowCount() > 0) {
            $replies = $sql->fetchAll(\PDO::FETCH_ASSOC);
            $students = new Students();
            
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
     */
    public function deleteComment($id_comment)
    {
        if (empty($id_comment) && $id_comment <= 0) { return false; }
        
        $sql = $this->db->prepare("
            DELETE FROM comments WHERE id_comment = ?
        ");
        
        $sql->execute(array($id_comment));
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Deletes a reply.
     * 
     * @param       int $id_reply Reply id
     * 
     * @return      boolean If reply was sucessfully deleted
     */
    public function deleteReply($id_comment, $id_student)
    {
//         if (empty($id_reply) && $id_reply <= 0) { return false; }
        
        $sql = $this->db->prepare("
            DELETE FROM comment_replies 
            WHERE id_comment = ? AND id_student = ? 
        ");
        $sql->execute(array($id_comment, $id_student));
        
        return $sql->rowCount() > 0;
    }
}