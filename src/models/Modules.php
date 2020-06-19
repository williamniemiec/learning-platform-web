<?php
namespace models;

use core\Model;


/**
 * Responsible for managing modules from a course.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class Modules extends Model
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates modules manager.
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
     * Gets modules from a course.
     *
     * @param       int $id_course Course id
     *
     * @return      array Modules from this course
     */
    public function getModules($id_course)
    {
        if (empty($id_course) || $id_course <= 0) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT * 
            FROM modules 
            WHERE id_course = ?
        ");
        $sql->execute(array($id_course));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetchAll(\PDO::FETCH_ASSOC);
            
            $classes = new Classes();
            
            for ($i=0; $i<count($response); $i++) {
                $response[$i]['classes'] = $classes->getClassesFromModule($response[$i]['id']);
            }
        }
        
        return $response;
    }
}