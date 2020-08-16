<?php
declare (strict_types=1);

namespace database;


/**
 * Class responsible for connecting to the database.
 * 
 * @apiNote     The documentation was obtained in conjunction with the PDO class.
 * 
 * @link https://www.php.net/manual/pt_BR/book.pdo.php
 */
abstract class Database
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $database = "";
    private $charset = "";
    private $host = "";
    private $username = "";
    private $password = "";
    protected $conn; 
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Connects to the database.
     * 
     * @return      Database Connected database
     */
    public function getConnection() : Database
    {
        if (empty($this->conn)) {
            $this->connect();
        }
        
        return $this;
    }
    
    /**
     * Set an attribute.
     * 
     * @param       int $attribute Attribute to be set
     * @param       mixed $value Attribute's value 
     * 
     * @return      bool True on success or false on failure
     */
    public abstract function setAttribute(int $attribute, $value) : bool;
    
    /**
     * Executes an SQL statement, returning a result set as an object.
     *
     * @return      DatabaseStatement Returns a statement object, or null on 
     * failure.
     */
    public abstract function query(string $statement) : ?DatabaseStatement;
    
    /**
     * Prepares a statement for execution and returns a statement object.
     *
     * @param       string $statement This must be a valid SQL statement 
     * template for the target database server.
     *
     * @return      DatabaseStatement If the database server successfully 
     * prepares the statement, returns a PDOStatement object. If the database
     * server cannot successfully prepare the statement, returns null;
     */
    public abstract function prepare(string $statement) : ?DatabaseStatement;
    
    /**
     * Returns the ID of the last inserted row or sequence value.
     *
     * @return       string If a sequence name was not specified for the name,
     * returns a string representing the row ID of the last row that was
     * inserted into the database. If a sequence name was specified for the
     * name parameter, returns a string representing the last value retrieved
     * from the specified name.
     */
    public abstract function lastInsertId(string $name = '') : string;
    
    /**
     * Initiates a transaction.
     * 
     * @return      bool True on success or false on failure
     */
    public abstract function beginTransaction() : bool;
    
    /**
     * Commits a transaction.
     * 
     * @return      bool True on success or false on failure
     */
    public abstract function commit() : bool;
    
    /**
     * Rolls back a transaction.
     * 
     * @return      bool True on success or false on failure
     */
    public abstract function rollback() : bool;
    
    /**
     * Checks if inside a transaction.
     * 
     * @return      bool True if a transaction is currently active, and false 
     * if not.
     */
    public abstract function inTransaction() : bool;
    
    /**
     * Gets database instance that will be used to connect to the database.
     */
    protected abstract function getInstance();
    
    /**
     * Sets environment and connects to the database.
     */
    private function connect() : void
    {
        if (ENVIRONMENT == "development") {
            $this->host = "127.0.0.1";
            $this->charset = "utf8";
            $this->username = "root";
            $this->password = "";
            $this->database = "learning_platform";
        }
        else {
            $this->host = "";
            $this->charset = "";
            $this->username = "";
            $this->password = "";
            $this->database = "";
        }
        
        $this->conn = $this->getInstance();
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    protected function getDatabase()
    {
        return $this->database;
    }
    
    protected function getCharset()
    {
        return $this->charset;
    }
    
    protected function getHost()
    {
        return $this->host;
    }
    
    protected function getUsername()
    {
        return $this->username;
    }
    
    protected function getPassword()
    {
        return $this->password;
    }
}
