<?php
/**
 * Copyright (c) William Niemiec.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Note;
use domain\Video;


/**
 * Responsible for managing 'notebook' table.
 */
class NotebookDAO extends DAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $idStudent;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'notebook' table manager.
     *
     * @param       Database $db Database
     * @param       int idStudent Student id
     * 
     * @throws      \InvalidArgumentException If student id is empty, less than
     * or equal to zero
     */
    public function __construct(Database $db, int $idStudent)
    {
        parent::__construct($db);
        $this->validateStudentId($idStudent);
        $this->idStudent = $idStudent;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    private function validateStudentId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Student id cannot be empty ".
                                                "or less than or equal to zero");
        }
    }

    /**
     * Gets information about a note.
     *
     * @param       int idNote Note id
     *
     * @return      Note Information about the note or null if note does
     * not exist or it exists but does not belongs to the student
     *
     * @throws      \InvalidArgumentException If note id is empty, less than or
     * equal to zero
     */
    public function get(int $idNote) : ?Note
    { 
        $this->validateNoteId($idNote);
        $this->withQuery("
            SELECT  *,
                    notebook.title AS notebook_title, 
                    videos.title AS videos_title
            FROM    notebook JOIN videos USING (id_module, class_order)
            WHERE   id_student = ? AND id_note = ?
        ");
        $this->runQueryWithArguments($this->idStudent, $idNote);
        
        return $this->parseNotebookResponseQuery();
    }

    private function validateNoteId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Note id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }

    private function parseNotebookResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return null;
        }

        $noteRaw = $this->getResponseQuery();
            
        return new Note(
            (int) $noteRaw['id_note'],
            $noteRaw['notebook_title'],
            $noteRaw['content'],
            new \DateTime($noteRaw['date']),
            new Video(
                (int) $noteRaw['id_module'],
                (int) $noteRaw['class_order'],
                $noteRaw['videos_title'],
                $noteRaw['videoID'],
                (int) $noteRaw['length'],
                $noteRaw['description']
            )
        );
    }
    
    /**
     * Gets all student notes for a class.
     *
     * @param       int idModule Module id to which the annotation belongs
     * @param       int classOrder Class order in the module
     * @param       int $limit [Optional] Maximum bundles returned
     * @param       int $offset [Optional] Ignores first results from the return
     *
     * @return      Note[] Notes that the student has or empty array if
     * the student does not have notes for the class.
     *
     * @throws      \InvalidArgumentException If module id, class order is 
     * empty, less than or equal to zero
     */
    public function getAllFromClass(
        int $idModule, 
        int $classOrder, 
        int $limit = -1, 
        int $offset = -1
    ) : array
    {
        $this->validateLoggedStudent();
        $this->validateModuleId($idModule);
        $this->validateClassOrder($classOrder);
        $this->withQuery($this->buildGetAllFromModuleQuery($limit, $offset));
        $this->runQueryWithArguments($this->idStudent, $idModule, $classOrder);
        
        return $this->parseNotebooksResponseQuery();
    }

    private function validateLoggedStudent()
    {
        if (empty($this->idStudent) || $this->idStudent <= 0) {
            throw new \InvalidArgumentException("Student id logged in must be ".
                                                "provided in the constructor");
        }
    }

    private function validateModuleId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Module id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }

    private function validateClassOrder($order)
    {
        if (empty($order) || $order <= 0) {
            throw new \InvalidArgumentException("Class order cannot be empty ".
                                                "or less than or equal to zero");
        }
    }

    private function buildGetAllFromModuleQuery($limit, $offset)
    {
        $query = "
            SELECT      *,
                        notebook.title AS notebook_title,
                        videos.title AS videos_title
            FROM        notebook JOIN videos USING (id_module, class_order)
            WHERE       id_student = ? AND id_module = ? AND class_order = ?
            ORDER BY    date DESC
        ";
            
        if ($limit > 0) {
            if ($offset > 0) {
                $query .= " LIMIT ".$offset.",".$limit;
            }
            else {
                $query .= " LIMIT ".$limit;
            }
        }

        return $query;
    }

    private function parseNotebooksResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $notebooks = array();
        
        foreach ($this->getAllResponseQuery() as $note) {
            $notebooks[] = new Note(
                (int) $note['id_note'],
                $note['notebook_title'],
                $note['content'],
                new \DateTime($note['date']),
                new Video(
                    (int) $note['id_module'],
                    (int) $note['class_order'],
                    $note['videos_title'],
                    $note['videoID'],
                    (int) $note['length'],
                    $note['description']
                )
            );
        }

        return $notebooks;
    }
    
    /**
     * Creates a new note.
     * 
     * @param       int idModule Module id to which the annotation was created
     * @param       int classOrder Class order in the module
     * @param       string $title Note's title
     * @param       string $content Note's content
     * 
     * @return      int New note id or -1 if note has not been created
     * 
     * @throws      \InvalidArgumentException If title or content is empty or 
     * if module id or class order or student id provided in the constructor is
     * empty, less than or equal to zero
     */
    public function new(int $idModule, int $classOrder, string $title, string $content) : int
    {
        $this->validateLoggedStudent();
        $this->validateModuleId($idModule);
        $this->validateClassOrder($classOrder);
        $this->validateTitle($title);
        $this->validateContent($content);
        $this->withQuery("
            INSERT INTO notebook
            (id_student, id_module, class_order, title, content, date)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $this->runQueryWithArguments(
            $this->idStudent, 
            $idModule, 
            $classOrder, 
            $title, 
            $content
        );
        
        return $this->parseNewResponseQuery();
    }

    private function validateTitle($title)
    {
        if (empty($title)) {
            throw new \InvalidArgumentException("Title cannot be empty");
        }
    }

    private function validateContent($content)
    {
        if (empty($content)) {
            throw new \InvalidArgumentException("Content cannot be empty");
        }
    }

    private function parseNewResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return -1;
        }

        return ((int) $this->db->lastInsertId());
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
        $this->validateLoggedStudent();
        $this->validateNote($note);
        $this->withQuery("
            UPDATE  notebook
            SET     title = ?, content = ?
            WHERE   id_student = ? AND id_note = ? 
        ");
        $this->runQueryWithArguments(
            $note->getTitle(),
            $note->getContent(),
            $this->idStudent, 
            $note->getId()
        );

        return $this->hasResponseQuery();
    }

    private function validateNote($note)
    {
        if (empty($note)) {
            throw new \InvalidArgumentException("Note cannot be empty");
        }
    }
    
    /**
     * Removes a note.
     *
     * @param       int idNote Note id to be removed
     *
     * @return      bool If note has been successfully removed.
     *
     * @throws      \InvalidArgumentException If note id or student id provided in the
     * constructor is empty, less than or equal to zero
     */
    public function delete(int $idNote) : bool
    {
        $this->validateLoggedStudent();
        $this->validateNoteId($idNote);
        $this->withQuery("
            DELETE FROM notebook
            WHERE id_student = ? AND id_note = ?
        ");
        $this->runQueryWithArguments($this->idStudent, $idNote);
            
        return $this->hasResponseQuery();
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
        $this->validateLoggedStudent();
        $this->withQuery($this->buildGetAllQuery($limit, $offset));
        $this->runQueryWithArguments($this->idStudent);
        
        return $this->parseNotebooksResponseQuery();
    }

    private function buildGetAllQuery($limit, $offset)
    {
        $query = "
            SELECT  *, 
                    notebook.title AS notebook_title, 
                    videos.title AS videos_title
            FROM    notebook JOIN videos USING (id_module, class_order)
            WHERE   id_student = ?
        ";
        
        if ($limit > 0) {
            if ($offset > 0) {
                $query .= " LIMIT ".$offset.",".$limit;
            }
            else {
                $query .= " LIMIT ".$limit;
            }
        }

        return $query;
    }
    
    /**
     * Gets total number of notes that a student has.
     * 
     * @return      int Total notes
     */
    public function count() : int
    {
        $this->withQuery("
            SELECT  COUNT(*) AS total
            FROM    notebook
            WHERE   id_student = ".$this->idStudent
        );
        $this->runQueryWithoutArguments();

        return ((int) $this->getResponseQuery()['total']);
    }
    
    /**
     * Gets total number of notes that a student created in a class.
     *
     * @param       int idModule Module id to which the annotation belongs
     * @param       int classOrder Class order in the module
     *
     * @return      int Total notes
     */
    public function countAllFromClass(int $idModule, int $classOrder) : int
    {
        $this->withQuery("
            SELECT  COUNT(*) AS total
            FROM    notebook JOIN videos USING (id_module, class_order)
            WHERE   id_student = ".$this->idStudent." AND 
                    id_module = ".$idModule." AND 
                    class_order = ".$classOrder
        );
        $this->runQueryWithoutArguments();
        
        return ((int) $this->getResponseQuery()['total']);
    }
}