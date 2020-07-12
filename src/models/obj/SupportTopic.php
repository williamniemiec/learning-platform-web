<?php
namespace models\obj;


/**
 * Responsible for representing a support topic.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class SupportTopic
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $id_topic;
    private $category;
    private $date;
    private $message;
    private $closed;
    private $replies;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a support topic.
     *
     * @param       int $id_topic Topic id
     * @param       Student $student Student who created the support topic
     * @param       string $category Support topic category 
     * @param       string $date Support topic creation date
     * @param       string $text Initial Support topic message
     * @param       bool $closed Support topic status
     * @param       Message[] $replies [Optional] Support topic replies
     */
    public function __construct($id_topic, $student, $category, $date, $message, $closed, $replies = array())
    {
        $this->id_topic = $id_topic;
        $this->student = $student;
        $this->category = $category;
        $this->date = $date;
        $this->message = $message;
        $this->closed = $closed == 1;
        $this->replies = $replies;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets support topic id.
     * 
     * @return      int support topic id
     */
    public function getSupportTopicId()
    {
        return $this->id_comment;
    }
    
    /**
     * Gets support topic creator.
     *
     * @return      Student Student who created the support topic
     */
    public function getCreator()
    {
        return $this->student;
    }
    
    /**
     * Gets support topic category.
     * 
     * @return      string Support topic category
     */
    public function getCategory()
    {
        return $this->category;
    }
    
    /**
     * Gets support topic creation date.
     *
     * @return      string Support topic creation date
     */
    public function getCreationDate()
    {
        return $this->date;
    }
    
    /**
     * Gets support topic initial message.
     *
     * @return      string Support topic initial message
     */
    public function getContent()
    {
        return $this->message;
    }
    
    /**
     * Checks whether the support topic is closed.
     * 
     * @return      boolean If support topic is closed
     */
    public function isClosed()
    {
        return $this->closed;
    }
    
    /**
     * Gets support topic replies.
     *
     * @return      Message[] Support topic replies or empty array if there are no
     * replies
     */
    public function getReplies()
    {
        return $this->replies;
    }
}