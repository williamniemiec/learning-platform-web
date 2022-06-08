<?php
declare (strict_types=1);

namespace panel\repositories\pdo;


use panel\repositories\Database;
use panel\repositories\DatabaseStatement;


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
    public function prepare(string $statement) : ?DatabaseStatement
    {
        $statement = $this->conn->prepare($statement);
        
        return $statement ? new PDODatabaseStatement($this, $statement) : null;
    }

    /**
     * {@inheritdoc}
     * @Override
     */
    public function query(string $statement) : ?DatabaseStatement
    {
        $statement = $this->conn->query($statement);
        
        return $statement ? new PDODatabaseStatement($this, $statement) : null;
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