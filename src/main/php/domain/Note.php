<?php
/**
 * Copyright (c) William Niemiec.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

declare (strict_types=1);

namespace domain;

use DateTime;


/**
 * Responsible for representing notebook notes.
 */
class Note implements \JsonSerializable
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
     * @param       int idNote Note id
     * @param       string $title Note title
     * @param       string $content Note content
     * @param       DateTime $date Note date
     * @param       Video $class Class to which the note belongs
     */
    public function __construct(
        int $idNote, 
        string $title, 
        string $content, 
        DateTime $date, 
        Video $class
    )
    {
        $this->id_note = $idNote;
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
     * @return      DateTime Creation date
     */
    public function getCreationDate() : DateTime
    {
        return $this->date;
    }
    
    /**
     * Gets class associated with the annotation.
     *
     * @return      Video Class associated with the annotation.
     */
    public function getClass() : Video
    {
        return $this->class;
    }
    
    
    //-------------------------------------------------------------------------
    //        Serialization
    //-------------------------------------------------------------------------
    /**
     * {@inheritDoc}
     * @see \JsonSerializable::jsonSerialize()
     *
     * @Override
     */
    public function jsonSerialize(): array
    {
        return array(
            'id' => $this->id_note,
            'title' => $this->title,
            'content' => $this->content,
            'date' => $this->date->format("Y/m/d H:i:s"),
            'class' => $this->class
        );
    }
}