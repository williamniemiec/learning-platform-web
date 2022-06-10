<?php
/**
 * Copyright (c) William Niemiec.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Admin;
use domain\Authorization;
use domain\enum\GenreEnum;


/**
 * Responsible for managing 'admins' table.
 */
class AdminsDAO extends DAO
{
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
        parent::__construct($db);
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
        $this->validateAdminId($idAdmin);
        $this->withQuery("
            SELECT  *, 
                    admins.name AS admin_name, 
                    authorization.name AS authorization_name
            FROM    admins JOIN authorization USING (id_authorization)
            WHERE   id_admin = ?
        ");
        $this->runQueryWithArguments($idAdmin);
        
        return $this->parseGetResponseQuery();
    }

    private function validateAdminId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Admin id cannot be empty or".
                                                "less than or equal to zero");
        }
    }

    private function parseGetResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return null;
        }

        $adminRaw = $this->getResponseQuery();
        
        return new Admin(
            (int) $adminRaw['id_admin'], 
            new Authorization($adminRaw['authorization_name'], (int) $adminRaw['level']), 
            $adminRaw['admin_name'], 
            new GenreEnum($adminRaw['genre']), 
            new \DateTime($adminRaw['birthdate']), 
            $adminRaw['email']
        );
    }
}