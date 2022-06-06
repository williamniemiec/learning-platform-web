<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Message;
use domain\Comment;


/**
 * Responsible for managing 'comments' table.
 */
class CommentsDAO extends DAO
{
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
        parent::__construct($db);
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
        $this->validateStudentId($idStudent);
        $this->validateCourseId($idCourse);
        $this->validateModuleId($idModule);
        $this->validateClassOrder($classOrder);
        $this->validateText($text);
        $this->withQuery("
            INSERT INTO comments 
            (id_student, id_course, id_module, class_order, date, text) 
            VALUES (?, ?,  ?, ?, NOW(), ?)
        ");
        $this->runQueryWithArguments(
            $idStudent, 
            $idCourse, 
            $idModule, 
            $classOrder, 
            $text
        );

        return $this->parseNewResponseQuery();
    }

    private function validateStudentId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Student id cannot be empty or".
                                                "less than or equal to zero");
        }
    }

    private function validateCourseId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Course id cannot be empty or".
                                                "less than or equal to zero");
        }
    }

    private function validateModuleId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Module id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }

    private function validateClassOrder($order)
    {
        if (empty($order) || $order <= 0) {
            throw new \InvalidArgumentException("Class order cannot be empty ".
                                                "or less than or equal to zero");
        }
    }

    private function validateText($text)
    {
        if (empty($text)) {
            throw new \InvalidArgumentException("Text cannot be empty ");
        }
    }

    private function parseNewResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return -1;
        }

        return ((int) $this->db->lastInsertId());
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
        $this->validateCommentId($idComment);
        $this->withQuery("
            SELECT  *
            FROM    comments
            WHERE   id_comment = ?
        ");
        $this->runQueryWithArguments($idComment);
        
        return $this->parseGetResponseQuery();
    }

    private function validateCommentId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Comment id cannot be empty or".
                                                "less than or equal to zero");
        }
    }

    private function parseGetResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return null;
        }

        $commentRaw = $this->getResponseQuery();
        $studentsDao = new StudentsDAO($this->db, (int) $commentRaw['id_student']);
        
        return new Comment(
            (int) $commentRaw['id_comment'],
            (int) $commentRaw['id_course'],
            (int) $commentRaw['id_module'],
            (int) $commentRaw['class_order'],
            $studentsDao->get(),
            new \DateTime($commentRaw['date']),
            $commentRaw['text']
        );
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
        $this->validateModuleId($idModule);
        $this->validateClassOrder($classOrder);
        $this->withQuery("
            SELECT  *
            FROM    comments NATURAL JOIN students
            WHERE   id_module = ? AND class_order = ?
        ");
        $this->runQueryWithArguments($idModule, $classOrder);
        
        return $this->parseGetCommentsResponseQuery();
    }

    private function parseGetCommentsResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $comments = array();
        $i = 0;
            
        foreach ($this->getAllResponseQuery() as $comment) {
            $studentsDao = new StudentsDAO($this->db, (int) $comment['id_student']);
            $comments[$i]['comment'] = new Comment(
                (int) $comment['id_comment'],
                (int) $comment['id_course'],
                (int) $comment['id_module'],
                (int) $comment['class_order'],
                $studentsDao->get(), 
                new \DateTime($comment['date']), 
                $comment['text']
            );
            $comments[$i]['replies'] = $this->getReplies((int) $comment['id_comment']);
            $i++;
        }

        return $comments;
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
        $this->validateCommentId($idComment);
        $this->validateStudentId($idStudent);
        $this->validateText($text);
        $this->withQuery("
            INSERT INTO comment_replies 
            (id_comment, id_student, date, text) 
            VALUES (?, ?, NOW(), ?)
        ");
        $this->runQueryWithArguments($idComment, $idStudent, $text);
        
        return $this->parseNewResponseQuery();
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
        $this->validateCommentId($idComment);
        $this->validateStudentId($idStudent);
        $this->withQuery("
            DELETE FROM comments 
            WHERE id_comment = ?
        ");
        $this->runQueryWithArguments($idComment);
        
        return $this->hasResponseQuery();
    }
    
    /**
     * Removes a reply.
     * 
     * @param       int $idReply Reply id to be removed
     * @param       int $idStudent Student id logged in. It is necessary to 
     * prevent a student from deleting a reply that is not his 
     * 
     * @return      bool If reply has been successfully deleted
     * 
     * @throws      \InvalidArgumentException If reply id or student id is 
     * empty or less than or equal to zero
     */
    public function deleteReply(int $idReply, int $idStudent) : bool
    {
        $this->validateReplyId($idReply);
        $this->validateStudentId($idStudent);
        $this->withQuery("
            DELETE FROM comment_replies 
            WHERE id_reply = ? AND id_student = ?
        ");
        $this->runQueryWithArguments($idReply, $idStudent);
        
        return $this->hasResponseQuery();
    }

    private function validateReplyId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Reply id cannot be empty or".
                                                "less than or equal to zero");
        }
    }
    
    /**
     * Gets replies from a comment.
     *
     * @param       int $idComment Comment id
     *
     * @return      Message[] Replies from this comment
     *
     * @throws      \InvalidArgumentException If comment id is empty or less
     * than or equal to zero
     */
    private function getReplies(int $idComment) : array
    {
        $this->validateCommentId($idComment);
        $this->withQuery("
            SELECT  *
            FROM    comment_replies
            WHERE   id_comment = ?
        ");
        $this->runQueryWithArguments($idComment);
        
        return $this->parseGetRepliesResponseQuery();
    }

    private function parseGetRepliesResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $replies = array();
            
        foreach ($this->getAllResponseQuery() as $reply) {
            $studentsDao = new StudentsDAO(
                $this->db, 
                (int) $reply['id_student']
            );
            $replies[] = new Message(
                $studentsDao->get(),
                new \DateTime($reply['date']),
                $reply['text'],
                (int) $reply['id_reply']
            );
        }

        return $replies;
    }
}