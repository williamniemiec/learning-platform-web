<?php
namespace domain\util;


/**
 * Should be thrown when a method has been invoked at an illegal or 
 * inappropriate time, or has an attribute that contains an invalid value.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
 class IllegalStateException extends \Exception
 {
     //-------------------------------------------------------------------------
     //        Constructor
     //-------------------------------------------------------------------------
     /**
      * Generates IllegalStateException. An IllegalStateException should be 
      * thrown when a method has been invoked at an illegal or inappropriate 
      * time, or has an attribute that contains an invalid value.
      *
      * @param       string $messsage [Optional] Message
      */
     public function __construct($message = '')
     {
         parent::__construct($message);
     }
 }