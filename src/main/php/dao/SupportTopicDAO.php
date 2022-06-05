<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\SupportTopic;
use domain\Message;
use domain\SupportTopicCategory;
use domain\util\IllegalAccessException;


/**
 * Responsible for managing 'support_topic' table.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class SupportTopicDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $db;
    private $id_student;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'support_topic' table manager.
     *
     * @param       Database $db Database
     * @param       int $id_student Student id
     * 
     * @throws      \InvalidArgumentException If student id is empty or less 
     * than or equal to zero
     */
    public function __construct(Database $db, int $id_student)
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty or ".
                "less than or equal to zero");
        
        $this->db = $db->getConnection();
        $this->id_student = $id_student;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets information about a support topic.
     *
     * @param      int $id_topic Topic id
     *
     * @return      SupportTopic Support topic with the given id or null if there
     * is no topic with the provided id
     * 
     * @throws      \InvalidArgumentException If topic id is empty or less than
     * or equal to zero
     */
    public function get(int $id_topic) : ?SupportTopic
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be empty ".
                "or less than or equal to zero");
        
        $response = null;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    support_topic NATURAL JOIN support_topic_category
            WHERE   id_topic = ?
        ");
        
        // Executes query
        $sql->execute(array($id_topic));

        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $topic = $sql->fetch();
            $students = new StudentsDAO($this->db, (int)$topic['id_student']);
            
            $response = new SupportTopic(
                (int)$topic['id_topic'],
                $students->get(),
                $topic['title'],
                new SupportTopicCategory((int)$topic['id_category'], $topic['name']),
                new \DateTime($topic['date']), 
                $topic['message'], 
                (int)$topic['closed']
            );
        }
        
        return $response;
    }
    
    /**
     * Gets all topics created by the student.
     * 
     * @param       int $limit [Optional] Maximum topics returned
     * @param       int $offset [Optional] Ignores first results from the return
     * 
     * @return      SupportTopic[] Support topics creadted by the student or 
     * empty array if he has not created a topic yet
     */
    public function getAll(int $limit = -1, int $offset = -1) : array
    {
        $response = array();
        
        $query = "
            SELECT  *
            FROM    support_topic NATURAL JOIN support_topic_category
            WHERE   id_student = ".$this->id_student;

        // Limits the results (if a limit was given)
        if ($limit > 0) {
            if ($offset > 0)
                $query .= " LIMIT ".$offset.",".$limit;
            else
                $query .= " LIMIT ".$limit;
        }
        
        $sql = $this->db->query($query);
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $topic) {
                $studentsDAO = new StudentsDAO($this->db, (int)$topic['id_student']);
                
                $response[] = new SupportTopic(
                    (int)$topic['id_topic'],
                    $studentsDAO->get(),
                    $topic['title'],
                    new SupportTopicCategory((int)$topic['id_category'], $topic['name']),
                    new \DateTime($topic['date']),
                    $topic['message'],
                    (int)$topic['closed']
                );
            }
        }
        
        return $response;
    }
    
    /**
     * Creates a new support topic.
     * 
     * @param       int $id_category Category id that the support topic belongs
     * @param       int $id_student Student id that created the support topic
     * @param       string $title Support topic's title
     * @param       string $message Support topic's content
     * 
     * @return      bool If support topic has been successfully created
     * 
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function new(int $id_category, int $id_student, string $title, 
        string $message) : bool
    {
        if (empty($id_category) || $id_category <= 0)
            throw new \InvalidArgumentException("Category id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($title))
            throw new \InvalidArgumentException("Title cannot be empty");
        
        if (empty($message))
            throw new \InvalidArgumentException("Message cannot be empty");
        
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO support_topic
            (id_category, id_student, title, date, message)
            VALUES (?, ?, ?, NOW(), ?)
        ");
        
        // Executes query
        $sql->execute(array($id_category, $id_student, $title, $message));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Removes a support topic
     * 
     * @param       int $id_topic Support topic id to be deleted
     * @param       int $id_student Student id logged in. It is necessary to 
     * prevent a student from deleting a topic that is not his 
     * 
     * @return      bool If support topic has been successfully removed
     * 
     * @throws      \InvalidArgumentException If topic id or student id is empty
     * or less than or equal to zero
     */
    public function delete(int $id_topic, int $id_student) : bool
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM support_topic
            WHERE id_topic = ? AND id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($id_topic, $id_student));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Closes a support topic.
     * 
     * @param       int $id_topic Support topic id to be closed
     * 
     * @return      bool If support topic has been successfully closed
     * 
     * @throws      \InvalidArgumentException If topic id is empty or less than
     * or equal to zero
     */
    public function close(int $id_topic) : bool
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be empty ".
                "or less than or equal to zero");
        
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  support_topic
            SET     closed = 1
            WHERE   id_topic = ? AND id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($id_topic, $this->id_student));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Opens a support topic.
     *
     * @param       int $id_topic Support topic id to be opened
     *
     * @return      bool If support topic has been successfully closed
     * 
     * @throws      \InvalidArgumentException If topic id is empty or less than
     * or equal to zero
     */
    public function open(int $id_topic) : bool
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be empty ".
                "or less than or equal to zero");
        
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  support_topic
            SET     closed = 0
            WHERE   id_topic = ? AND id_student = ?
        ");
        
        // Executes query
        $sql->execute(array($id_topic, $this->id_student));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Replies a support topic.
     * 
     * @param       int $id_topic Support topic id to be replied
     * @param       int $id_student Student id that will reply the support topic
     * @param       string $message Reply's content
     * 
     * @return      bool If the reply has been successfully added
     * 
     * @throws      \InvalidArgumentException If any argument is invalid
     * @throws      IllegalAccessException If support topic is closed
     */
    public function newReply(int $id_topic, int $id_student, string $message) : bool
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($message))
            throw new \InvalidArgumentException("Message cannot be empty");
        
        if (!$this->isOpen($id_topic))
            throw new IllegalAccessException("Topic is closed");
            
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO support_topic_replies
            (id_topic, id_user, user_type, date, text)
            VALUES (?, ?, 0, NOW(), ?)
        ");
        
        // Executes query
        $sql->execute(array($id_topic, $id_student, $message));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Gets all replies from a support topic.
     * 
     * @param       int $id_topic Support topic id
     * 
     * @return      Message[] Support topic replies or empty array if there are
     * no replies
     * 
     * @throws      \InvalidArgumentException If topic id is empty or less than
     * or equal to zero
     */
    public function getReplies(int $id_topic) : array
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be empty ".
                "or less than or equal to zero");

        $response = array();
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    support_topic_replies
            WHERE   id_topic = ?
        ");
        
        // Executes query
        $sql->execute(array($id_topic));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $replies = $sql->fetchAll();
            
            foreach ($replies as $reply) {
                if ($reply['user_type'] == 0) {
                    $students = new StudentsDAO($this->db, (int)$reply['id_user']);
                    $user = $students->get();
                }
                else {
                    $admins = new AdminsDAO($this->db);
                    $user = $admins->get((int)$reply['id_user']);
                }
                
                $response[] = new Message(
                    $user, 
                    new \DateTime($reply['date']), 
                    $reply['text'],
                    (int)$reply['id_reply']
                );
            }
        }
            
        return $response;
    }
    
    /**
     * Gets all answered support topics from a user with a specific category.
     * 
     * @param       string $name [Optional] Support topic title to be searched
     * @param       int $id_category [Optional] Category id
     * 
     * @return      SupportTopic[] Support topics that have already been 
     * answered and that belongs to the category with the given id or empty
     * array if there are no matches
     */
    public function getAllAnsweredByCategory(string $name = '', int $id_category = 0) : array
    {
        $response = array();
        
        // Query construction
        $query = "
            SELECT  *
            FROM    support_topic NATURAL JOIN support_topic_category
            WHERE   id_student = ? AND
                    title LIKE ? AND
                    id_topic IN (SELECT id_topic
                                 FROM   support_topic_replies)
        ";
        
        if ($id_category > 0)
            $query .= " AND id_category = ".$id_category;
        
        $sql = $this->db->prepare($query);
        
        // Executes query
        $sql->execute(array($this->id_student, $name."%"));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $supportTopic) {            
                $students = new StudentsDAO($this->db, (int)$supportTopic['id_student']);
                
                $response[] = new SupportTopic(
                    (int)$supportTopic['id_topic'],
                    $students->get(),
                    $supportTopic['title'],
                    new SupportTopicCategory((int)$supportTopic['id_category'], $supportTopic['name']),
                    new \DateTime($supportTopic['date']),
                    $supportTopic['message'],
                    (int)$supportTopic['closed']
                );
            }
        }
        
        return $response;
    }
    
    /**
     * Searches for a topic with a given name.
     * 
     * @param       string $name [Optional] Support topic title to be searched
     * @param       int $id_category [Optional] Searches for topics that belongs to a
     * category
     * 
     * @return      SupportTopic[] Support topics that match with the provided
     * name or empty array if there are no matches
     */
    public function search(string $name = '', int $id_category = 0) : array
    {
        $response = array();
        
        // Query construction
        $query = "
            SELECT  *
            FROM    support_topic NATURAL JOIN support_topic_category
            WHERE   id_student = ? AND title LIKE ?
        ";
        
        if ($id_category > 0)
            $query .= " AND id_category = ".$id_category;
        
        $sql = $this->db->prepare($query);
        
        // Executes query
        $sql->execute(array($this->id_student, $name.'%'));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $supportTopic) {
                $students = new StudentsDAO($this->db, (int)$supportTopic['id_student']);
                
                $response[] = new SupportTopic(
                    (int)$supportTopic['id_topic'],
                    $students->get(),
                    $supportTopic['title'],
                    new SupportTopicCategory((int)$supportTopic['id_category'], $supportTopic['name']),
                    new \DateTime($supportTopic['date']),
                    $supportTopic['message'],
                    (int)$supportTopic['closed']
                );
            }
        }
        
        return $response;
    }
    
    /**
     * Gets all support topic categories.
     * 
     * @return      SupportTopicCategory[] Support topic categories or empty 
     * array if there are no registered categories
     */
    public function getCategories() : array
    {
        $response = array();
        
        // Query construction
        $sql = $this->db->query("
            SELECT  *
            FROM    support_topic_category
        ");
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $category) {
                $response[] = new SupportTopicCategory(
                    (int)$category['id_category'],
                    $category['name']
                );
            }
        }
        
        return $response;
    }
    
    /**
     * Gets total of support topics.
     *
     * @return      int Total of bundles
     */
    public function count() : int
    {
        return (int)$this->db->query("
            SELECT  COUNT(*) AS total
            FROM    support_topic
        ")->fetch()['total'];
    }
    
    /**
     * Checks whether a support topic is open.
     * 
     * @param       int $id_topic Support topic id
     * 
     * @return      bool If support topic is open
     */
    private function isOpen(int $id_topic) : bool
    {
        $sql = $this->db->prepare("CALL sp_support_topic_is_open(?, @isOpen)");
        $sql->execute(array($id_topic));
        
        $sql = $this->db->query("SELECT @isOpen AS is_open");
        
        return $sql->fetch()['is_open'] == 1;
    }
}