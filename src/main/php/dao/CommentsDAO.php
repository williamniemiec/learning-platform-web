<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Message;
use domain\Comment;


/**
 * Responsible for managing 'comments' table.
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
     * @param       int idStudent Student id
     * @param       int idCourse Course id to which the class belongs
     * @param       int idModule Module id that the class belongs
     * @param       int classOrder Class order within the module
     * @param       string $text Comment content
     * 
     * @return      int Comment id or -1 if comment has not been added
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function newComment(int $idStudent, int $idCourse, int $idModule, 
        int $classOrder, string $text) : int
    {
        if (empty($idStudent) || $idStudent <= 0) {
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        }
        
        if (empty($idCourse) || $idCourse <= 0) {
            throw new \InvalidArgumentException("Course id cannot be empty ".
                "or less than or equal to zero");
        }
            
        if (empty($idModule) || $idModule <= 0) {
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
        }
        
        if (empty($classOrder) || $classOrder <= 0) {
            throw new \InvalidArgumentException("Class order cannot be empty ".
                "or less than or equal to zero");
        }
        
        if (empty($text)) {
            throw new \InvalidArgumentException("Invalid text");
        }
        
        $response = -1;
            
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO comments 
            (id_student, id_course, id_module, class_order, date, text) 
            VALUES (?, ?,  ?, ?, NOW(), ?)
        ");
        
        // Executes query
        $sql->execute(array($idStudent, $idCourse, $idModule, $classOrder, $text));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = (int) $this->db->lastInsertId();
        }
        
        return $response;
    }
    
    /**
     * Gets a comment.
     *
     * @param       int idComment Comment id
     *
     * @return      Comment Comment with the specified id of null if does not 
     * exist a commend with the specified id 
     *
     * @throws      \InvalidArgumentException If comment id is empty or less 
     * than or equal to zero
     */
    public function get(int $idComment) : ?Comment
    {
        if (empty($idComment) || $idComment <= 0) {
            throw new \InvalidArgumentException("Comment id cannot be empty ".
                "or less than or equal to zero");
        }
            
            $response = null;
                
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    comments
            WHERE   id_comment = ?
        ");
                
        // Executes query
        $sql->execute(array($idComment));
                
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $comment = $sql->fetch();
            $students_dao = new StudentsDAO($this->db, (int) $comment['id_student']);
            
            $response = new Comment(
                (int) $comment['id_comment'],
                (int) $comment['id_course'],
                (int) $comment['id_module'],
                (int) $comment['class_order'],
                $students_dao->get(),
                new \DateTime($comment['date']),
                $comment['text']
            );
        }
        
        return $response;
    }
    
    /**
     * Gets comments from a class.
     * 
     * @param       int idModule Module id that the class belongs
     * @param       int classOrder Class order within the module
     * 
     * @return      array Comments from this class along with its replies. Each 
     * position of the returned array has the following keys:
     * <ul>
     *  <li><b>comment</b>: (type:Comment) Comment</li>
     *  <li><b>replies</b>: (type:Message[]) Comment replies</li>
     * </ul>
     * 
     * @throws      \InvalidArgumentException If module id or class order is 
     * empty or less than or equal to zero
     */
    public function getComments(int $idModule, int $classOrder) : array
    {
        if (empty($idModule) || $idModule <= 0) {
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
        }
            
        if (empty($classOrder) || $classOrder <= 0) {
            throw new \InvalidArgumentException("Class order cannot be empty ".
                "or less than or equal to zero");
        }
        
        $response = array();
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    comments NATURAL JOIN students
            WHERE   id_module = ? AND class_order = ?
        ");
        
        // Executes query
        $sql->execute(array($idModule, $classOrder));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $i = 0;
            
            foreach ($sql->fetchAll() as $comment) {
                $students_dao = new StudentsDAO($this->db, (int) $comment['id_student']);
                
                $response[$i]['comment'] = new Comment(
                    (int) $comment['id_comment'],
                    (int) $comment['id_course'],
                    (int) $comment['id_module'],
                    (int) $comment['class_order'],
                    $students_dao->get(), 
                    new \DateTime($comment['date']), 
                    $comment['text']
                );
                
                $response[$i]['replies'] = $this->getReplies((int) $comment['id_comment']);
                
                $i++;
            }
        }
        
        return $response;
    }
    
    /**
     * Replies a comment.
     * 
     * @param       int idComment Comment id
     * @param       int idStudent Student id that replies the comment
     * @param       string $text Reply content
     * 
     * @return      int Reply id added or -1 if reply has not been added
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function newReply(int $idComment, int $idStudent, string $text) : int 
    {
        if (empty($idComment) || $idComment <= 0) {
            throw new \InvalidArgumentException("Comment id cannot be empty ".
                "or less than or equal to zero");
        }
            
        if (empty($idStudent) || $idStudent <= 0) {
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        }
        
        if (empty($text)) {
            throw new \InvalidArgumentException("Invalid text");
        }
        
        $response = -1;
        
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO comment_replies 
            (id_comment, id_student, date, text) 
            VALUES (?, ?, NOW(), ?)
        ");
        
        // Executes query
        $sql->execute(array($idComment, $idStudent, $text));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $response = (int) $this->db->lastInsertId();
        }
        
        return $response;
    }
    
    /**
     * Deletes a comment.
     * 
     * @param       int idComment Doubt id
     * @param       int idStudent Student id logged in. It is necessary to 
     * prevent a student from deleting a comment that is not his 
     * 
     * @return      boolean If doubt has been successfully deleted
     * 
     * @throws      \InvalidArgumentException If comment id or student id is 
     * empty or less than or equal to zero
     */
    public function deleteComment(int $idComment, int $idStudent) : bool
    {
        if (empty($idComment) || $idComment <= 0) {
            throw new \InvalidArgumentException("Comment id cannot be empty ".
                "or less than or equal to zero");
        }
        
        if (empty($idStudent) || $idStudent <= 0) {
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        }
            
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM comments 
            WHERE id_comment = ?
        ");
        
        // Executes query
        $sql->execute(array($idComment));
        
        return !empty($sql) && $sql->rowCount() > 0;
    }
    
    /**
     * Removes a reply.
     * 
     * @param       int $id_reply Reply id to be removed
     * @param       int $id_student Student id logged in. It is necessary to 
     * prevent a student from deleting a reply that is not his 
     * 
     * @return      bool If reply has been successfully deleted
     * 
     * @throws      \InvalidArgumentException If reply id or student id is 
     * empty or less than or equal to zero
     */
    public function deleteReply(int $id_reply, int $id_student) : bool
    {
        if (empty($id_reply) || $id_reply <= 0) {
            throw new \InvalidArgumentException("Reply id cannot be empty ".
                "or less than or equal to zero");
        }
            
        if (empty($id_student) || $id_student <= 0) {
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        }
            
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM comment_replies 
            WHERE id_reply = ? AND id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($id_reply, $id_student));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Gets replies from a comment.
     *
     * @param       int $id_comment Comment id
     *
     * @return      Message[] Replies from this comment
     *
     * @throws      \InvalidArgumentException If comment id is empty or less
     * than or equal to zero
     */
    private function getReplies(int $id_comment) : array
    {
        if (empty($id_comment) || $id_comment <= 0) {
            throw new \InvalidArgumentException("Comment id cannot be empty ".
                "or less than or equal to zero");
        }
            
            $response = array();
            
            // Query construction
            $sql = $this->db->prepare("
            SELECT  *
            FROM    comment_replies
            WHERE   id_comment = ?
        ");
            
            // Executes query
            $sql->execute(array($id_comment));
            
            // Parses results
            if ($sql && $sql->rowCount() > 0) {
                $replies = $sql->fetchAll();
                
                foreach ($replies as $reply) {
                    $students = new StudentsDAO($this->db, (int) $reply['id_student']);
                    
                    $response[] = new Message(
                        $students->get(),
                        new \DateTime($reply['date']),
                        $reply['text'],
                        (int) $reply['id_reply']
                    );
                }
            }
            
            return $response;
    }
}