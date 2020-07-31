<?php
namespace database;


/**
 * Class responsible for connecting to the database.
 */
class Database
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $host = "";
    private $username = "";
    private $password = "";
    private $database = "";
    private $conn = null; 
    private $charset = "";
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    public function __construct()
    {
        if (ENVIRONMENT == "development") {
            $this->host = '127.0.0.1';
            $this->charset = "utf8";
            $this->username = "root";
            $this->password = "";
            $this->database = "learning_platform";
        }
        else {
            $this->host = '127.0.0.1';
            $this->charset = "utf8";
            $this->username = "root";
            $this->password = "";
            $this->database = "learning_platform";
        }
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    public function getConnection()
    {
        if (isEmpty($this->conn)) {
            $this->connect();
        }
        
        return $this->conn;
    }
    
    private function conect()
    {
        $this->conn = new \PDO(
            "mysql:dbname=".$this->database.";
            host=".$this->host.";
            charset=".$this->charset,
            $this->username,
            $this->password
        );
    }
}

