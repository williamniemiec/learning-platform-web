<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Note;
use domain\Video;


/**
 * Responsible for managing 'notebook' table.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class NotebookDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $db;
    private $id_student;
    private $id_module;
    private $class_order;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'notebook' table manager.
     *
     * @param       Database $db Database
     * @param       int $id_student Student id
     * 
     * @throws      \InvalidArgumentException If student id is empty, less than
     * or equal to zero
     */
    public function __construct(Database $db, int $id_student)
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
            
        $this->db = $db->getConnection();
        $this->id_student = $id_student;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets information about a note.
     *
     * @param       int $id_note Note id
     *
     * @return      Note Information about the note or null if note does
     * not exist or it exists but does not belongs to the student
     *
     * @throws      \InvalidArgumentException If note id is empty, less than or
     * equal to zero
     */
    public function get(int $id_note) : ?Note
    {       
        if (empty($id_note) || $id_note <= 0)
            throw new \InvalidArgumentException("Note id cannot be empty ".
                "or less than or equal to zero");
            
        $response = null;
            
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *,
                    notebook.title AS notebook_title, 
                    videos.title AS videos_title
            FROM    notebook JOIN videos USING (id_module, class_order)
            WHERE   id_student = ? AND id_note = ?
        ");
            
        // Executes query
        $sql->execute(array($this->id_student, $id_note));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $note = $sql->fetch();
            
            $response = new Note(
                (int)$note['id_note'],
                $note['notebook_title'],
                $note['content'],
                new \DateTime($note['date']),
                new Video(
                    (int)$note['id_module'],
                    (int)$note['class_order'],
                    $note['videos_title'],
                    $note['videoID'],
                    (int)$note['length'],
                    $note['description']
                )
            );
        }
        
        return $response;
    }
    
    /**
     * Gets all student notes for a class.
     *
     * @param       int $id_module Module id to which the annotation belongs
     * @param       int $class_order Class order in the module
     * @param       int $limit [Optional] Maximum bundles returned
     * @param       int $offset [Optional] Ignores first results from the return
     *
     * @return      Note[] Notes that the student has or empty array if
     * the student does not have notes for the class.
     *
     * @throws      \InvalidArgumentException If module id, class order is 
     * empty, less than or equal to zero
     */
    public function get_all_from_class(int $id_module, int $class_order, 
        int $limit = -1, int $offset = -1) : array
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Class order cannot be empty ".
                "or less than or equal to zero");
            
        $response = array();
            
        // Query construction
        $query = "
            SELECT      *,
                        notebook.title AS notebook_title,
                        videos.title AS videos_title
            FROM        notebook JOIN videos USING (id_module, class_order)
            WHERE       id_student = ? AND id_module = ? AND class_order = ?
            ORDER BY    date DESC
        ";
            
        if ($limit > 0) {
            if ($offset > 0)
                $query .= " LIMIT ".$offset.",".$limit;
            else
                $query .= " LIMIT ".$limit;
        }
        
        $sql = $this->db->prepare($query);
        
        // Executes query
        $sql->execute(array($this->id_student, $id_module, $class_order));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $note) {
                $response[] = new Note(
                    (int)$note['id_note'],
                    $note['notebook_title'],
                    $note['content'],
                    new \DateTime($note['date']),
                    new Video(
                        (int)$note['id_module'],
                        (int)$note['class_order'],
                        $note['videos_title'],
                        $note['videoID'],
                        (int)$note['length'],
                        $note['description']
                    )
                );
            }
        }
            
        return $response;
    }
    
    /**
     * Creates a new note.
     * 
     * @param       int $id_module Module id to which the annotation was created
     * @param       int $class_order Class order in the module
     * @param       string $title Note's title
     * @param       string $content Note's content
     * 
     * @return      int New note id or -1 if note has not been created
     * 
     * @throws      \InvalidArgumentException If title or content is empty or 
     * if module id or class order or student id provided in the constructor is
     * empty, less than or equal to zero
     */
    public function new(int $id_module, int $class_order, string $title, string $content) : int
    {
        if (empty($this->id_student) || $this->id_student <= 0)
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
            
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Class order cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($title))
            throw new \InvalidArgumentException("Title cannot be empty");
            
        if (empty($content))
            throw new \InvalidArgumentException("Content cannot be empty");
        
        $response = -1;
        
        $sql = $this->db->prepare("
            INSERT INTO notebook
            (id_student, id_module, class_order, title, content, date)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $sql->execute(array(
            $this->id_student, 
            $id_module, 
            $class_order, 
            $title, 
            $content
        ));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = (int)$this->db->lastInsertId();
        }
        
        return $response;
    }
    
    /**
     * Updates a note.
     * 
     * @param       Note $note Note to be updated
     * 
     * @return      bool If note has been successfully updated
     * 
     * @throws      \InvalidArgumentException If note is empty or student id 
     * provided in the constructor is empty, less than or equal to zero
     */
    public function update(Note $note) : bool
    {
        if (empty($this->id_student) || $this->id_student <= 0)
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
            
        if (empty($note))
            throw new \InvalidArgumentException("Note cannot be empty");
   
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  notebook
            SET     title = ?, content = ?
            WHERE   id_student = ? AND id_note = ? 
        ");
                        
        // Executes query
        $sql->execute(array(
            $note->getTitle(),
            $note->getContent(),
            $this->id_student, 
            $note->get_id()
        ));

        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Removes a note.
     *
     * @param       int $id_note Note id to be removed
     *
     * @return      bool If note has been successfully removed.
     *
     * @throws      \InvalidArgumentException If note id or student id provided in the
     * constructor is empty, less than or equal to zero
     */
    public function delete(int $id_note) : bool
    {
        if (empty($this->id_student) || $this->id_student <= 0)
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
            
        if (empty($id_note) || $id_note <= 0)
            throw new \InvalidArgumentException("Note id cannot be empty ".
                "or less than or equal to zero");
            
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM notebook
            WHERE id_student = ? AND id_note = ?
        ");
        
        // Executes query
        $sql->execute(array($this->id_student, $id_note));
            
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Gets all student notes.
     *
     * @param       int $limit [Optional] Maximum bundles returned
     * @param       int $offset [Optional] Ignores first results from the return
     *
     * @return      Note[] Notes that the student has or empty array if
     * the student does not have notes.
     *
     * @throws      \InvalidArgumentException If student id provided in the
     * constructor is empty, less than or equal to zero
     */
    public function get_all(int $limit = -1, int $offset = -1) : array
    {
        if (empty($this->id_student) || $this->id_student <= 0)
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
                
        $response = array();
        
        $query = "
            SELECT  *, 
                    notebook.title AS notebook_title, 
                    videos.title AS videos_title
            FROM    notebook JOIN videos USING (id_module, class_order)
            WHERE   id_student = ?
        ";
        
        if ($limit > 0) {
            if ($offset > 0)
                $query .= " LIMIT ".$offset.",".$limit;
            else
                $query .= " LIMIT ".$limit;
        }
        
        // Query construction
        $sql = $this->db->prepare($query);
            
        // Executes query
        $sql->execute(array($this->id_student));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $note) {
                $response[] = new Note(
                    (int)$note['id_note'],
                    $note['notebook_title'],
                    $note['content'],
                    new \DateTime($note['date']),
                    new Video(
                        (int)$note['id_module'],
                        (int)$note['class_order'],
                        $note['videos_title'],
                        $note['videoID'],
                        (int)$note['length'],
                        $note['description']
                    )
                );
            }
        }
        
        return $response;
    }
    
    /**
     * Gets total number of notes that a student has.
     * 
     * @return      int Total notes
     */
    public function count() : int
    {
        return (int)$this->db->query("
            SELECT  COUNT(*) AS total
            FROM    notebook
            WHERE   id_student = ".$this->id_student
        )->fetch()['total'];
    }
    
    /**
     * Gets total number of notes that a student created in a class.
     *
     * @param       int $id_module Module id to which the annotation belongs
     * @param       int $class_order Class order in the module
     *
     * @return      int Total notes
     */
    public function count_all_from_class(int $id_module, int $class_order) : int
    {
        return (int)$this->db->query("
            SELECT  COUNT(*) AS total
            FROM    notebook JOIN videos USING (id_module, class_order)
            WHERE   id_student = ".$this->id_student." AND 
                    id_module = ".$id_module." AND 
                    class_order = ".$class_order
            )->fetch()['total'];
    }
}