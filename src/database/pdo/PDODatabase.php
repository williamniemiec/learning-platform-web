<?php
declare (strict_types=1);

namespace database\pdo;


use database\Database;
use database\DatabaseStatement;


/**
 * Connects to the database via PDO.
 * 
 * @link https://www.php.net/manual/pt_BR/book.pdo.php
 */
abstract class PDODatabase extends Database
{
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * {@inheritdoc}
     * @Override
     */
    public function setAttribute(int $attribute, $value) : bool
    {
        return $this->conn->setAttribute($attribute, $value);
    }
    
    /**
     * {@inheritdoc}
     * @Override
     */
    public function prepare(string $statement) : DatabaseStatement
    {
        $statement = $this->conn->prepare($statement);
        
        
        return $statement == false ? null : new PDODatabaseStatement($this, $statement);
    }

    /**
     * {@inheritdoc}
     * @Override
     */
    public function query(string $statement) : DatabaseStatement
    {
        $statement = $this->conn->query($statement);
        
        
        return $statement == false ? null : new PDODatabaseStatement($this->conn, $statement);
    }

    /**
     * {@inheritdoc}
     * @Override
     */
    public function lastInsertId(string $name = null): string
    {
        return $this->conn->lastInsertId($name);
    }
    
    /**
     * {@inheritdoc}
     * @Override
     */
    protected abstract function getInstance();
}