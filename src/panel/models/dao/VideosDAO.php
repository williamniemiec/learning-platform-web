<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Video;


/**
 * Responsible for managing 'videos' table.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class VideosDAO
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'videos' table manager.
     *
     * @param       Database $db Database
     */
    public function __construct(Database $db)
    {
        $this->db = $db->getConnection();
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets video from a class.
     *
     * @param       int $id_module Module id that the class belongs to
     * @param       int $class_order Class order inside the module that it 
     * belongs to
     *
     * @return      Video Video class or null if class does not exist
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function get(int $id_module, int $class_order) : Video
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Invalid module id");
            
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Invalid class order");
            
        $response = null;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  * 
            FROM    videos 
            WHERE   id_module = ? AND class_order = ?
        ");
        
        // Executes query
        $sql->execute(array($id_module, $class_order));
        
        // Parses results
        if ($sql->rowCount() > 0) {
            $class = $sql->fetch(\PDO::FETCH_ASSOC);
            
            $response = new Video(
                $class['id_module'],
                $class['class_order'],
                $class['title'],
                $class['videoID'],
                $class['length'],
                $class['description']
            ); 
        }
        
        return $response; 
    }
    
    /**
     * Adds a new video class.
     * 
     * @param       int $id_module Module id
     * @param       int $id_module Class order in module
     * @param       string $title Class title
     * @param       string $url Video url (must be from YouTube)
     * @param       int $length Video length
     * @param       string $description [Optional] Class description
     * 
     * @return      bool If class was sucessfully added
     */
    public function add(int $id_module, int $class_order, string $title, 
        string $videoID, int $length, string $description = '') : bool
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Invalid module id");
            
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Invalid class order");
        
        if (empty($title))
            throw new \InvalidArgumentException("Title cannot be empty");
            
        if (empty($videoID))
            throw new \InvalidArgumentException("Video ID cannot be empty");
        
        if (empty($length) || $length <= 0)
            throw new \InvalidArgumentException("Invalid length");
        
        if (empty($description)) {
            $sql = $this->db->prepare("
                INSERT INTO videos
                (id_module, class_order, title, videoID, length)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            // Executes query
            $sql->execute(array($id_module, $class_order, $title, $videoID, $length));
        }
        else {
            $sql = $this->db->prepare("
                INSERT INTO videos
                (id_module, class_order, title, description, videoID, length)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            // Executes query
            $sql->execute(array($id_module, $class_order, $title, $description, $videoID, $length));
        }
        
         
        return $sql && $sql->rowCount() > 0;
    }
    
    
    /**
     * Edits a video class.
     * 
     * @param       int $id_video Class id
     * @param       string $title New class title
     * @param       string $videoID New class URL (must be from YouTube)
     * @param       string $description [Optional] New class description 
     * 
     * @return      boolean If class was sucessfully edited
     */
    public function edit($id_module, $class_order, $title, $videoID, $length, $description = '')
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Invalid module id");
            
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Invalid class order");
        
        if (empty($title))
            throw new \InvalidArgumentException("Title cannot be empty");
        
        if (empty($videoID))
            throw new \InvalidArgumentException("Video ID cannot be empty");
                    
        if (empty($length) || $length <= 0)
            throw new \InvalidArgumentException("Invalid length");
        
        if (empty($description)) {
            $sql = $this->db->prepare("
                UPDATE videos
                (title, videoID, length)
                WHERE id_module = ? AND class_order = ?
            ");
                
            $sql->execute(array($title, $videoID, $length, $id_module, $class_order));
        }
        else {
            $sql = $this->db->prepare("
                UPDATE videos
                (title, description, videoID, length)
                WHERE id_module = ? AND class_order = ?
            ");
            
            $sql->execute(array($title, $description, $videoID, $length, $id_module, $class_order));
        }
        
        return $sql->rowCount() > 0;
    }
}