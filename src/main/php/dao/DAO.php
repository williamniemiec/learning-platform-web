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


/**
 * Represents a Data Access Object.
 */
abstract class DAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    protected $db;
    protected $sql;
    private $query;


    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    protected function __construct(Database $db)
    {
        $this->db = $db->getConnection();
    }


    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    protected function withQuery($query)
    {
        $this->query = $query;
    }

    protected function runQueryWithArguments(...$bindArguments)
    {
        $this->sql = $this->db->prepare($this->query);

        if (is_array($bindArguments[0])) {
            $this->sql->execute(...$bindArguments);
        }
        else {
            $this->sql->execute($bindArguments);
        }
    }

    protected function runQueryWithoutArguments()
    {
        $this->sql = $this->db->query($this->query);
    }

    protected function hasResponseQuery()
    {
        return $this->sql && ($this->sql->rowCount() > 0);
    }

    protected function getResponseQuery()
    {
        return $this->sql->fetch();
    }

    protected function getAllResponseQuery()
    {
        return $this->sql->fetchAll();
    }
}
