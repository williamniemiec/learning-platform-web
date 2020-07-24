<?php
namespace models;

use core\Model;


/**
 * Responsible for managing admins table.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class IllegalAccessException extends \Exception
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates admins table manager.
     *
     * @param       int $id_user [Optional] Student id
     *
     * @apiNote     It will connect to the database when it is instantiated
     */
    public function __construct($message)
    {
        parent::__construct($message);
    }
}