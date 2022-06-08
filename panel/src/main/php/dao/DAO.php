<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use util\IllegalAccessException;


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

    protected function validateLoggedAdmin()
    {
        if (empty($this->admin) || $this->admin->getId() <= 0) {
            throw new \InvalidArgumentException("Admin logged in must be ".
                                                "provided in the constructor");
        }
    }

    protected function validateAuthorization(...$levels)
    {
        if (!in_array($this->admin->getAuthorization()->getLevel(), $levels)) {
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
        }
    }
}
