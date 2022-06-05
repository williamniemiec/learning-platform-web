<?php
declare (strict_types=1);

namespace domain;


use repositories\Database;
use DateTime;
use domain\dao\SupportTopicDAO;


/**
 * Responsible for representing a support topic.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class SupportTopic implements \JsonSerializable
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
    private $db;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a support topic.
     *
     * @param       int $id_topic Topic id
     * @param       Student $student Student who created the support topic
     * @param       string $title
     * @param       SupportTopicCategory $category Support topic category 
     * @param       DateTime $date Support topic creation date
     * @param       string $message Initial Support topic message
     * @param       bool $closed Support topic status
     */
    public function __construct(int $id_topic, Student $student, string $title,
        SupportTopicCategory $category, DateTime $date, string $message, int $closed)
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
    //        Getters & Setters
    //-------------------------------------------------------------------------
    /**
     * Gets support topic id.
     * 
     * @return      int support topic id
     */
    public function getId() : int
    {
        return $this->id_topic;
    }
    
    /**
     * Gets support topic creator.
     *
     * @return      Student Student who created the support topic
     */
    public function getCreator() : Student
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
     * @return      SupportTopicCategory Support topic category
     */
    public function getCategory() : SupportTopicCategory
    {
        return $this->category;
    }
    
    /**
     * Gets support topic creation date.
     *
     * @return      DateTime Support topic creation date
     */
    public function getCreationDate() : DateTime
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
     * @throws      \InvalidArgumentException If database has not yet been
     * set and a database is not provided to obtain this information
     * 
     * @implNote    Lazy initialization
     */
    public function getReplies(?Database $db = null) : array
    {
        if (empty($this->db) && empty($db))
            throw new \InvalidArgumentException("Database cannot be empty");
        
        if (empty($db)) {
            $db = $this->db;       
        }
            
        if (empty($this->replies)) {
            $topic = new SupportTopicDAO($db, Student::get_logged_in($db)->get_id());
            $this->replies = $topic->getReplies($this->id_topic);
        }
        
        return $this->replies;
    }
    
    /**
     * Sets a database.
     * 
     * @param       Database $db Database
     * 
     * @return      SupportTopic Itself to allow chained calls
     * 
     * @throws      \InvalidArgumentException If database is null
     */
    public function set_database(Database $db) : SupportTopic
    {
        if (empty($db))
            throw new \InvalidArgumentException("Database cannot be empty");
        
        $this->db = $db;
        
        return $this;
    }
    
    
    //-------------------------------------------------------------------------
    //        Serialization
    //-------------------------------------------------------------------------
    /**
     * {@inheritDoc}
     *  @see \JsonSerializable::jsonSerialize()
     *
     *  @Override
     */
    public function jsonSerialize()
    {
        return array(
            'id' => $this->id_topic,
            'student' => $this->student,
            'title' => $this->title,
            'category' => $this->category,
            'date' => $this->date->format("Y/m/d H:i:s"),
            'closed' => $this->closed,
            'replies' => $this->replies
        );
    }
}