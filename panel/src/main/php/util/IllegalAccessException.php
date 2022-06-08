<?php
namespace panel\util;


/**
 * An IllegalAccessException is thrown when an application tries to do 
 * something and it does not have permission to do it.
 */
class IllegalAccessException extends \Exception
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Generates IllegalAccessException. An IllegalAccessException should be 
     * thrown when an application tries to do something and it does not have
     * permission to do it.
     *
     * @param       string $message [Optional] Message
     */
    public function __construct($message = '')
    {
        parent::__construct($message);
    }
}