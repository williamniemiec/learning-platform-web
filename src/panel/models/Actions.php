<?php
namespace models;

use core\Model;
use models\obj\Action;


/**
 * Responsible for managing actions table.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class Actions extends Model
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates actions table manager.
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Register new action.
     * 
     * @param       int $id_admin Admin who performed the action
     * @param       string $description Action description
     * 
     * @return      boolean If the action was sucessfully registered
     */
    public function register($id_admin, $description)
    {
        $sql = $this->db->query("
            INSERT INTO actions
            (id_admin, description, date)
            VALUES (?, ?, NOW())
        ");
        
        $sql->execute(array($id_admin, $description));
        
        return $sql->rowCount() > 0;
    }
}