<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Video;
use models\util\IllegalAccessException;
use models\Module;


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
            FROM    videos  NATURAL JOIN modules
            WHERE   id_module = ? AND class_order = ?
        ");
        
        // Executes query
        $sql->execute(array($id_module, $class_order));
        
        // Parses results
        if ($sql->rowCount() > 0) {
            $class = $sql->fetch();
            
            $response = new Video(
                new Module((int)$class['id_module'], $class['name']),
                (int)$class['class_order'],
                $class['title'],
                $class['videoID'],
                (int)$class['length'],
                $class['description']
            ); 
        }
        
        return $response; 
    }
    
    /**
     * Gets all registered video classes.
     * 
     * @return      Video[] Registered video classes or empty array if there are
     * no registered video classes
     */
    public function getAll() : array
    {
        $response = array();
        
        $sql = $this->db->query("
            SELECT  *
            FROM    videos NATURAL JOIN modules
        ");
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $class) {
                $response[] = new Video(
                    new Module((int)$class['id_module'], $class['name']),
                    (int)$class['class_order'],
                    $class['title'],
                    $class['videoID'],
                    (int)$class['length'],
                    $class['description']
                );
            }
        }
        
        return $response;
    }
    
    /**
     * Gets all video classes from a module.
     *
     * @param       int $id_module Module id
     *
     * @return      Video[] Classes that belongs to the module
     *
     * @throws      \InvalidArgumentException If module id is empty or less
     * than or equal to zero
     *
     * @Override
     */
    public function getAllFromModule(int $id_module) : array
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
            
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT  *
            FROM    videos NATURAL JOIN modules
            WHERE   id_module = ?
        ");
            
        $sql->execute(array($id_module));
        
        if ($sql->rowCount() > 0) {
            $classes =  $sql->fetchAll();
            
            foreach ($classes as $class) {
                $response[] = new Video(
                    new Module((int)$class['id_module'], $class['name']),
                    (int)$class['class_order'],
                    $class['title'],
                    $class['videoID'],
                    (int)$class['length'],
                    $class['description']
                );
            }
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
            $this->getAuthorization()->getLevel() != 1)
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
                $video->getModule()->getId(), 
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
                $video->getModule()->getId(),
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
            $this->getAuthorization()->getLevel() != 1)
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
                $video->getModule()->getId(),
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
            $this->getAuthorization()->getLevel() != 1)
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