<?php
namespace domain\util;


/**
 * An IllegalAccessException is thrown when an application tries to do 
 * something and it does not have permission to do it.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
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
     * @param       string $messsage [Optional] Message
     */
    public function __construct($message = '')
    {
        parent::__construct($message);
    }
}