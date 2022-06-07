<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Admin;
use models\Message;
use models\SupportTopic;
use models\util\IllegalAccessException;
use models\SupportTopicCategory;
use models\Action;


/**
 * Responsible for managing 'support_topic' table.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class SupportTopicDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $db;
    private $id_topic;
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
        if (empty($admin))
            throw new \InvalidArgumentException("Admin cannot be empty ".
                "or less than or equal to zero");

        $this->admin = $admin;
        $this->db = $db->getConnection();
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets information about a support topic.
     *
     * @param       int $id_topic Support topic id
     *
     * @return      SupportTopic Support topic with the given id or null if there
     * is no topic with the provided id
     * 
     * @throws      IllegalAccessException If admin does not have authorization
     * to read topics
     * @throws      \InvalidArgumentException If topic id is empty, less than 
     * or equal to zero
     */
    public function get(int $id_topic) : ?SupportTopic
    {            
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be empty ".
                "or less than or equal to zero");
        
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        $response = null;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    support_topic NATURAL JOIN support_topic_category
            WHERE   id_topic = ?
        ");
            
        // Executes query
        $sql->execute(array($id_topic));
        
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
            
        $sql = $this->db->prepare($query);
        
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
     * @param       int $id_topic Support topic id
     * 
     * @return      bool If support topic has been successfully closed
     * 
     * @throws      IllegalAccessException If admin does not have authorization
     * to close topics
     * @throws      \InvalidArgumentException If topic id is empty, less than
     * or equal to zero
     */
    public function close(int $id_topic) : bool
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be empty ".
                "or less than or equal to zero");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        $response = false;
            
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  support_topic
            SET     closed = 1
            WHERE   id_topic = ?
        ");
        
        // Executes query
        $sql->execute(array($id_topic));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->closeTopic($id_topic);
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
    
    /**
     * Opens a support topic.
     *
     * @param       int $id_topic Support topic id
     *
     * @return      bool If support topic has been successfully closed
     * 
     * @throws      IllegalAccessException If admin does not have authorization
     * to open topics
     * @throws      \InvalidArgumentException If topic id is empty, less than 
     * or equal to zero
     */
    public function open(int $id_topic) : bool
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be empty ".
                "or less than or equal to zero");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        $response = false;
            
        // Query construction
        $sql = $this->db->prepare("
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
     * @param       int $id_topic Support topic id
     * @param       string $text Reply's content
     *
     * @return      bool If the reply has been successfully added
     *
     * @throws      IllegalAccessException If admin does not have authorization
     * to reply topics or if topic is closed
     * @throws      \InvalidArgumentException If text is empty or if topic id 
     * is empty, less than or equal to zero
     */
    public function newReply(int $id_topic, string $text) : bool
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be empty ".
                "or less than or equal to zero");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($text))
            throw new \InvalidArgumentException("Text cannot be empty");
                
        if (!$this->isOpen($id_topic))
            throw new IllegalAccessException("Topic is closed");
            
        $response = false;
            
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO support_topic_replies
            (id_topic, id_user, user_type, date, text)
            VALUES (?, ?, 1, NOW(), ?)
        ");
        
        // Executes query
        $sql->execute(array($id_topic, $this->admin->getId(), $text));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->answerTopic($id_topic);
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
    
    /**
     * Gets all replies from a support topic.
     *
     * @param       int $id_topic Support topic id
     *
     * @return      Message[] Support topic replies or empty array if there are
     * no replies
     * 
     * @throws      IllegalAccessException If admin does not have authorization
     * to read topics
     * @throws      \InvalidArgumentException If text is empty or if topic id 
     * is empty, less than or equal to zero
     */
    public function getReplies(int $id_topic) : array
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be empty ".
                "or less than or equal to zero");
            
        if ($this->admin->getAuthorization()->getLevel() != 0 &&
            $this->admin->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        $response = array();
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    support_topic_replies
            WHERE   id_topic = ?
        ");
        
        // Executes query
        $sql->execute(array($id_topic));
        
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
        $sql = $this->db->prepare("CALL sp_support_topic_is_open(?, @isOpen)");
        $sql->execute(array($id_topic));
        
        $sql = $this->db->query("SELECT @isOpen AS is_open");
        
        return $sql->fetch()['is_open'] == 1;
    }
}