<?php
namespace models;

use core\Model;
use models\obj\Video;


/**
 * Responsible for managing video classes.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class Videos extends Model
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates video classes manager.
     *
     * @apiNote     It will connect to the database when it is instantiated
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets video from a class.
     * 
     * @param       int $id_class Class id
     * 
     * @return      array Class video
     */
    public function get($id_module, $class_order)
    {
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT  *
            FROM    videos
            WHERE   id_module = ? AND class_order = ?
        ");
        $sql->execute(array($id_module, $class_order));
        
        if ($sql->rowCount() > 0) {
            $class = $sql->fetch(\PDO::FETCH_ASSOC);
            
            $response = new Video(
                $class['id_module'],
                $class['class_order'],
                $class['title'],
                $class['videoID'],
                $class['length']
            );
        }
        
        return $response;
    }
    
    /**
     * Adds a new video class.
     * 
     * @param       int $classId Class id
     * @param       string $title Class title
     * @param       string $description Class description
     * @param       string $url Video url (must be from YouTube)
     * 
     * @return      boolean If class was sucessfully added
     */
    public function add($id_module, $class_order, $title, $description, $url)
    {
        if (empty($title) || empty($description) || empty($url)) { return false; }
        
        $sql = $this->db->prepare("
            INSERT INTO videos 
            (id_module, class_order, title, description, url, length) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $sql->execute(array($id_module, $class_order, $title, $description, $url));
         
        return $sql->rowCount() > 0;
    }
    
    
    /**
     * Edits a video class.
     * 
     * @param       int $id_video Class id
     * @param       string $title New class title
     * @param       string $description New class description 
     * @param       string $url New class URL (must be from YouTube)
     * 
     * @return      boolean If class was sucessfully edited
     */
    public function edit($id_module, $class_order, $title, $description, $url, $length)
    {
        if (empty($id_module) || empty($title) || 
            empty($description) || empty($url)) { 
            return false; 
        }
        
        $sql = $this->db->prepare("
            UPDATE videos 
            (title, description, url, length) 
            WHERE id_module = ? AND class_order = ?
        ");
        $sql->execute(array($title, $description, $url, $length, $id_module, $class_order));
        
        return $sql->rowCount() > 0;
    }
}