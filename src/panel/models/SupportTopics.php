<?php
namespace models;

use core\Model;
use models\obj\SupportTopic;
use models\obj\Message;



/**
 * Responsible for managing support_topic table.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class SupportTopics extends Model
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates support_topic table manager.
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
     * Gets information about a support topic.
     *
     * @param      
     *
     * @return      array questions from this class
     */
    public function get($id_topic)
    {
        //         if (empty($id_module) || $id_module <= 0) { return array(); }
        
        $response = NULL;
        
        $sql = $this->db->prepare("
            SELECT  *
            FROM    support_topic NATURAL JOIN support_category
            WHERE   id_topic = ?
        ");
        
        $sql->execute(array($id_topic));
        
        if ($sql->rowCount() > 0) {
            $supportTopic = $sql->fetch(\PDO::FETCH_ASSOC);
            $students = new Students();
            
            $response = new SupportTopics(
                $supportTopic['id_topic'],
                $students->get(supportTopic['id_student']), 
                $supportTopic['title'], 
                $supportTopic['name'], 
                $supportTopic['date'], 
                $supportTopic['message'], 
                $supportTopic['closed']
            );
        }
        
        return $response;
    }
    
    public function close($id_topic)
    {
        $sql = $this->db->prepare("
            UPDATE  support_topic
            SET     closed = 1
            WHERE   id_topic = ?
        ");
        
        $sql->execute(array($id_topic));
        
        return $sql->rowCount() > 0;
    }
    
    public function open($id_topic)
    {
        $sql = $this->db->prepare("
            UPDATE  support_topic
            SET     closed = 0
            WHERE   id_topic = ?
        ");
        
        $sql->execute(array($id_topic));
        
        return $sql->rowCount() > 0;
    }
    
    public function newReply($id_topic, $id_admin, $date, $text)
    {
        $sql = $this->db->prepare("
            INSERT INTO support_topic_replies
            (id_topic, id_user, user_type, date, text)
            VALUES (?, ?, 1, NOW(), ?)
        ");
        
        $sql->execute(array($id_topic, $id_admin, $date, $text));
        
        return $sql->rowCount() > 0;
    }
    
    public function getReplies($id_topic)
    {
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT  *
            FROM    support_topic_replies
            WHERE   id_topic = ?
        ");
        
        $sql->execute(array($id_topic));
        
        if ($sql->rowCount() > 0) {
            $replies = $sql->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($replies as $reply) {
                if ($reply['user_type'] == 0) {
                    $students = new Students();
                    $user = $students->get($reply['id_user']);
                }
                else {
                    $admins = new Admins();
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