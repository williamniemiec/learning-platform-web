<?php
namespace models;

use core\Model;


/**
 *
 */
class Doubts extends Model
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
    public function sendDoubt($id_user, $id_class, $text)
    {
        if (empty($id_class) && $id_class <= 0 || 
            empty($id_user) && $id_user <= 0) { return false; }
        
        $classes = new Classes();
        $students = new Students();
        
        if (!$classes->exist($id_class) || !$students->exist($id_user)) { return false; }
        
        $sql = $this->db->prepare("INSERT INTO doubts (id_user,id_class,text,`date-publication`) VALUES (?,?,?,NOW())");
        $sql->execute(array($id_user,$id_class,$text));
        
        return $sql->rowCount() > 0;
    }
    
    public function getDoubts($id_class)
    {
        if (empty($id_class) && $id_class <= 0) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT 
                *,
                (select students.name from students where students.id = doubts.id_user) as name 
            FROM doubts 
            WHERE id_class = ?
        ");
        $sql->execute(array($id_class));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        return $response;
    }
    
    public function delete($id_comment)
    {
        if (empty($id_comment) && $id_comment <= 0) { return false; }
        
        $sql = $this->db->prepare("DELETE FROM doubts WHERE id = ?");
        $sql->execute(array($id_comment));
        
        return $sql->rowCount() > 0;
    }
}