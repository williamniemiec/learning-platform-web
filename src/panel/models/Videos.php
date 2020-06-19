<?php
namespace models;

use core\Model;


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
    public function getVideoFromClass($id_class)
    {
        if (empty($id_class) || $id_class <= 0) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT * 
            FROM videos 
            WHERE id_class = ?
        ");
        $sql->execute(array($id_class));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch(\PDO::FETCH_ASSOC);
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
    public function add($classId, $title, $description, $url)
    {
        if (empty($title) || empty($description) || empty($url)) { return false; }
        
        $sql = $this->db->prepare("
            INSERT INTO videos 
            (id_class,title,description,url) 
            VALUES (?,?,?,?)
        ");
        $sql->execute(array($classId,$title, $description, $url));
         
        return $sql->rowCount() > 0;
    }
    
    /**
     * Gets a video class.
     * 
     * @param       int $id_video Class id
     * 
     * @return      array Video class
     */
    public function get($id_video)
    {
        if (empty($id_video)) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT * 
            FROM videos 
            WHERE id = ?
        ");
        $sql->execute(array($id_video));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch(\PDO::FETCH_ASSOC);
        }
        
        return $response;
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
    public function edit($id_video, $title, $description, $url)
    {
        if (empty($id_video) || empty($title) || 
            empty($description) || empty($url)) { 
            return false; 
        }
        
        $sql = $this->db->prepare("
            UPDATE videos 
            SET title = ?, description = ?, url = ? 
            WHERE id = ?
        ");
        $sql->execute(array($title, $description, $url, $id_video));
        
        return $sql->rowCount() > 0;
    }
}