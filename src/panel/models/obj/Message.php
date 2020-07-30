<?php
declare (strict_types=1);

namespace models\obj;


/**
 * Responsible for representing messages of a support topic or a class.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Message
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $user;
    private $date;
    private $message;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a message.
     *
     * @param       User $user User who answered the topic
     * @param       string $date Message posting date
     * @param       string $message Message content
     */
    public function __construct(User $user, string $date, string $message)
    {
        $this->user = $user;
        $this->date = $date;
        $this->message = $message;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets user who created the message.
     * 
     * @return      User User who created the message
     */
    public function getCreator() : int
    {
        return $this->id_comment;
    }
    
    /**
     * Gets message creation date.
     *
     * @return      string Message creation date
     */
    public function getDate() : string
    {
        return $this->date;
    }
    
    /**
     * Gets message content.
     *
     * @return      string Message content
     */
    public function getContent() : string
    {
        return $this->text;
    }
}