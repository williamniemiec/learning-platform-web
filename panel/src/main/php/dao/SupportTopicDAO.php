<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Admin;
use domain\Message;
use domain\SupportTopic;
use domain\SupportTopicCategory;
use domain\Action;
use util\IllegalAccessException;


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
        $response = null;
        
        // Query construction
        $this->withQuery("
            SELECT  *
            FROM    support_topic NATURAL JOIN support_topic_category
            WHERE   id_topic = ?
        ");
            
        // Executes query
        $sql->execute(array($idTopic));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $supportTopic = $sql->fetch();
            $students = new StudentsDAO($this->db);
            
            $response = new SupportTopic(
                (int)$supportTopic['id_topic'],
                $students->get((int)$supportTopic['id_student']),
                $supportTopic['title'],
                new SupportTopicCategory((int)$supportTopic['id_category'], $supportTopic['name']),
                new \DateTime($supportTopic['date']),
                $supportTopic['message'],
                (int)$supportTopic['closed']
            );
        }
        
        return $response;
    }

    private function validateTopicId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Topic id cannot be empty or ".
                                                "less than or equal to zero");
        }
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
        $response = array();
        
        $query = "
            SELECT  *
            FROM    support_topic NATURAL JOIN support_topic_category
            WHERE   closed = 0
        ";
        
        // Limits the results (if a limit was given)
        if ($limit > 0) {
            if ($offset > 0)
                $query .= " LIMIT ".$offset.",".$limit;
            else
                $query .= " LIMIT ".$limit;
        }
        
        $sql = $this->db->query($query);
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $topic) {
                $studentsDAO = new StudentsDAO($this->db);
                
                $response[] = new SupportTopic(
                    (int)$topic['id_topic'],
                    $studentsDAO->get((int)$topic['id_student']),
                    $topic['title'],
                    new SupportTopicCategory((int)$topic['id_category'], $topic['name']),
                    new \DateTime($topic['date']),
                    $topic['message'],
                    (int)$topic['closed']
                );
            }
        }
        
        return $response;
    }
    
    /**
     * Gets all support topic categories.
     *
     * @return      SupportTopicCategory[] Support topic categories or empty
     * array if there are no registered categories
     */
    public function getCategories() : array
    {
        $response = array();
        
        // Query construction
        $sql = $this->db->query("
            SELECT  *
            FROM    support_topic_category
        ");
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $category) {
                $response[] = new SupportTopicCategory(
                    (int)$category['id_category'],
                    $category['name']
                );
            }
        }
        
        return $response;
    }
    
    /**
     * Searches for a topic with a given name that is open.
     *
     * @param       string $name [Optional] Support topic title to be searched
     * @param       int $id_category [Optional] Searches for topics that belongs to a
     * category
     *
     * @return      SupportTopic[] Support topics that match with the provided
     * name or empty array if there are no matches
     */
    public function search(string $name = '', int $id_category = 0) : array
    {
        $response = array();
        
        // Query construction
        $query = "
            SELECT  *
            FROM    support_topic NATURAL JOIN support_topic_category
            WHERE   title LIKE ? AND closed = 0
        ";
        
        if ($id_category > 0)
            $query .= " AND id_category = ".$id_category;
            
        $this->withQuery($query);
        
        // Executes query
        $sql->execute(array($name.'%'));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $supportTopic) {
                $students = new StudentsDAO($this->db);
                
                $response[] = new SupportTopic(
                    (int)$supportTopic['id_topic'],
                    $students->get((int)$supportTopic['id_student']),
                    $supportTopic['title'],
                    new SupportTopicCategory((int)$supportTopic['id_category'], $supportTopic['name']),
                    new \DateTime($supportTopic['date']),
                    $supportTopic['message'],
                    (int)$supportTopic['closed']
                );
            }
        }
        
        return $response;
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
        $response = false;
            
        // Query construction
        $this->withQuery("
            UPDATE  support_topic
            SET     closed = 1
            WHERE   id_topic = ?
        ");
        
        // Executes query
        $sql->execute(array($idTopic));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->closeTopic($idTopic);
            $adminsDAO->newAction($action);
        }
        
        return $response;
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
        $response = false;
            
        // Query construction
        $this->withQuery("
            UPDATE  support_topic
            SET     closed = 0
            WHERE   id_topic = ?
        ");
        
        // Executes query
        $sql->execute(array($id_topic));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->openTopic($id_topic);
            $adminsDAO->newAction($action);
        }
        
        return $response;
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
            
        $response = false;
            
        // Query construction
        $this->withQuery("
            INSERT INTO support_topic_replies
            (id_topic, id_user, user_type, date, text)
            VALUES (?, ?, 1, NOW(), ?)
        ");
        
        // Executes query
        $sql->execute(array($idTopic, $this->admin->getId(), $text));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->answerTopic($idTopic);
            $adminsDAO->newAction($action);
        }
        
        return $response;
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
        $response = array();
        
        // Query construction
        $this->withQuery("
            SELECT  *
            FROM    support_topic_replies
            WHERE   id_topic = ?
        ");
        
        // Executes query
        $sql->execute(array($idTopic));
        
        // Parses result
        if ($sql && $sql->rowCount() > 0) {
            $replies = $sql->fetchAll();
            
            foreach ($replies as $reply) {
                if ($reply['user_type'] == 0) {
                    $students = new StudentsDAO($this->db);
                    $user = $students->get((int)$reply['id_user']);
                }
                else {
                    $admins = new AdminsDAO($this->db);
                    $user = $admins->get((int)$reply['id_user']);
                }
                
                $response[] = new Message(
                    $user, 
                    new \DateTime($reply['date']), 
                    $reply['text'],
                    $reply['id_reply']
                );
            }
        }
            
        return $response;
    }
    
    /**
     * Gets total of support topics opened.
     *
     * @return      int Total of bundles
     */
    public function count() : int
    {
        return (int)$this->db->query("
            SELECT  COUNT(*) AS total
            FROM    support_topic
            WHERE   closed = 0
        ")->fetch()['total'];
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
        $sql->execute(array($id_topic));
        
        $sql = $this->db->query("SELECT @isOpen AS is_open");
        
        return $sql->fetch()['is_open'] == 1;
    }
}