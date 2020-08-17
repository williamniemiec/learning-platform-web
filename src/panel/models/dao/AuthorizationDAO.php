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
     * @param       int $id_admin [Optional] Admin id
     * 
     * @return      \models\Authorization Admin authorization
     * 
     * @throws      \InvalidArgumentException If admin id is empty, less than
     * or equal to zero
     */
    public function get(int $id_admin) : Authorization
    {
        if (empty($id_admin) || $id_admin <= 0)
            throw new \InvalidArgumentException("Admin id cannot be empty, ".
                "less than or equal to zero");
        
        $sql = $this->db->query("
            SELECT  id_authorization, authorization.name, level
            FROM    authorization JOIN admins USING (id_authorization)
            WHERE   id_admin = ".$id_admin
        );
        
        $authorization = $sql->fetch();
        
        return new Authorization(
            (int)$authorization['id_authorization'], 
            $authorization['name'], 
            (int)$authorization['level']
        );
    }
    
    /**
     * Gets all registered authorizations.
     * 
     * @return      Authorization[] Authorizations or empty array if there are
     * no registered authorizations
     */
    public function getAll() : array
    {
        $response = array();
        
        $sql = $this->db->query("
            SELECT  *
            FROM    authorization
        ");
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $authorization) {
                $response[] = new Authorization(
                    $authorization['id_authorization'],
                    $authorization['name'], 
                    (int)$authorization['level']
                );
            }
        }
        
        return $response;
    }
}