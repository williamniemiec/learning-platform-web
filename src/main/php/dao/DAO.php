<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;


abstract class DAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    protected $db;


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
    protected function runQueryWithArguments($sql, ...$bindArguments)
    {
        $sql->execute($bindArguments);
    }

    protected function runQueryWithoutArguments($query)
    {
        return $this->db->query($query);
    }

    protected function hasDatabaseChanged($sql)
    {
        return $sql && ($sql->rowCount() > 0);
    }
}
