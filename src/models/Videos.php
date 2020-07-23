<?php
namespace models;

use core\Model;
use models\obj\Video;


/**
 * Responsible for managing videos table.
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
     * Creates videos table manager.
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
    
    public function getFromModule($id_module)
    {
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT  *
            FROM    videos
            WHERE   id_module = ?
        ");
        
        $sql->execute(array($id_module));
        
        if ($sql->rowCount() > 0) {
            $classes =  $sql->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($classes as $class) {
                $response[] = new Video(
                    $class['id_module'], 
                    $class['class_order'], 
                    $class['title'], 
                    $class['videoID'], 
                    $class['length']
                );
            }
        }
        
        return $response;
    }
    
    /**
     * Marks a class as watched by a student.
     *
     */
    public function markAsWatched($id_student, $id_module, $class_order)
    {
        if (empty($id_student) || $id_student <= 0)
            return;
            
        if (empty($id_module) || $id_module <= 0)
            return;
            
        if ($class_order <= 0)
            return;
                
        $sql = $this->db->prepare("
            INSERT INTO student_historic
            (id_student, id_module, class_order, 0, date)
            VALUES (?, ?, ?, NOW())
        ");
                    
        $sql->execute(array($id_student, $id_module, $class_order));
    }
}