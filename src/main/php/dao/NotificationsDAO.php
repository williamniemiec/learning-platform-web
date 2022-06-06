<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Notification;
use domain\enum\NotificationTypeEnum;
use domain\Student;


/**
 * Responsible for managing 'notifications' table.
 */
class NotificationsDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $idStudent;
    private $db;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'notifications' table manager.
     *
     * @param       Database $db Database
     * @param       int $idStudent Logged student id
     * 
     * @throws      \InvalidArgumentException If student id is empty or less 
     * than or equal to zero
     */
    public function __construct(Database $db, int $idStudent)
    {
        if (empty($idStudent) || $idStudent <= 0) {
            throw new \InvalidArgumentException("Student id cannot be empty or ".
                "less than or equal to zero");
        }
            
        $this->idStudent = $idStudent;
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
    public function getNotifications(int $limit = 10) : array
    {
        if (empty($limit) || $limit <= 0) {
            throw new \InvalidArgumentException("Limit cannot be empty or ".
                "less than or equal to zero");
        }
        
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
        $sql->execute(array($this->idStudent));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $notifications = $sql->fetchAll();
            
            foreach ($notifications as $notification) {
                if ($notification['type'] == 0) {
                    $commentsDao = new CommentsDAO($this->db);
                    
                    $ref = $commentsDao->get((int) $notification['id_reference']);
                }
                else {
                    $supportTopicDao = new SupportTopicDAO($this->db, Student::getLoggedIn($this->db)->getId());
                    $ref = $supportTopicDao->get((int) $notification['id_reference']);
                }
                
                $response[] = new Notification(
                    (int) $notification['id_notification'],
                    (int) $notification['id_student'], 
                    new \DateTime($notification['date']),
                    $ref,
                    new NotificationTypeEnum($notification['type']),
                    $notification['message'],
                    (int) $notification['read']
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
        $sql->execute(array($this->idStudent));
        
        return (int) $sql->fetch()['total_unread'];
    }
    
    /**
     * Removes a notification.
     * 
     * @param       int idNotification Notification id
     * 
     * @return      bool If notification has been successfully removed

     * @throws      \InvalidArgumentException If notification id is empty or
     * less than or equal to zero
     */
    public function delete(int $idNotification) : bool
    {
        if (empty($idNotification) || $idNotification <= 0) {
            throw new \InvalidArgumentException("Notification id cannot be empty ".
                "or less than or equal to zero");
        }
        
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM notifications
            WHERE id_student = ? AND id_notification = ?
        ");
        
        // Executes query
        $sql->execute(array($this->idStudent, $idNotification));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Mark a notification as read.
     * 
     * @param       int idNotification Notification id
     * 
     * @throws      \InvalidArgumentException If notification id is empty or
     * less than or equal to zero
     */
    public function markAsRead(int $idNotification) : void
    {
        if (empty($idNotification) || $idNotification <= 0) {
            throw new \InvalidArgumentException("Notification id cannot be empty ".
                "or less than or equal to zero");
        }
            
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  notifications
            SET     `read` = 1
            WHERE   id_student = ? AND id_notification = ?
        ");
        
        // Executes query
        $sql->execute(array($this->idStudent, $idNotification));
    }
    
    /**
     * Mark a notification as unread.
     *
     * @param       int $idNotification Notification id
     *
     * @throws      \InvalidArgumentException If notification id is empty or
     * less than or equal to zero
     */
    public function markAsUnread(int $idNotification) : void
    {
        if (empty($idNotification) || $idNotification <= 0) {
            throw new \InvalidArgumentException("Notification id cannot be empty ".
                "or less than or equal to zero");
        }
            
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  notifications
            SET     `read` = 0
            WHERE   id_student = ? AND id_notification = ?
        ");
            
        // Executes query
        $sql->execute(array($this->idStudent, $idNotification));
    }
}
