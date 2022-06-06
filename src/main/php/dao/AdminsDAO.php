<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Admin;
use domain\Authorization;
use domain\enum\GenreEnum;


/**
 * Responsible for managing 'admins' table.
 */
class AdminsDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $db;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'admins' table manager.
     *
     * @param       Database $db Database
     *
     * @apiNote     It will connect to the database when it is instantiated
     */
    public function __construct(Database $db)
    {
        $this->db = $db->getConnection();
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets information about an admin.
     * 
     * @param       int idAdmin Admin id
     * 
     * @return      Admin Admin with the given id or null if there us no admin
     * with the given id
     * 
     * @throws      \InvalidArgumentException If admin id is empty, less than
     * or equal to zero
     */
    public function get($idAdmin) : Admin
    {
        if (empty($idAdmin) || $idAdmin <= 0) {
            throw new \InvalidArgumentException("Admin id cannot be less than ".
                "or equal to zero");
        }
        
        $response = null;

        // Query construction
        $sql = $this->db->prepare("
            SELECT  *, 
                    admins.name AS admin_name, 
                    authorization.name AS authorization_name
            FROM    admins JOIN authorization USING (id_authorization)
            WHERE   id_admin = ?
        ");
        
        // Executes query
        $sql->execute(array($idAdmin));
        
        // Parses result
        if ($sql->rowCount() > 0) {
            $admin = $sql->fetch();
            
            $response = new Admin(
                (int) $admin['id_admin'], 
                new Authorization($admin['authorization_name'], (int) $admin['level']), 
                $admin['admin_name'], 
                new GenreEnum($admin['genre']), 
                new \DateTime($admin['birthdate']), 
                $admin['email']
            );
        }
        
        return $response;
    }
}