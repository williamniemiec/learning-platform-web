<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Notification;
use domain\enum\NotificationTypeEnum;
use domain\SupportTopic;
use domain\Comment;
use domain\Student;

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
     * 
     * @throws      \InvalidArgumentException If student id is empty or less 
     * than or equal to zero
     */
    public function __construct(Database $db, int $id_student)
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty or ".
                "less than or equal to zero");
            
        $this->id_student = $id_student;
        $this->db = $db->getConnection();        
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
     * @throws      \InvalidArgumentException If limit is empty or less than or
     * equal to zero
     */
    public function get_notifications(int $limit = 10) : array
    {
        if (empty($limit) || $limit <= 0)
            throw new \InvalidArgumentException("Limit cannot be empty or ".
                "less than or equal to zero");
        
        $response = array();
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT      *
            FROM        notifications
            WHERE       id_student = ?
            ORDER BY    date DESC, `read` ASC
            LIMIT       ".$limit."
        ");
        
        // Executes query
        $sql->execute(array($this->id_student));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $notifications = $sql->fetchAll();
            
            foreach ($notifications as $notification) {
                if ($notification['type'] == 0) {
                    $commentsDAO = new CommentsDAO($this->db);
                    
                    $ref = $commentsDAO->get((int)$notification['id_reference']);
                }
                else {
                    $supportTopicDAO = new SupportTopicDAO($this->db, Student::get_logged_in($this->db)->get_id());
                    $ref = $supportTopicDAO->get((int)$notification['id_reference']);
                }
                
                $response[] = new Notification(
                    (int)$notification['id_notification'],
                    (int)$notification['id_student'], 
                    new \DateTime($notification['date']),
                    $ref,
                    new NotificationTypeEnum($notification['type']),
                    $notification['message'],
                    (int)$notification['read']
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
    public function count_unread_notification() : int
    {
        // Query construction
        $sql = $this->db->prepare("
            SELECT  COUNT(*) AS total_unread
            FROM    notifications
            WHERE   id_student = ? AND `read` = 0
        ");

        // Executes query
        $sql->execute(array($this->id_student));
        
        return (int)$sql->fetch()['total_unread'];
    }
    
    /**
     * Removes a notification.
     * 
     * @param       int $id_notification Notification id
     * 
     * @return      bool If notification has been successfully removed

     * @throws      \InvalidArgumentException If notification id is empty or
     * less than or equal to zero
     */
    public function delete(int $id_notification) : bool
    {
        if (empty($id_notification) or $id_notification <= 0)
            throw new \InvalidArgumentException("Notification id cannot be empty ".
                "or less than or equal to zero");
        
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM notifications
            WHERE id_student = ? AND id_notification = ?
        ");
        
        // Executes query
        $sql->execute(array($this->id_student, $id_notification));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Mark a notification as read.
     * 
     * @param       int $id_notification Notification id
     * 
     * @throws      \InvalidArgumentException If notification id is empty or
     * less than or equal to zero
     */
    public function mark_as_read(int $id_notification) : void
    {
        if (empty($id_notification) or $id_notification <= 0)
            throw new \InvalidArgumentException("Notification id cannot be empty ".
                "or less than or equal to zero");
            
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  notifications
            SET     `read` = 1
            WHERE   id_student = ? AND id_notification = ?
        ");
        
        // Executes query
        $sql->execute(array($this->id_student, $id_notification));
    }
    
    /**
     * Mark a notification as unread.
     *
     * @param       int $id_notification Notification id
     *
     * @throws      \InvalidArgumentException If notification id is empty or
     * less than or equal to zero
     */
    public function markAsUnread(int $id_notification) : void
    {
        if (empty($id_notification) or $id_notification <= 0)
            throw new \InvalidArgumentException("Notification id cannot be empty ".
                "or less than or equal to zero");
            
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  notifications
            SET     `read` = 0
            WHERE   id_student = ? AND id_notification = ?
        ");
            
        // Executes query
        $sql->execute(array($this->id_student, $id_notification));
    }
}
