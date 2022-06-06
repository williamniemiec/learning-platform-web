<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\SupportTopic;
use domain\Message;
use domain\SupportTopicCategory;
use domain\util\IllegalAccessException;


/**
 * Responsible for managing 'support_topic' table.
 */
class SupportTopicDAO extends DAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $idStudent;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'support_topic' table manager.
     *
     * @param       Database $db Database
     * @param       int idStudent Student id
     * 
     * @throws      \InvalidArgumentException If student id is empty or less 
     * than or equal to zero
     */
    public function __construct(Database $db, int $idStudent)
    {
        parent::__construct($db);
        $this->validateStudentId($idStudent);
        $this->idStudent = $idStudent;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    private function validateStudentId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Student id cannot be empty ".
                                                "or less than or equal to zero");
        }
    }

    /**
     * Gets information about a support topic.
     *
     * @param      int $idTopic Topic id
     *
     * @return      SupportTopic Support topic with the given id or null if there
     * is no topic with the provided id
     * 
     * @throws      \InvalidArgumentException If topic id is empty or less than
     * or equal to zero
     */
    public function get(int $idTopic) : ?SupportTopic
    {
        $this->validateTopicId($idTopic);
        $this->withQuery("
            SELECT  *
            FROM    support_topic NATURAL JOIN support_topic_category
            WHERE   id_topic = ?
        ");
        $this->runQueryWithArguments($idTopic);
        
        return $this->parseGetResponseQuery();
    }

    private function validateTopicId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Topic id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }

    private function parseGetResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return null;
        }

        $topic = $this->getResponseQuery();
        $students = new StudentsDAO($this->db, (int) $topic['id_student']);
        
        return new SupportTopic(
            (int) $topic['id_topic'],
            $students->get(),
            $topic['title'],
            new SupportTopicCategory((int) $topic['id_category'], $topic['name']),
            new \DateTime($topic['date']), 
            $topic['message'], 
            (int) $topic['closed']
        );
    }
    
    /**
     * Gets all topics created by the student.
     * 
     * @param       int $limit [Optional] Maximum topics returned
     * @param       int $offset [Optional] Ignores first results from the return
     * 
     * @return      SupportTopic[] Support topics created by the student or 
     * empty array if he has not created a topic yet
     */
    public function getAll(int $limit = -1, int $offset = -1) : array
    {
        $this->withQuery($this->buildGetAllQuery($limit, $offset));
        $this->runQueryWithoutArguments();
        
        return $this->parseGetAllResponseQuery();
    }

    private function buildGetAllQuery($limit, $offset)
    {
        $query = "
            SELECT  *
            FROM    support_topic NATURAL JOIN support_topic_category
            WHERE   id_student = ".$this->idStudent;

        if ($limit > 0) {
            if ($offset > 0) {
                $query .= " LIMIT ".$offset.",".$limit;
            }
            else {
                $query .= " LIMIT ".$limit;
            }
        }
        
        return $query;
    }

    private function parseGetAllResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $supportTopics = array();

        foreach ($this->getAllResponseQuery() as $topic) {
            $students_dao = new StudentsDAO($this->db, (int) $topic['id_student']);
            
            $supportTopics[] = new SupportTopic(
                (int) $topic['id_topic'],
                $students_dao->get(),
                $topic['title'],
                new SupportTopicCategory((int) $topic['id_category'], $topic['name']),
                new \DateTime($topic['date']),
                $topic['message'],
                (int) $topic['closed']
            );
        }

        return $supportTopics;
    }
    
    /**
     * Creates a new support topic.
     * 
     * @param       int $idCategory Category id that the support topic belongs
     * @param       int $idStudent Student id that created the support topic
     * @param       string $title Support topic's title
     * @param       string $message Support topic's content
     * 
     * @return      bool If support topic has been successfully created
     * 
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function new(int $idCategory, int $idStudent, string $title, 
        string $message) : bool
    {
        $this->validateCategoryId($idCategory);
        $this->validateStudentId($idStudent);
        $this->validateTitle($title);
        $this->validateMessage($message);
        $this->withQuery("
            INSERT INTO support_topic
            (id_category, id_student, title, date, message)
            VALUES (?, ?, ?, NOW(), ?)
        ");
        $this->runQueryWithArguments($idCategory, $idStudent, $title, $message);
        
        return $this->hasResponseQuery();
    }

    private function validateCategoryId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Category id cannot be empty ".
                                                "or less than or equal to zero");
        }
    }

    private function validateTitle($title)
    {
        if (empty($title)) {
            throw new \InvalidArgumentException("Title cannot be empty");
        }
    }

    private function validateMessage($message)
    {
        if (empty($message)) {
            throw new \InvalidArgumentException("Message cannot be empty");
        }
    }
    
    /**
     * Removes a support topic
     * 
     * @param       int $idTopic Support topic id to be deleted
     * @param       int $idStudent Student id logged in. It is necessary to 
     * prevent a student from deleting a topic that is not his 
     * 
     * @return      bool If support topic has been successfully removed
     * 
     * @throws      \InvalidArgumentException If topic id or student id is empty
     * or less than or equal to zero
     */
    public function delete(int $idTopic, int $idStudent) : bool
    {
        $this->validateTopicId($idTopic);
        $this->validateStudentId($idStudent);
        $this->withQuery("
            DELETE FROM support_topic
            WHERE id_topic = ? AND id_student = ?
        ");
        $this->runQueryWithArguments($idTopic, $idStudent);
        
        return $this->hasResponseQuery();
    }
    
    /**
     * Closes a support topic.
     * 
     * @param       int $idTopic Support topic id to be closed
     * 
     * @return      bool If support topic has been successfully closed
     * 
     * @throws      \InvalidArgumentException If topic id is empty or less than
     * or equal to zero
     */
    public function close(int $idTopic) : bool
    {
        $this->validateTopicId($idTopic);
        $this->withQuery("
            UPDATE  support_topic
            SET     closed = 1
            WHERE   id_topic = ? AND id_student = ?
        ");
        $this->runQueryWithArguments($idTopic, $this->idStudent);
        
        return $this->hasResponseQuery();
    }

    /**
     * Opens a support topic.
     *
     * @param       int $idTopic Support topic id to be opened
     *
     * @return      bool If support topic has been successfully closed
     * 
     * @throws      \InvalidArgumentException If topic id is empty or less than
     * or equal to zero
     */
    public function open(int $idTopic) : bool
    {
        $this->validateTopicId($idTopic);
        $this->withQuery("
            UPDATE  support_topic
            SET     closed = 0
            WHERE   id_topic = ? AND id_student = ?
        ");
        $this->runQueryWithArguments($idTopic, $this->idStudent);
        
        return $this->hasResponseQuery();
    }
    
    /**
     * Replies a support topic.
     * 
     * @param       int idTopic Support topic id to be replied
     * @param       int $idStudent Student id that will reply the support topic
     * @param       string $message Reply's content
     * 
     * @return      bool If the reply has been successfully added
     * 
     * @throws      \InvalidArgumentException If any argument is invalid
     * @throws      IllegalAccessException If support topic is closed
     */
    public function newReply(int $idTopic, int $idStudent, string $message) : bool
    {
        $this->validateTopicId($idTopic);
        $this->validateStudentId($idStudent);
        $this->validateMessage($message);
        $this->validateTopicIsOpen($idTopic);
        $this->withQuery("
            INSERT INTO support_topic_replies
            (id_topic, id_user, user_type, date, text)
            VALUES (?, ?, 0, NOW(), ?)
        ");
        $this->runQueryWithArguments($idTopic, $idStudent, $message);
        
        return $this->hasResponseQuery();
    }

    private function validateTopicIsOpen($idTopic)
    {
        if (!$this->isOpen($idTopic)) {
            throw new IllegalAccessException("Topic is closed");
        }
    }

    /**
     * Gets all replies from a support topic.
     * 
     * @param       int idTopic Support topic id
     * 
     * @return      Message[] Support topic replies or empty array if there are
     * no replies
     * 
     * @throws      \InvalidArgumentException If topic id is empty or less than
     * or equal to zero
     */
    public function getReplies(int $idTopic) : array
    {
        $this->validateTopicId($idTopic);
        $this->withQuery("
            SELECT  *
            FROM    support_topic_replies
            WHERE   id_topic = ?
        ");
        $this->runQueryWithArguments($idTopic);
            
        return $this->parseGetRepliesResponseQuery();
    }

    private function parseGetRepliesResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $replies = array();
        
        foreach ($this->getAllResponseQuery() as $reply) {
            if ($reply['user_type'] == 0) {
                $studentsDao = new StudentsDAO($this->db, (int) $reply['id_user']);
                $user = $studentsDao->get();
            }
            else {
                $adminsDao = new AdminsDAO($this->db);
                $user = $adminsDao->get((int) $reply['id_user']);
            }
            
            $replies[] = new Message(
                $user, 
                new \DateTime($reply['date']), 
                $reply['text'],
                (int) $reply['id_reply']
            );
        }
        
        return $replies;
    }
    
    /**
     * Gets all answered support topics from a user with a specific category.
     * 
     * @param       string $name [Optional] Support topic title to be searched
     * @param       int idCategory [Optional] Category id
     * 
     * @return      SupportTopic[] Support topics that have already been 
     * answered and that belongs to the category with the given id or empty
     * array if there are no matches
     */
    public function getAllAnsweredByCategory(string $name = '', int $idCategory = 0) : array
    {
        $this->withQuery($this->buildGetAllAnsweredByCategoryQuery($idCategory));
        $this->runQueryWithArguments($this->idStudent, $name."%");
        
        return $this->parseGetFilteredSupportTopicResponseQuery();
    }

    private function buildGetAllAnsweredByCategoryQuery($idCategory)
    {
        $query = "
            SELECT  *
            FROM    support_topic NATURAL JOIN support_topic_category
            WHERE   id_student = ? AND
                    title LIKE ? AND
                    id_topic IN (SELECT id_topic
                                 FROM   support_topic_replies)
        ";
        
        if ($idCategory > 0) {
            $query .= " AND id_category = ".$idCategory;
        }
        
        return $query;
    }

    private function parseGetFilteredSupportTopicResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }
        
        $supportTopics = array();

        foreach ($this->getAllResponseQuery() as $supportTopic) {            
            $students = new StudentsDAO(
                $this->db, 
                (int) $supportTopic['id_student']
            );
            $category = new SupportTopicCategory(
                (int) $supportTopic['id_category'], 
                $supportTopic['name']
            );
            
            $supportTopics[] = new SupportTopic(
                (int) $supportTopic['id_topic'],
                $students->get(),
                $supportTopic['title'],
                $category,
                new \DateTime($supportTopic['date']),
                $supportTopic['message'],
                (int) $supportTopic['closed']
            );
        }

        return $supportTopics;
    }
    
    /**
     * Searches for a topic with a given name.
     * 
     * @param       string $name [Optional] Support topic title to be searched
     * @param       int $idCategory [Optional] Searches for topics that belongs to a
     * category
     * 
     * @return      SupportTopic[] Support topics that match with the provided
     * name or empty array if there are no matches
     */
    public function search(string $name = '', int $idCategory = 0) : array
    {
        $this->withQuery($this->buildSearchQuery($idCategory));
        $this->runQueryWithArguments($this->idStudent, $name."%");

        return $this->parseGetFilteredSupportTopicResponseQuery();
    }

    private function buildSearchQuery($idCategory)
    {
        $query = "
            SELECT  *
            FROM    support_topic NATURAL JOIN support_topic_category
            WHERE   id_student = ? AND title LIKE ?
        ";
        
        if ($idCategory > 0) {
            $query .= " AND id_category = ".$idCategory;
        }
        
        return $query;
    }
    
    /**
     * Gets all support topic categories.
     * 
     * @return      SupportTopicCategory[] Support topic categories or empty 
     * array if there are no registered categories
     */
    public function getCategories() : array
    {
        $this->withQuery("
            SELECT  *
            FROM    support_topic_category
        ");
        $this->runQueryWithoutArguments();
        
        return $this->parseGetCategoriesResponseQuery();
    }

    private function parseGetCategoriesResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $categories = array();

        foreach ($this->getAllResponseQuery() as $category) {
            $categories[] = new SupportTopicCategory(
                (int) $category['id_category'],
                $category['name']
            );
        }

        return $categories;
    }
    
    /**
     * Gets total of support topics.
     *
     * @return      int Total of bundles
     */
    public function count() : int
    {
        $this->withQuery("
            SELECT  COUNT(*) AS total
            FROM    support_topic
        ");
        $this->runQueryWithoutArguments();

        return ((int) $this->getResponseQuery()['total']);
    }
    
    /**
     * Checks whether a support topic is open.
     * 
     * @param       int idTopic Support topic id
     * 
     * @return      bool If support topic is open
     */
    private function isOpen(int $idTopic) : bool
    {
        $this->withQuery("CALL sp_support_topic_is_open(?, @isOpen)");
        $this->runQueryWithArguments($idTopic);
        $this->withQuery("SELECT @isOpen AS is_open");
        $this->runQueryWithoutArguments();

        return ($this->getResponseQuery()['is_open'] == 1);
    }
}