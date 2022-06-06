<?php
declare (strict_types=1);

namespace domain;

use DateTime;
use domain\enum\NotificationTypeEnum;


/**
 * Responsible for representing notifications.
 */
class Notification
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $idNotification;
    private $idStudent;
    private $date;
    private $reference;
    private $type;
    private $message;
    private $read;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a notification.
     *
     * @param       int idNotification Notification id
     * @param       int idStudent Student id to which the notification
     * belongs
     * @param       DateTime $date Date to which the notification was generated
     * @param       mixed $reference Comment or Support topic to which the 
     * notification refers
     * @param       NotificationTypeEnum $type $id_reference type
     * @param       string $message Notification content
     * @param       int $read [Optional] If the notification has not yet been
     * read
     */
    public function __construct(
        int $idNotification, 
        int $idStudent, 
        DateTime $date, 
        $reference, 
        NotificationTypeEnum $type, 
        string $message, 
        int $read = 0
    )
    {
        $this->idNotification = $idNotification;
        $this->idStudent = $idStudent;
        $this->date = $date;
        $this->reference = $reference;
        $this->type = $type;
        $this->message = $message;
        $this->read = $read == 1;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets notification id.
     *
     * @return      int Notification id
     */
    public function getId() : int
    {
        return $this->idNotification;
    }
    
    /**
     * Gets student id to which the notification belongs.
     * 
     * @return      int Student id
     */
    public function getStudentId() : int
    {
        return $this->idStudent;
    }
    
    /**
     * Gets creation date.
     * 
     * @return      DateTime Creation date
     */
    public function getDate() : DateTime
    {
        return $this->date;
    }
    
    /**
     * Gets reference id to which the notification refers.
     * 
     * @return      mixed Support topic or Comment
     */
    public function getReference()
    {
        return $this->reference;
    }
    
    /**
     * Gets reference id type.
     * 
     * @return      NotificationTypeEnum Reference id type
     */
    public function getReferenceType() : NotificationTypeEnum
    {
        return $this->type;
    }
    
    /**
     * Gets notification's content.
     * 
     * @return      string Notification's content
     */
    public function getMessage() : string
    {
        return $this->message;
    }
    
    /**
     * Checks whether the notification was read.
     * 
     * @return      bool If notification was read or not
     */
    public function wasRead() : bool
    {
        return $this->read;
    }
}