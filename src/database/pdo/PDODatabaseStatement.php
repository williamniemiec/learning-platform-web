<?php
declare (strict_types=1);

namespace database\pdo;


use database\DatabaseStatement;


/**
 * Represents a PDO database statement.
 */
class PDODatabaseStatement extends DatabaseStatement
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates database statement for PDO.
     *
     * @param       \PDOStatement $statement PDO statement
     */
    public function __construct(\PDOStatement $statement)
    {
        $this->statement = $statement;    
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
    public function fetchAll(): array
    {
        $result = $this->statement->fetchAll(\PDO::FETCH_ASSOC);
        
        
        return $result == false ? array() : $result;
    }

    /**
     * {@inheritdoc}
     * @Override
     */
    public function fetch()
    {
        $result = $this->statement->fetch();
        
        
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