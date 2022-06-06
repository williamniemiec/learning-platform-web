<?php
namespace domain\util;


/**
 * Should be thrown when a method has been invoked at an illegal or 
 * inappropriate time, or has an attribute that contains an invalid value.
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
      * @param       string $message [Optional] Message
      */
     public function __construct($message = '')
     {
         parent::__construct($message);
     }
 }