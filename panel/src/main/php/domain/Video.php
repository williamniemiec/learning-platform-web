<?php
declare (strict_types=1);

namespace panel\domain;


/**
 * Responsible for representing video-type classes.
 */
class Video extends ClassType
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $title;
    private $description;
    private $videoId;
    private $length;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a video-type class.
     * 
     * @param       int idModule Module id that the class belongs to
     * @param       int classOrder Class order inside the module to which the
     * class belongs
     * @param       string $title Class title
     * @param       string video_id Class video URL (must be from YouTube). 
     * Must be in the following format:
     * <ul>
     *  <li><b>Youtube URL:</b> https://www.youtube.com/watch?v=abcdefghijk</li>
     *  <li><b>videoID:</b> abcdefghijk</li>
     * </ul>
     * @param       int $length Video length
     * @param       string $description [Optional] Class description
     */
    public function __construct(
        int $idModule, 
        int $classOrder, 
        string $title,
        string $videoId, 
        int $length, 
        ?string $description = ''
    )
    {
        $this->idModule = $idModule;
        $this->classOrder = $classOrder;
        $this->title = $title;
        $this->videoId = $videoId;
        $this->length = $length;
        $this->description = empty($description) ? '' : $description;
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
        return $this->videoId;
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
    
    
    //-------------------------------------------------------------------------
    //        Serialization
    //-------------------------------------------------------------------------
    /**
     * {@inheritDoc}
     * @see \JsonSerializable::jsonSerialize()
     *
     * @Override
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $json['type'] = 'video';
        $json['title'] = $this->title;
        $json['description'] = $this->description;
        $json['videoID'] = $this->videoId;
        $json['length'] = $this->length;
        
        return $json;
    }
}