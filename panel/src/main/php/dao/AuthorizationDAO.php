<?php
/**
 * Copyright (c) William Niemiec.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

declare (strict_types=1);

namespace panel\dao;


use panel\repositories\Database;
use panel\domain\Authorization;


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
        $this->withQuery("
            SELECT  id_authorization, authorization.name, level
            FROM    authorization JOIN admins USING (id_authorization)
            WHERE   id_admin = ".$idAdmin
        );
        $this->runQueryWithoutArguments();
        
        return $this->parseGetResponseQuery();
    }

    private function validateAdminId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Admin id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }

    private function parseGetResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return null;
        }

        $authorizationRaw = $this->getResponseQuery();
        
        return new Authorization(
            (int) $authorizationRaw['id_authorization'], 
            $authorizationRaw['name'], 
            (int) $authorizationRaw['level']
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
        $this->withQuery("
            SELECT  *
            FROM    authorization
        ");
        $this->runQueryWithoutArguments();
        
        return $this->parseGetAllResponseQuery();
    }

    private function parseGetAllResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $authorizations = array();
            
        foreach ($this->getAllResponseQuery() as $authorization) {
            $authorizations[] = new Authorization(
                $authorization['id_authorization'],
                $authorization['name'], 
                (int) $authorization['level']
            );
        }

        return $authorizations;
    }
}