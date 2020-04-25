<?php
namespace core;


/**
 * All models will have access to a database (if the application has one).
 */
class Model 
{
    //-----------------------------------------------------------------------
    //        Attributes
    //-----------------------------------------------------------------------
	protected $db;


    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
	public function __construct ()
	{
	    $this->db = new Database();
	}
}
