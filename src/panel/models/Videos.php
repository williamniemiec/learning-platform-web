<?php
namespace models;

use core\Model;


/**
 *
 */
class Videos extends Model
{
    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    public function __construct()
    {
        parent::__construct();
    }
    
    
    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
    public function getVideoFromClass($id_class)
    {
        if (empty($id_class) || $id_class <= 0) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("SELECT * FROM videos WHERE id_class = ?");
        $sql->execute(array($id_class));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch(\PDO::FETCH_ASSOC);
        }
        
        return $response; 
    }
    
    public function add($classId, $title, $description, $url)
    {
        if (empty($title) || empty($description) || empty($url)) { return false; }
        
        $sql = $this->db->prepare("INSERT INTO videos (id_class,title,description,url) VALUES (?,?,?,?)");
        $sql->execute(array($classId,$title, $description, $url));
         
        return $sql->rowCount() > 0;
    }
    
    public function get($id_video)
    {
        if (empty($id_video)) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("SELECT * FROM videos WHERE id = ?");
        $sql->execute(array($id_video));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch(\PDO::FETCH_ASSOC);
        }
        
        return $response;
    }
    
    public function edit($id_video, $title, $description, $url)
    {
        if (empty($id_video) || empty($title) || 
            empty($description) || empty($url)) { 
            return false; 
        }
        
        $sql = $this->db->prepare("UPDATE videos SET title = ?, description = ?, url = ? WHERE id = ?");
        $sql->execute(array($title, $description, $url, $id_video));
        
        return $sql->rowCount() > 0;
    }
}