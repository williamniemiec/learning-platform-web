<?php
declare (strict_types=1);

namespace database\pdo;


use database\Database;
use database\DatabaseStatement;


/**
 * Represents a PDO database statement.
 */
class PDODatabaseStatement extends DatabaseStatement
{
    private $db;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates database statement for PDO.
     *
     * @param       Database $db PDO database
     * @param       \PDOStatement $statement PDO statement
     */
    public function __construct(Database $db, \PDOStatement $statement)
    {
        $this->statement = $statement;
        $this->db = $db;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * {@inheritdoc}
     * @Override
     */
    public function execute(array $bindArguments = null): bool
    {
        return $this->statement->execute($bindArguments);
    }
    
    
    /**
     * {@inheritdoc}
     * @Override
     */
    public function fetchAll(bool $withTableName = false): array
    {
        if ($withTableName)
            $this->db->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES, true);
            
            $result = $this->statement->fetchAll(\PDO::FETCH_ASSOC);
            
            return $result == false ? array() : $result;
    }
    
    /**
     * {@inheritdoc}
     * @Override
     */
    public function fetch(bool $withTableName = false)
    {
        if ($withTableName)
            $this->db->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES, true);
            
            $result = $this->statement->fetch(\PDO::FETCH_ASSOC);
            
            return $result == false ? array() : $result;
    }
    
    /**
     * {@inheritdoc}
     * @Override
     */
    public function rowCount(): int
    {
        return empty($this->statement) ? 0 : $this->statement->rowCount();
    }
}