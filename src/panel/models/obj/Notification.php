<?php
namespace models\obj;


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
    private $id_student;
    private $date;
    private $ref_text;
    private $type;
    private $message;
    private $read;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a notification.
     *
     * @param       int $id_student Student id to which the notification
     * belongs
     * @param       string $date Date to which the notification was generated
     * @param       string $ref_text Topic message or comment message to which
     * the notification refers
     * @param       int $type $id_reference type - 0 for comment id or 1 to
     * comment id.
     * @param       string $message Notification content
     * @param       int $read [Optional] If the notification has not yet been
     * read
     */
    public function __construct($id_student, $date, $ref_text, $type, $message, $read = 0)
    {
        $this->id_student = $id_student;
        $this->date = $date;
        $this->$ref_text = $ref_text;
        $this->description = $type;
        $this->message = $message;
        $this->read = $read == 1;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    public function getStudentId()
    {
        return $this->id_student;
    }
    
    public function getDate()
    {
        return $this->date;
    }
    
    public function getRefText()
    {
        return $this->ref_text;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function getMessage()
    {
        return $this->message;
    }
    
    public function wasRead()
    {
        return $this->read;
    }
}