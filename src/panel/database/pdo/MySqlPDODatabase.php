<?php
declare (strict_types=1);

namespace database\pdo;


/**
 * Connects to a MySQL database via PDO.
 *
 * @link        https://www.php.net/manual/pt_BR/book.pdo.php
 * @link        https://www.mysql.com/
 */
class MySqlPDODatabase extends PDODatabase
{
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * {@inheritdoc}
     * @Override
     */
    private function getInstance()
    {
        $this->conn = new \PDO(
            "mysql:dbname=".$this->getDatabase().";
            host=".$this->getHost().";
            charset=".$this->getCharset(),
            $this->getUsername(),
            $this->getPassword()
            );
    }
}