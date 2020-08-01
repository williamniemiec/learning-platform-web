<?php
declare (strict_types=1);

namespace models;


/**
 * Responsible for representing notebook notes.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Note
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $id_note;
    private $title;
    private $content;
    private $date;
    private $class;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a notebook note.
     *
     * @param       int $id_note Note id
     * @param       string $title Note title
     * @param       string $content Note content
     * @param       string $date Note date
     * @param       Video $class Class to which the note belongs
     */
    public function __construct(int $id_note, string $title, string $content, 
        string $date, Video $class)
    {
        $this->id_note = $id_note;
        $this->title = $title;
        $this->content = $content;
        $this->date = $date;
        $this->class = $class;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets annotation id.
     * 
     * @return      int Annotation id
     */
    public function getId() : int
    {
        return $this->id_note;
    }
    
    /**
     * Gets annotation title.
     *
     * @return      string Note title
     */
    public function getTitle() : string
    {
        return $this->title;
    }
    
    /**
     * Gets annotation content.
     *
     * @return      string Note content
     */
    public function getContent() : string
    {
        return $this->content;
    }
    
    /**
     * Gets annotation creation date.
     *
     * @return      string Creation date
     */
    public function getCreationDate() : string
    {
        return $this->date;
    }
    
    /**
     * Gets class associated with the annotation.
     *
     * @return      Video Class associated with the annotation.
     */
    public function getClass() : string
    {
        return $this->class;
    }
}