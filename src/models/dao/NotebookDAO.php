<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Note;
use models\Video;


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
     */
    public function __construct(Database $db, int $id_student)
    {
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
     * not exist
     *
     * @throws      \InvalidArgumentException If note id or student id provided
     * in the constructor is empty, less than or equal to zero
     */
    public function get(int $id_note) : Note
    {
        if (empty($this->id_student) || $this->id_student <= 0)
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
            
        if (empty($id_note) || $id_note <= 0)
            throw new \InvalidArgumentException("Note id cannot be empty ".
                "or less than or equal to zero");
            
        $response = null;
            
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    notebook NATURAL JOIN videos
            WHERE   id_student = ? AND id_note = ?
        ");
            
        // Executes query
        $sql->execute(array($this->id_student, $id_note));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $note = $sql->fetch(\PDO::ATTR_FETCH_TABLE_NAMES);
            
            $response = new Note(
                $note['notebook.id_note'],
                $note['notebook.content'],
                $note['notebook.note'],
                new Video(
                    $note['videos.id_module'],
                    $note['videos.class_order'],
                    $note['videos.title'],
                    $note['videos.videoID'],
                    $note['videos.length'],
                    $note['videos.description']
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
     * @throws      \InvalidArgumentException If module id, class order or 
     * student id provided in the constructor is empty, less than or equal to 
     * zero
     */
    public function getAllFromClass(int $id_module, int $class_order, 
        int $limit = -1, int $offset = -1) : array
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
            
        $response = array();
            
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    notebook NATURAL JOIN videos
            WHERE   id_student = ? AND id_module = ? AND class_order = ?
        ");
            
        // Executes query
        $sql->execute(array($this->id_student, $id_module, $class_order));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $note = $sql->fetchAll(\PDO::ATTR_FETCH_TABLE_NAMES);
            
            $response[] = new Note(
                $note['notebook.id_note'],
                $note['notebook.content'],
                $note['notebook.note'],
                new Video(
                    $note['videos.id_module'],
                    $note['videos.class_order'],
                    $note['videos.title'],
                    $note['videos.videoID'],
                    $note['videos.length'],
                    $note['videos.description']
                )
            );
        }
            
        return $response;
    }
    
    /**
     * Updates a note.
     * 
     * @param       int $id_note Note id to be updated
     * @param       string $newTitle New title
     * @param       string $newContent New content
     * 
     * @return      bool If note has been successfully updated
     * 
     * @throws      \InvalidArgumentException If note id or student id provided
     * in the constructor is empty, less than or equal to zero or if title or
     * content is empty
     */
    public function update(int $id_note, string $newTitle, string $newContent) : bool
    {
        if (empty($this->id_student) || $this->id_student <= 0)
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
            
        if (empty($id_note) || $id_note <= 0)
            throw new \InvalidArgumentException("Note id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($newTitle))
            throw new \InvalidArgumentException("Title cannot be empty");
        
        if (empty($newContent))
            throw new \InvalidArgumentException("Content cannot be empty");
   
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  notebook
            SET     title = ?, content = ?
            WHERE   id_student = ? AND id_note = ? 
        ");
                        
        // Executes query
        $sql->execute(array($this->id_student, $newTitle, $newContent));
        
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
        $sql = $this->db->query("
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
    public function getAll(int $limit = -1, int $offset = -1) : array
    {
        if (empty($this->id_student) || $this->id_student <= 0)
            throw new \InvalidArgumentException("Student id logged in must be ".
                "provided in the constructor");
                
        $response = array();
        
        $query = "
            SELECT  *
            FROM    notebook NATURAL JOIN videos
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
            $note = $sql->fetchAll(\PDO::ATTR_FETCH_TABLE_NAMES);
            
            $response[] = new Note(
                $note['notebook.id_note'],
                $note['notebook.content'],
                $note['notebook.note'],
                new Video(
                    $note['videos.id_module'],
                    $note['videos.class_order'],
                    $note['videos.title'],
                    $note['videos.videoID'],
                    $note['videos.length'],
                    $note['videos.description']
                )
            );
        }
        
        return $response;
    }
}