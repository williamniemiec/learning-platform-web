<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Action;


/**
 * Responsible for managing 'actions' table.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class ActionsDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $db;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'actions' table manager.
     * 
     * @param       Database $db Database
     */
    public function __construct(Database $db)
    {
        $this->db = $db->getConnection();
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Registers new action.
     * 
     * @param       int $id_admin Admin who performed the action
     * @param       Action $action Action description
     * 
     * @return      bool If the action has been sucessfully registered
     * 
     * @throws      \InvalidArgumentException If action is empty or no action was 
     * selected or admin id is empty, less than or equal to zero
     */
    public function register(int $id_admin, Action $action) : bool
    {
        if (empty($id_admin) || $id_admin <= 0)
            throw new \InvalidArgumentException("Admin id cannot be null or less ".
                "than or equal to zero");
        
        if (empty($action) || empty($action->get()))
            throw new \InvalidArgumentException("Action cannot be empty");
            
        // Query construction
        $sql = $this->db->query("
            INSERT INTO actions
            (id_admin, description, date)
            VALUES (?, ?, NOW())
        ");
        
        // Executes query
        $sql->execute(array($id_admin, $action->get()));
        
        return $sql && $sql->rowCount() > 0;
    }
}