<?php
declare (strict_types=1);

namespace models;


use database\Database;


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
    private $student;
    private $title;
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
     * @param       string $title
     * @param       string $category Support topic category 
     * @param       string $date Support topic creation date
     * @param       string $text Initial Support topic message
     * @param       bool $closed Support topic status
     * @param       Message[] $replies [Optional] Support topic replies
     */
    public function __construct(int $id_topic, Student $student, string $title,
        string $category, string $date, string $message, int $closed)
    {
        $this->id_topic = $id_topic;
        $this->student = $student;
        $this->title = $title;
        $this->category = $category;
        $this->date = $date;
        $this->message = $message;
        $this->closed = $closed == 1;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets support topic id.
     * 
     * @return      int support topic id
     */
    public function getSupportTopicId() : int
    {
        return $this->id_comment;
    }
    
    /**
     * Gets support topic creator.
     *
     * @return      Student Student who created the support topic
     */
    public function getCreator() : string
    {
        return $this->student;
    }
    
    /**
     * Gets support topic title.
     * 
     * @return      string Support topic title
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * Gets support topic category.
     * 
     * @return      string Support topic category
     */
    public function getCategory() : string
    {
        return $this->category;
    }
    
    /**
     * Gets support topic creation date.
     *
     * @return      string Support topic creation date
     */
    public function getCreationDate() : string
    {
        return $this->date;
    }
    
    /**
     * Gets support topic initial message.
     *
     * @return      string Support topic initial message
     */
    public function getContent() : string
    {
        return $this->message;
    }
    
    /**
     * Checks whether the support topic is closed.
     * 
     * @return      boolean If support topic is closed
     */
    public function isClosed() : bool
    {
        return $this->closed;
    }
    
    /**
     * Gets support topic replies.
     *
     * @param       Database $db Database
     *
     * @return      Message[] Support topic replies or empty array if there are no
     * replies
     * 
     * @implNote    Lazy initialization
     */
    public function getReplies(Database $db) : array
    {
        if (empty($this->replies)) {
            $topic = new SupportTopicDAO($db);
            $this->replies = $topic->getReplies($this->id_topic);
        }
        
        return $this->replies;
    }
}