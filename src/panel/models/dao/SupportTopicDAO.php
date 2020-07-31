<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\SupportTopic;
use models\Message;



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
     * @param       mixed $db Database
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
     * @throws      \InvalidArgumentException If topic id is invalid
     */
    public function get(int $id_topic) : array
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Invalid topic id");
            
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
            $students = new StudentsDAO();
            
            $response = new SupportTopicDAO(
                $supportTopic['id_topic'],
                $students->get($supportTopic['id_student']),
                $supportTopic['title'],
                $supportTopic['name'],
                $supportTopic['date'],
                $supportTopic['message'],
                $supportTopic['closed']
            );
        }
        
        return $response;
    }
    
    /**
     * Closes a support topic.
     * 
     * @param       int $id_topic Support topic id to be closed
     * 
     * @return      bool If support topic has been successfully closed
     * 
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function close(int $id_topic) : bool
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Invalid topic id");
        
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  support_topic
            SET     closed = 1
            WHERE   id_topic = ?
        ");
        
        // Executes query
        $sql->execute(array($id_topic));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Opens a support topic.
     *
     * @param       int $id_topic Support topic id to be opened
     *
     * @return      bool If support topic has been successfully closed
     *
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function open(int $id_topic) : bool
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Invalid topic id");
        
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  support_topic
            SET     closed = 0
            WHERE   id_topic = ?
        ");
        
        // Executes query
        $sql->execute(array($id_topic));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Replies a support topic.
     *
     * @param       int $id_topic Support topic id to be replied
     * @param       int $id_admin Admin id that will reply the support topic
     * @param       string $text Reply's content
     *
     * @return      bool If the reply was sucessfully added
     *
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function newReply(int $id_topic, int $id_admin, string $text) : bool
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Invalid topic id");
            
        if (empty($id_admin) || $id_admin <= 0)
            throw new \InvalidArgumentException("Invalid admin id");
            
        if (empty($text))
            throw new \InvalidArgumentException("Text cannot be empty");
                
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO support_topic_replies
            (id_topic, id_user, user_type, date, text)
            VALUES (?, ?, 1, NOW(), ?)
        ");
        
        // Executes query
        $sql->execute(array($id_topic, $id_admin, $text));
        
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
     * @throws      \InvalidArgumentException If topic id is invalid
     */
    public function getReplies($id_topic)
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Invalid topic id");
        
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
                    $reply['date'], 
                    $reply['text']
                );
            }
        }
            
        return $response;
    }
}