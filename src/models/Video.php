<?php
declare (strict_types=1);

namespace models;


/**
 * Responsible for representing video-type classes.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Video extends _Class
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $title;
    private $description;
    private $videoID;
    private $length;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a video-type class.
     * 
     * @param       int $id_module Module id that the class belongs to
     * @param       int $class_order Class order inside the module to which the
     * class belongs
     * @param       string $title Class title
     * @param       string $videoID Class video URL (must be from YouTube). 
     * Must be in the following format:
     * <ul>
     *  <li><b>Youtube URL:</b> https://www.youtube.com/watch?v=abcdefghijk</li>
     *  <li><b>videoID:</b> abcdefghijk</li>
     * </ul>
     * @param       int $length Video length
     * @param       string $description [Optional] Class description
     */
    public function __construct(int $id_module, int $class_order, string $title,
        string $videoID, int $length, string $description = '')
    {
        $this->id_module = $id_module;
        $this->class_order = $class_order;
        $this->title = $title;
        $this->videoID = $videoID;
        $this->length = $length;
        $this->description = $description;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets class title.
     * 
     * @return      string Class title
     */
    public function getTitle() : string
    {
        return $this->title;
    }
    
    /**
     * Gets video id.
     * 
     * @return      string Video id
     */
    public function getVideoId() : string
    {
        return $this->videoID;
    }
    
    /**
     * Gets video length (in minutes).
     * 
     * @return      int Video length
     */
    public function getLength() : int
    {
        return $this->length;
    }
    
    /**
     * Gets video description.
     * 
     * @return      string Video description or empty string if there is no
     * description
     */
    public function getDescription() : string
    {
        return $this->description;
    }
}