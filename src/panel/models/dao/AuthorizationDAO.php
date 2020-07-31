<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Authorization;


/**
 * Responsible for managing 'authorization' table.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class AuthorizationDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $db;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'authorization' table manager.
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
     * Gets admin authorization.
     * 
     * @param       int $id_admin Admin id
     * 
     * @return      \models\Authorization Admin authorization 
     * 
     * @throws      \InvalidArgumentException If admin id is invalid 
     */
    public function getAuthorization(int $id_admin) : Authorization
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Invalid admin id");
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    authorization
            WHERE   id_authorization = (select  id_authorization
                                        from    admins
                                        where   id_admin = ?)
        ");
        
        // Executes query
        $sql->execute(array($id_admin));
        
        $sql = $sql->fetch();
        
        return new AuthorizationDAO(
            $sql['id_authorization'],
            $sql['name'], 
            $sql['level']
        );
    }
    
}