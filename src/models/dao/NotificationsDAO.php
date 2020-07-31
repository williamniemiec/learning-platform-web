<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Notification;
use models\enum\NotificationTypeEnum;

/**
 * Responsible for managing 'notifications' table.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class NotificationsDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $id_student;
    private $db;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'notifications' table manager.
     *
     * @param       Database $db Database
     */
    public function __construct(Database $db, int $id_user)
    {
        $this->db = $db->getConnection();
        $this->id_student = $id_user;
        
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets notifications from current student.
     * 
     * @param       int $limit [Optional] Maximum notifications that will be 
     * caught. Default value is 10
     * 
     * @return      Notification[] Notifications that current student has
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function getNotifications(int $limit = 10) : array
    {
        if (empty($limit) || $limit <= 0)
            throw new \InvalidArgumentException("Invalid limit");
        
        $response = array();
        
        // Query construction
        $sql = $this->db->prepare("
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
            WHERE   id_student = ?
            LIMIT   ".$limit."
        ");
        
        // Executes query
        $sql->execute(array($this->id_student));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $notifications = $sql->fetchAll();
            
            foreach ($notifications as $notification) {
                $response[] = new Notification(
                    $notification['id_student'], 
                    $notification['date'],
                    $notification['id_reference'],
                    new NotificationTypeEnum($notification['type']),
                    $notification['ref_text'],
                    $notification['type'],
                    $notification['message'],
                    $notification['read']
                );
            }
        }
        
        return $response;
    }

    /**
     * Gets total of unread notifications.
     * 
     * @return      int Total unread notifications
     */
    public function countUnreadNotification() : int
    {
        // Query construction
        $sql = $this->db->prepare("
            SELECT  COUNT(*) AS total_unread
            FROM    notifications
            WHERE   id_student = ? AND `read` = 0
        ");

        // Executes query
        $sql->execute(array($this->id_student));
        
        return $sql->fetch()['total_unread'];
    }
    
    /**
     * Deletes a notification
     * 
     * @param       string $date Notification creation date
     * 
     * @return      bool If notification was sucessfully deleted

     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function delete(string $date) : bool
    {
        if (empty($date))
            throw new \InvalidArgumentException("Invalid date");
        
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM notifications
            WHERE id_student = ? AND date = ?
        ");
        
        // Executes query
        $sql->execute(array($this->id_student, $date));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Creates a new notification.
     * 
     * @param       int $id_reference Reference id (comment id or support topic id)
     * @param       NotificationTypeEnum $type Notification type
     * @param       string $message Notification content
     * 
     * @return      bool If the notification was sucessfully created
     * 
     * @throws \InvalidArgumentException If any argument is invalid 
     */
    public function new(int $id_reference, NotificationTypeEnum $type, string $message) : bool
    {
        if (empty($id_reference) || $id_reference <= 0)
            throw new \InvalidArgumentException("Invalid id_reference");
        
        if (empty($type) || ($type != 0 && $type != 1))
            throw new \InvalidArgumentException("Invalid type");
            
        if (empty($message))
            throw new \InvalidArgumentException("Message cannot be empty");
        
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO notifications
            (id_student, date, id_reference, type, message)
            VALUES (?, NOW(), ?, ?, ?)
        ");
        
        // Executes query
        $sql->execute(array($this->id_student, $id_reference, $type->get(), $message));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Mark a notification as read.
     * 
     * @param       string $date Notification creation date
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function markAsRead(string $date) : void
    {
        if (empty($date))
            throw new \InvalidArgumentException("Invalid date");
            
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  notifications
            SET     read = 1
            WHERE   id_student = ? AND date = ?
        ");
        
        // Executes query
        $sql->execute(array($this->id_student, $date));
    }
    
    /**
     * Mark a notification as unread.
     *
     * @param       string $date Notification creation date
     *
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function markAsUnread(string $date) : void
    {
        if (empty($date))
            throw new \InvalidArgumentException("Invalid date");
            
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  notifications
            SET     read = 0
            WHERE   id_student = ? AND date = ?
        ");
            
        // Executes query
        $sql->execute(array($this->id_student, $date));
    }
}
