<?php
namespace core;


/**
 * Class responsible for connecting to the database.
 */
class Database extends \PDO
{
    public function __construct()
    {
        global $config;
        
        parent::__construct("mysql:dbname=".$config['dbname'].";host=".$config['host'].";charset=".$config['charset'], 
            $config['dbuser'], 
            $config['dbpass']
        );
    }
}

