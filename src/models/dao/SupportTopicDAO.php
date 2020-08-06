<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\SupportTopic;
use models\Message;
use models\SupportTopicCategory;
use models\util\IllegalAccessException;


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
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'support_topic' table manager.
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
     * Gets information about a support topic.
     *
     * @param      int $id_topic Topic id
     *
     * @return      SupportTopic Support topic with the given id or null if there
     * is no topic with the provided id
     * 
     * @throws      \InvalidArgumentException If topic id is empty or less than
     * or equal to zero
     */
    public function get(int $id_topic) : array
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be empty ".
                "or less than or equal to zero");
        
        $response = NULL;
        
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
            $supportTopic = $sql->fetch(\PDO::FETCH_ASSOC);
            $students = new StudentsDAO($this->db);
            
            $response = new SupportTopicDAO(
                $supportTopic['id_topic'],
                $students->get($supportTopic['id_student']), 
                $supportTopic['title'], 
                $supportTopic['name'], 
                new \DateTime($supportTopic['date']), 
                $supportTopic['message'], 
                $supportTopic['closed']
            );
        }
        
        return $response;
    }
    
    /**
     * Creates a new support topic.
     * 
     * @param       int $id_category Category id that the support topic belongs
     * @param       int $id_student Student id that created the support topic
     * @param       string $title Support topic's title
     * @param       string $message Support topic's content
     * 
     * @return      bool If support topic has been successfully created
     * 
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function new(int $id_category, int $id_student, string $title, 
        string $message) : bool
    {
        if (empty($id_category) || $id_category <= 0)
            throw new \InvalidArgumentException("Category id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($title))
            throw new \InvalidArgumentException("Title cannot be empty");
        
        if (empty($message))
            throw new \InvalidArgumentException("Message cannot be empty");
        
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO support_topic
            (id_category, id_student, title, date, message)
            VALUES (?, ?, NOW(), ?)
        ");
        
        // Executes query
        $sql->execute(array($id_category, $id_student, $title, $message));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Removes a support topic
     * 
     * @param       int $id_topic Support topic id to be deleted
     * @param       int $id_student Student id logged in. It is necessary to 
     * prevent a student from deleting a topic that is not his 
     * 
     * @return      bool If support topic has been successfully removed
     * 
     * @throws      \InvalidArgumentException If topic id or student id is empty
     * or less than or equal to zero
     */
    public function delete(int $id_topic, int $id_student) : bool
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM support_topic
            WHERE id_topic = ? AND id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($id_topic, $id_student));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Closes a support topic.
     * 
     * @param       int $id_topic Support topic id to be closed
     * @param       int $id_student Student id logged in. It is necessary to 
     * prevent a student from closing a topic that is not his 
     * 
     * @return      bool If support topic has been successfully closed
     * 
     * @throws      \InvalidArgumentException If topic id or student id is empty
     * or less than or equal to zero
     */
    public function close(int $id_student, int $id_topic) : bool
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  support_topic
            SET     closed = 1
            WHERE   id_topic = ? AND id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($id_topic, $id_student));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Opens a support topic.
     *
     * @param       int $id_topic Support topic id to be opened
     * @param       int $id_student Student id logged in. It is necessary to 
     * prevent a student from opening a topic that is not his 
     *
     * @return      bool If support topic has been successfully closed
     * 
     * @throws      \InvalidArgumentException If topic id or student id is empty
     * or less than or equal to zero
     */
    public function open(int $id_topic, int $id_student) : bool
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  support_topic
            SET     closed = 0
            WHERE   id_topic = ? AND id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($id_topic, $id_student));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Replies a support topic.
     * 
     * @param       int $id_topic Support topic id to be replied
     * @param       int $id_student Student id that will reply the support topic
     * @param       string $text Reply's content
     * 
     * @return      bool If the reply has been successfully added
     * 
     * @throws      \InvalidArgumentException If any argument is invalid
     * @throws      IllegalAccessException If support topic is closed
     */
    public function newReply(int $id_topic, int $id_student, string $text) : bool
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($text))
            throw new \InvalidArgumentException("Text cannot be empty");
        
        if (!$this->isOpen($id_topic))
            throw new IllegalAccessException("Topic is closed");
            
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO support_topic_replies
            (id_topic, id_user, user_type, date, text)
            VALUES (?, ?, 0, NOW(), ?)
        ");
        
        // Executes query
        $sql->execute(array($id_topic, $id_student, $text));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Gets all replies from a support topic.
     * 
     * @param       int $id_topic Support topic id
     * 
     * @return      Message[] Support topic replies or empty array if there are
     * no replies
     * 
     * @throws      \InvalidArgumentException If topic id is empty or less than
     * or equal to zero
     */
    public function getReplies(int $id_topic) : array
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be empty ".
                "or less than or equal to zero");

        $response = array();
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    support_topic_replies
            WHERE   id_topic = ?
        ");
        
        // Executes query
        $sql->execute(array($id_topic));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $replies = $sql->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($replies as $reply) {
                if ($reply['user_type'] == 0) {
                    $students = new StudentsDAO($this->db);
                    $user = $students->get($reply['id_user']);
                }
                else {
                    $admins = new AdminsDAO($this->db);
                    $user = $admins->get($reply['id_user']);
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
     * Gets all answered support topics from a user with a specific category.
     * 
     * @param       int $id_student Student id
     * @param       int $id_category Category id
     * 
     * @return      SupportTopic[] Support topics that have already been 
     * answered and that belongs to the category with the given id or empty
     * array if there are no matches
     * 
     * @throws      \InvalidArgumentException If topic id or category id is
     * empty or less than or equal to zero
     */
    public function getAllAnsweredByCategory(int $id_student, int $id_category) : array
    {
        if (empty($id_category) || $id_category <= 0)
            throw new \InvalidArgumentException("Category id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        $response = array();
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    support_topic NATURAL JOIN support_category
            WHERE   id_student = ? AND
                    id_category = ? AND
                    id_topic IN (SELECT id_topic
                                 FROM   support_topic_replies)
        ");
        
        // Executes query
        $sql->execute(array());
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $students = new StudentsDAO($this->db);
            
            foreach ($sql->fetchAll(\PDO::FETCH_ASSOC) as $supportTopic) {
                $response = new SupportTopicDAO(
                    $supportTopic['id_topic'],
                    $students->get($supportTopic['id_student']),
                    $supportTopic['title'],
                    $supportTopic['name'],
                    new \DateTime($supportTopic['date']),
                    $supportTopic['message'],
                    $supportTopic['closed']
                );
            }
        }
        
        return $response;
    }
    
    /**
     * Searches for a topic with a given name.
     * 
     * @param       int $id_student Student id
     * @param       string $name Name to be searched
     * 
     * @return      SupportTopic[] Support topics that match with the provided
     * name or empty array if there are no matches
     * 
     * @throws      \InvalidArgumentException If name is empty or student id is
     * empty or less than or equal to zero
     */
    public function search(int $id_student, string $name) : array
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($name))
            throw new \InvalidArgumentException("Name cannot be empty");
            
        $response = array();
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    support_topic NATURAL JOIN support_category
            WHERE   id_student = ? AND title LIKE ?
        ");
        
        // Executes query
        $sql->execute(array($id_student, $name.'%'));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $students = new StudentsDAO($this->db);
            
            foreach ($sql->fetchAll(\PDO::FETCH_ASSOC) as $supportTopic) {
                $response = new SupportTopicDAO(
                    $supportTopic['id_topic'],
                    $students->get($supportTopic['id_student']),
                    $supportTopic['title'],
                    $supportTopic['name'],
                    new \DateTime($supportTopic['date']),
                    $supportTopic['message'],
                    $supportTopic['closed']
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
            FROM    support_category
        ");
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $category) {
                $response[] = new SupportTopicCategory(
                    $category['id_category'],
                    $category['name']
                );
            }
        }
        
        return $response;
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