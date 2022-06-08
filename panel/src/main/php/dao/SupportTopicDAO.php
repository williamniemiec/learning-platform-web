<?php
declare (strict_types=1);

namespace panel\dao;


use panel\repositories\Database;
use panel\domain\Admin;
use panel\domain\Message;
use panel\domain\SupportTopic;
use panel\domain\SupportTopicCategory;
use panel\domain\Action;
use panel\util\IllegalAccessException;


/**
 * Responsible for managing 'support_topic' table.
 */
class SupportTopicDAO extends DAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $admin;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'support_topic' table manager.
     *
     * @param       Database $db Database
     * @param       Admin $_admin Admin logged in
     * 
     * @throws      \InvalidArgumentException If admin is empty
     */
    public function __construct(Database $db, Admin $admin)
    {
        parent::__construct($db);
        $this->validateAdmin($admin);
        $this->admin = $admin;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    private function validateAdmin($admin)
    {
        if (empty($admin)) {
            throw new \InvalidArgumentException("Admin cannot be empty ");
        }
    }

    /**
     * Gets information about a support topic.
     *
     * @param       int $idTopic Support topic id
     *
     * @return      SupportTopic Support topic with the given id or null if there
     * is no topic with the provided id
     * 
     * @throws      IllegalAccessException If admin does not have authorization
     * to read topics
     * @throws      \InvalidArgumentException If topic id is empty, less than 
     * or equal to zero
     */
    public function get(int $idTopic) : ?SupportTopic
    {            
        $this->validateTopicId($idTopic);
        $this->validateAuthorization(0, 2);
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
        $studentsDao = new StudentsDAO($this->db);
        
        return new SupportTopic(
            (int) $topic['id_topic'],
            $studentsDao->get((int)$topic['id_student']),
            $topic['title'],
            new SupportTopicCategory((int) $topic['id_category'], $topic['name']),
            new \DateTime($topic['date']),
            $topic['message'],
            (int) $topic['closed']
        );
    }
    
    /**
     * Gets all topics opened.
     *
     * @param       int $limit [Optional] Maximum topics returned
     * @param       int $offset [Optional] Ignores first results from the return
     *
     * @return      SupportTopic[] Support topics or empty array if there are no
     * topics opened
     */
    public function getAllOpened(int $limit = -1, int $offset = -1) : array
    {
        $this->withQuery($this->buildGetAllOpenedQuery($limit, $offset));
        $this->runQueryWithoutArguments();
        
        return $this->parseGetAllResponseQuery();
    }

    private function buildGetAllOpenedQuery($limit, $offset)
    {
        $query = "
            SELECT  *
            FROM    support_topic NATURAL JOIN support_topic_category
            WHERE   closed = 0
        ";

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
            $studentsDao = new StudentsDAO($this->db);
            
            $supportTopics[] = new SupportTopic(
                (int) $topic['id_topic'],
                $studentsDao->get((int) $topic['id_student']),
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
        
        return $this->parseGetAllCategoriesResponseQuery();
    }

    private function parseGetAllCategoriesResponseQuery()
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
     * Searches for a topic with a given name that is open.
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
        $this->runQueryWithArguments($name.'%');
        
        return $this->parseGetAllResponseQuery();
    }

    private function buildSearchQuery($idCategory)
    {
        $query = "
            SELECT  *
            FROM    support_topic NATURAL JOIN support_topic_category
            WHERE   title LIKE ? AND closed = 0
        ";
        
        if ($idCategory > 0) {
            $query .= " AND id_category = ".$idCategory;
        }
        
        return $query;
    }
    
    /**
     * Closes a support topic.
     * 
     * @param       int $idTopic Support topic id
     * 
     * @return      bool If support topic has been successfully closed
     * 
     * @throws      IllegalAccessException If admin does not have authorization
     * to close topics
     * @throws      \InvalidArgumentException If topic id is empty, less than
     * or equal to zero
     */
    public function close(int $idTopic) : bool
    {
        $this->validateTopicId($idTopic);
        $this->validateAuthorization(0, 2);
        $this->withQuery("
            UPDATE  support_topic
            SET     closed = 1
            WHERE   id_topic = ?
        ");
        $this->runQueryWithArguments($idTopic);
        
        return $this->parseCloseResponseQuery($idTopic);
    }

    private function parseCloseResponseQuery($idTopic)
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->closeTopic($idTopic);
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);

        return true;
    }
    
    /**
     * Opens a support topic.
     *
     * @param       int $idTopic Support topic id
     *
     * @return      bool If support topic has been successfully closed
     * 
     * @throws      IllegalAccessException If admin does not have authorization
     * to open topics
     * @throws      \InvalidArgumentException If topic id is empty, less than 
     * or equal to zero
     */
    public function open(int $idTopic) : bool
    {
        $this->validateTopicId($idTopic);
        $this->validateAuthorization(0, 2);
        $this->withQuery("
            UPDATE  support_topic
            SET     closed = 0
            WHERE   id_topic = ?
        ");
        $this->runQueryWithArguments($idTopic);
        
        return $this->parseCloseResponseQuery($idTopic);
    }

    private function parseOpenResponseQuery($idTopic)
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->openTopic($idTopic);
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);

        return true;
    }
    
    /**
     * Replies a support topic.
     * 
     * @param       int $idTopic Support topic id
     * @param       string $text Reply's content
     *
     * @return      bool If the reply has been successfully added
     *
     * @throws      IllegalAccessException If admin does not have authorization
     * to reply topics or if topic is closed
     * @throws      \InvalidArgumentException If text is empty or if topic id 
     * is empty, less than or equal to zero
     */
    public function newReply(int $idTopic, string $text) : bool
    {
        $this->validateAuthorization(0, 2);
        $this->validateTopicId($idTopic);
        $this->validateTopicIsOpen($idTopic);
        $this->validateText($text);
        $this->withQuery("
            INSERT INTO support_topic_replies
            (id_topic, id_user, user_type, date, text)
            VALUES (?, ?, 1, NOW(), ?)
        ");
        $this->runQueryWithArguments($idTopic, $this->admin->getId(), $text);
        
        return $this->parseNewReplyResponseQuery($idTopic);
    }

    private function parseNewReplyResponseQuery($idTopic)
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->answerTopic($idTopic);
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);

        return true;
    }

    private function validateText($text)
    {
        if (empty($text)) {
            throw new \InvalidArgumentException("Text cannot be empty");
        }
    }

    private function validateTopicIsOpen($topicId)
    {
        if (!$this->isOpen($topicId)) {
            throw new IllegalAccessException("Topic is closed");
        }
    }
    
    /**
     * Gets all replies from a support topic.
     *
     * @param       int $idTopic Support topic id
     *
     * @return      Message[] Support topic replies or empty array if there are
     * no replies
     * 
     * @throws      IllegalAccessException If admin does not have authorization
     * to read topics
     * @throws      \InvalidArgumentException If text is empty or if topic id 
     * is empty, less than or equal to zero
     */
    public function getReplies(int $idTopic) : array
    {
        $this->validateTopicId($idTopic);
        $this->validateAuthorization(0, 2);
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
                $studentsDao = new StudentsDAO($this->db);
                $user = $studentsDao->get((int)$reply['id_user']);
            }
            else {
                $admins = new AdminsDAO($this->db);
                $user = $admins->get((int)$reply['id_user']);
            }
            
            $replies[] = new Message(
                $user, 
                new \DateTime($reply['date']), 
                $reply['text'],
                $reply['id_reply']
            );
        }

        return $replies;
    }
    
    /**
     * Gets total of support topics opened.
     *
     * @return      int Total of bundles
     */
    public function count() : int
    {
        $this->withQuery("
            SELECT  COUNT(*) AS total
            FROM    support_topic
            WHERE   closed = 0
        ");
        $this->runQueryWithoutArguments();

        return ((int) $this->getResponseQuery()['total']);
    }
    
    /**
     * Checks whether a support topic is open.
     *
     * @param       int $id_topic Support topic id
     *
     * @return      bool If support topic is open
     */
    private function isOpen(int $id_topic) : bool
    {
        $this->withQuery("CALL sp_support_topic_is_open(?, @isOpen)");
        $this->runQueryWithArguments($id_topic);
        $this->withQuery("SELECT @isOpen AS is_open");
        $this->runQueryWithoutArguments();
        
        return ($this->getResponseQuery()['is_open'] == 1);
    }
}