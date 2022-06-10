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
class NotificationsDAO extends DAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $idStudent;
    
    
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
        parent::__construct($db);   
        $this->validateStudentId($idStudent);
        $this->idStudent = $idStudent;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    private function validateStudentId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Student id cannot be empty ".
                                                "or less than or equal to zero");
        }
    }

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
        $this->validateLimit($limit);
        $this->withQuery("
            SELECT      *
            FROM        notifications
            WHERE       id_student = ?
            ORDER BY    date DESC, `read` ASC
            LIMIT       ".$limit."
        ");
        $this->runQueryWithArguments($this->idStudent);

        return $this->parseNotificationsResponseQuery();
    }

    private function validateLimit($value)
    {
        if (empty($value) || $value <= 0) {
            throw new \InvalidArgumentException("Limit cannot be empty or ".
                                                "less than or equal to zero");
        }
    }

    private function parseNotificationsResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }
        
        $notifications = array();
        
        foreach ($this->getAllResponseQuery() as $notification) {
            if ($notification['type'] == 0) {
                $commentsDao = new CommentsDAO($this->db);
                
                $ref = $commentsDao->get((int) $notification['id_reference']);
            }
            else {
                $supportTopicDao = new SupportTopicDAO(
                    $this->db, 
                    Student::getLoggedIn($this->db)->getId()
                );
                $ref = $supportTopicDao->get((int) $notification['id_reference']);
            }
            
            $notifications[] = new Notification(
                (int) $notification['id_notification'],
                (int) $notification['id_student'], 
                new \DateTime($notification['date']),
                $ref,
                new NotificationTypeEnum($notification['type']),
                $notification['message'],
                (int) $notification['read']
            );
        }

        

        return $notifications;
    }

    /**
     * Gets total of unread notifications.
     * 
     * @return      int Total unread notifications
     */
    public function countUnreadNotification() : int
    {
        $this->withQuery("
            SELECT  COUNT(*) AS total_unread
            FROM    notifications
            WHERE   id_student = ? AND `read` = 0
        ");
        $this->runQueryWithArguments($this->idStudent);
        
        return ((int) $this->getResponseQuery()['total_unread']);
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
        $this->validateNotificationId($idNotification);
        $this->withQuery("
            DELETE FROM notifications
            WHERE id_student = ? AND id_notification = ?
        ");
        $this->runQueryWithArguments($this->idStudent, $idNotification);
        
        return $this->hasResponseQuery();
    }

    private function validateNotificationId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Notification id cannot be ".
                                                "empty or less than or equal ".
                                                "to zero");
        }
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
        $this->validateNotificationId($idNotification);
        $this->withQuery("
            UPDATE  notifications
            SET     `read` = 1
            WHERE   id_student = ? AND id_notification = ?
        ");
        $this->runQueryWithArguments($this->idStudent, $idNotification);
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
        $this->validateNotificationId($idNotification);
        $this->withQuery("
            UPDATE  notifications
            SET     `read` = 0
            WHERE   id_student = ? AND id_notification = ?
        ");
        $this->runQueryWithArguments($this->idStudent, $idNotification);
    }
}
