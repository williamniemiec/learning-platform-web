<?php
namespace models;

use core\Model;
use models\obj\Notification;


/**
 * Responsible for managing notifications table.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class Notifications extends Model
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $id_student;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates notifications table manager.
     *
     * @apiNote     It will connect to the database when it is instantiated
     */
    public function __construct($id_user)
    {
        parent::__construct();
        $this->id_student = $id_user;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    public function getNotifications($limit = -1)
    {
        $response = array();
        
        $query = "
            SELECT  *,
            CASE
               WHEN type = 0 THEN (SELECT   text 
                                   FROM     comments 
                                   WHERE    id_comment = id_reference)
               ELSE (SELECT message 
                     FROM   support_topic 
                     WHERE  id_topic = id_reference)
            END AS 'text_ref'
            FROM    notifications
            WHERE   id_student = ".$this->id_student."
        ";
        
        if ($limit > 0)
            $query .= " LIMIT $limit";
        
        $sql = $this->db->query($query);
        
        if ($sql->rowCount() > 0) {
            $notifications = $sql->fetchAll();
            
            foreach ($notifications as $notification) {
                $response[] = new Notification(
                    $notification['id_student'], 
                    $notification['date'],
                    $notification['ref_text'],
                    $notification['type'],
                    $notification['message'],
                    $notification['read']
                );
            }
        }
        
        return $response;
    }

    public function countUnreadNotification($limit = -1)
    {
        $query = "
            SELECT  COUNT(*) AS total_unread
            FROM    notifications
            WHERE   `read` = 0
        ";

        if ($limit > 0) {
            $query .= " LIMIT ".$limit;
        }

        return $this->db->query($query)->fetch()['total_unread'];
    }
    
    public function delete($id_student, $date)
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Invalid id_student");
        
        if (empty($date))
            throw new \InvalidArgumentException("Invalid date");
        
        $sql = $this->db->prepare("
            DELETE FROM notifications
            WHERE id_student = ? AND date = ?
        ");
        
        $sql->execute(array($id_student, $date));
        
        return $sql->rowCount() > 0;
    }
    
    public function new($id_student, $id_reference, $type, $message)
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Invalid id_student");
        
        if (empty($id_reference) || $id_reference <= 0)
            throw new \InvalidArgumentException("Invalid id_reference");
        
        if (empty($type) || ($type != 0 && $type != 1))
            throw new \InvalidArgumentException("Invalid type");
            
        if (empty($message))
            throw new \InvalidArgumentException("Message cannot be empty");
        
        $sql = $this->db->prepare("
            INSERT INTO notifications
            (id_student, date, id_reference, type, message)
            VALUES (?, NOW(), ?, ?, ?)
        ");
        
        $sql->execute(array($id_student, $id_reference, $type, $message));
        
        return $sql->rowCount() > 0;
    }
    
    public function markAsRead($id_student, $date)
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Invalid id_student");
            
        if (empty($date))
            throw new \InvalidArgumentException("Invalid date");
            
        $sql = $this->db->prepare("
            UPDATE  notifications
            SET     read = 1
            WHERE   id_student = ? AND date = ?
        ");
        
        $sql->execute(array($id_student, $date));
    }
}