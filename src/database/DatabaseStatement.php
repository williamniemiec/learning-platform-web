<?php
declare (strict_types=1);

namespace database;


/**
 * Represents a prepared statement and a result set.
 */
abstract class DatabaseStatement
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $statement;
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Executes a prepared statement.
     *
     * @param       array $bindArguments [Optional] An array of values with as
     * many elements as there are bound parameters in the SQL statement being
     * executed
     *
     * @return      bool True on success or false on failure
     */
    public abstract function execute(array $bindArguments = null) : bool;
    
    /**
     * Fetches the next row from a result set.
     *
     * @return     array Return an array containing the row in the result set 
     * or empty array on failure. 
     */
    public abstract function fetch();
    
    /**
     * Returns an array containing all of the result set rows.
     *
     * @return      array Returns an array containing all of the remaining rows
     * in the result set. The array represents each row as either an array of
     * column values or an object with properties corresponding to each column
     * name. An empty array is returned if there are zero results to fetch, or
     * empty array on failure.
     */
    public abstract function fetchAll() : array;
    
    /**
     * Returns the number of rows affected by the last SQL statement.
     *
     * @return      int The number of rows.
     */
    public abstract function rowCount() : int;
}