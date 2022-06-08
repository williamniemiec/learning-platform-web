<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Authorization;


/**
 * Responsible for managing 'authorization' table.
 */
class AuthorizationDAO extends DAO
{
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
        parent::__construct($db);
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets admin authorization.
     * 
     * @param       int $idAdmin [Optional] Admin id
     * 
     * @return      Authorization Admin authorization
     * 
     * @throws      \InvalidArgumentException If admin id is empty, less than
     * or equal to zero
     */
    public function get(int $idAdmin) : Authorization
    {
        $this->validateAdminId($idAdmin);
        
        $sql = $this->db->query("
            SELECT  id_authorization, authorization.name, level
            FROM    authorization JOIN admins USING (id_authorization)
            WHERE   id_admin = ".$idAdmin
        );
        
        $authorization = $sql->fetch();
        
        return new Authorization(
            (int)$authorization['id_authorization'], 
            $authorization['name'], 
            (int)$authorization['level']
        );
    }

    private function validateAdminId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Admin id cannot be empty or ".
                                                "less than or equal to zero");
        }
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