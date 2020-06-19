<?php
namespace models;

use core\Model;


/**
 * Responsible for managing students historic.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class Historic extends Model
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates students historic manager.
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
     * Gets watched classes by a student in a course.
     * 
     * @param       int $id_student Student id
     * @param       int $id_course Course id
     * 
     * @return      array Watched classes
     */
    public function getWatchedClasses($id_student, $id_course)
    {
        $sql = $this->db->prepare("
            SELECT COUNT(*) AS watchedClasses
            FROM historic
            WHERE id_student = ? AND id_class IN (
                select classes.id 
                from classes 
                where classes.id_course = ?
            )
        ");
        $sql->execute(array($id_student, $id_course));
        
        return $sql->fetch()['watchedClasses'];
    }
}