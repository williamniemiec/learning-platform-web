<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Video;
use models\util\IllegalAccessException;


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
    //        Attributes
    //-------------------------------------------------------------------------
    private $db;
    private $id_admin;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'videos' table manager.
     *
     * @param       Database $db Database
     * @param       int $id_admin [Optional] Admin id logged in
     */
    public function __construct(Database $db, int $id_admin = -1)
    {
        $this->db = $db->getConnection();
        $this->id_admin = $id_admin;
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
     * @throws      \InvalidArgumentException If module id or class order is 
     * empty or less than or equal to zero
     */
    public function get(int $id_module, int $class_order) : Video
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Class order cannot be empty ".
                "or less than or equal to zero");
            
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
            $class = $sql->fetch();
            
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
     * @param       Video $video Video to be added
     * 
     * @return      bool If class was successfully added
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to create new classes
     * @throws      \InvalidArgumentException If video is empty or if admin id
     * provided in the constructor is empty, less than or equal to zero
     */
    public function add(Video $video) : bool
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->getAuthorization()->getLevel() != 0 &&
            $this->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($video))
            throw new \InvalidArgumentException("Video cannot be empty");
        
        if (empty($video->getDescription())) {
            $sql = $this->db->prepare("
                INSERT INTO videos
                (id_module, class_order, title, videoID, length)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            // Executes query
            $sql->execute(array(
                $video->getModuleId(), 
                $video->getClassOrder(), 
                $video->getTitle(), 
                $video->getVideoId(), 
                $video->getLength()
            ));
        }
        else {
            $sql = $this->db->prepare("
                INSERT INTO videos
                (id_module, class_order, title, videoID, length, description)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            // Executes query
            $sql->execute(array(
                $video->getModuleId(),
                $video->getClassOrder(),
                $video->getTitle(), 
                $video->getVideoId(),
                $video->getLength(),
                $video->getDescription()
            ));
        }
        
         
        return $sql && $sql->rowCount() > 0;
    }
    
    
    /**
     * Updates a video class.
     * 
     * @param       Video $video Video to be added
     * 
     * @return      boolean If class has been successfully updated
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update classes
     * @throws      \InvalidArgumentException If video is empty or if admin id
     * provided in the constructor is empty, less than or equal to zero
     */
    public function update(Video $video)
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->getAuthorization()->getLevel() != 0 &&
            $this->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($video))
            throw new \InvalidArgumentException("Video cannot be empty");
        
        if (empty($video->getDescription())) {
            $sql = $this->db->prepare("
                UPDATE  videos
                SET     title = ?, videoID = ?, length = ?
                WHERE   id_module = ? AND class_order = ?
            ");
                
            $sql->execute(array(
                $video->getTitle(), 
                $video->getVideoId(),
                $video->getLength(),
                $video->getModuleId(),
                $video->getClassOrder()
            ));
        }
        else {
            $sql = $this->db->prepare("
                UPDATE  videos
                SET     title = ?, videoID = ?, length = ?, description = ?
                WHERE   id_module = ? AND class_order = ?
            ");
            
            $sql->execute(array(
                $video->getTitle(),
                $video->getVideoId(),
                $video->getLength(),
                $video->getDescription(),
                $video->getModuleId(),
                $video->getClassOrder()
            ));
        }
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Removes a video class.
     *
     * @param       int $id_module Module id to which the class belongs
     * @param       int $class_order Class order in the module
     *
     * @return      bool If class has been successfully removed
     *
     * @throws      IllegalAccessException If current admin does not have
     * authorization to delete classes
     * @throws      \InvalidArgumentException If module id, class order or 
     * admin id provided in the constructor is empty, less than or equal to
     * zero
     */
    public function delete(int $id_module, int $class_order) : bool
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->getAuthorization()->getLevel() != 0 &&
            $this->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
                
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Class order cannot be empty ".
                "or less than or equal to zero");
                    
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM videos
            WHERE id_module = ? AND class_order = ?
        ");
                    
        // Executes query
        $sql->execute(array($id_module, $class_order));
        
        return $sql->rowCount() > 0;
    }
}