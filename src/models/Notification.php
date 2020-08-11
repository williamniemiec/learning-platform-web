<?php
declare (strict_types=1);

namespace models;

use DateTime;
use models\enum\NotificationTypeEnum;


/**
 * Responsible for representing notifications.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Notification
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $id_notification;
    private $id_student;
    private $date;
    private $id_reference;
    private $type;
    private $ref_text;
    private $message;
    private $read;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a notification.
     *
     * @param       int $id_notification Notification id
     * @param       int $id_student Student id to which the notification
     * belongs
     * @param       DateTime $date Date to which the notification was generated
     * @param       mixed $reference Comment or Support topic to which the 
     * notification refers
     * @param       NotificationTypeEnum $type $id_reference type
     * @param       string $ref_text Topic message or comment message to which
     * the notification refers
     * @param       string $message Notification content
     * @param       int $read [Optional] If the notification has not yet been
     * read
     */
    public function __construct(int $id_notification, int $id_student, DateTime $date, $reference,
        NotificationTypeEnum $type, string $ref_text, string $message, int $read = 0)
    {
        $this->id_notification = $id_notification;
        $this->id_student = $id_student;
        $this->date = $date;
        $this->reference = $reference;
        $this->type = $type;
        $this->ref_text = $ref_text;
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
        return $this->id_notification;
    }
    
    /**
     * Gets student id to which the notification belongs.
     * 
     * @return      int Student id
     */
    public function getStudentId() : int
    {
        return $this->id_student;
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
     * Gets reference text.
     * 
     * @return      string Reference text
     */
    public function getRefText() : string
    {
        return $this->ref_text;
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