<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Message;
use models\SupportTopic;
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
    private $id_topic;
    private $id_admin;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'support_topic' table manager.
     *
     * @param       Database $db Database
     * @param       int $id_topic Topic id
     * @param       int $id_admin Admin id logged in
     * 
     * @throws      \InvalidArgumentException If topic id is empty, less than 
     * or equal to zero
     */
    public function __construct(Database $db, int $id_topic, int $id_admin)
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($id_admin) || $id_admin <= 0)
            throw new \InvalidArgumentException("Admin id cannot be empty ".
                "or less than or equal to zero");
            
        $this->id_topic = $id_topic;
        $this->id_admin = $id_admin;
        $this->db = $db->getConnection();
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets information about a support topic.
     *
     * @return      SupportTopic Support topic with the given id or null if there
     * is no topic with the provided id
     */
    public function get() : ?SupportTopic
    {            
        if ($this->getAuthorization()->getLevel() != 0 &&
            $this->getAuthorization()->getLevel() != 2)
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
        $sql->execute(array($this->id_topic));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $supportTopic = $sql->fetch();
            $students = new StudentsDAO();
            
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
     * Closes a support topic.
     * 
     * @return      bool If support topic has been successfully closed
     */
    public function close() : bool
    {
        if ($this->getAuthorization()->getLevel() != 0 &&
            $this->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  support_topic
            SET     closed = 1
            WHERE   id_topic = ?
        ");
        
        // Executes query
        $sql->execute(array($this->id_topic));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Opens a support topic.
     *
     * @return      bool If support topic has been successfully closed
     */
    public function open() : bool
    {
        if ($this->getAuthorization()->getLevel() != 0 &&
            $this->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  support_topic
            SET     closed = 0
            WHERE   id_topic = ?
        ");
        
        // Executes query
        $sql->execute(array($this->id_topic));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Replies a support topic.
     *
     * @param       int $id_admin Admin id that will reply the support topic
     * @param       string $text Reply's content
     *
     * @return      bool If the reply has been successfully added
     *
     * @throws      \InvalidArgumentException If admin id is empty, less than 
     * or equal to zero or if text is empty
     */
    public function newReply(int $id_admin, string $text) : bool
    {
        if ($this->getAuthorization()->getLevel() != 0 &&
            $this->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_admin) || $id_admin <= 0)
            throw new \InvalidArgumentException("Admin id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($text))
            throw new \InvalidArgumentException("Text cannot be empty");
                
        if (!$this->isOpen($this->id_topic))
            throw new IllegalAccessException("Topic is closed");
            
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO support_topic_replies
            (id_topic, id_user, user_type, date, text)
            VALUES (?, ?, 1, NOW(), ?)
        ");
        
        // Executes query
        $sql->execute(array($this->id_topic, $id_admin, $text));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Gets all replies from a support topic.
     *
     * @return      Message[] Support topic replies or empty array if there are
     * no replies
     */
    public function getReplies()
    {
        if ($this->getAuthorization()->getLevel() != 0 &&
            $this->getAuthorization()->getLevel() != 2)
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
        $sql->execute(array($this->id_topic));
        
        // Parses result
        if ($sql && $sql->rowCount() > 0) {
            $replies = $sql->fetchAll();
            
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