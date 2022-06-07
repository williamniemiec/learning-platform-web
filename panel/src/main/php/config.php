<?php
require 'environment.php';


if (ENVIRONMENT == 'development') {
	define("BASE_URL", "http://localhost/panel/");
	define("DB", array(
	    'host' => "127.0.0.1",
	    'charset' => "utf8",
	    'username' => "root",
	    'password' => "",
	    'database' => "learning_platform"
	));
} 
else {
    define("BASE_URL", "https://wniemiec-web-learningplatform.herokuapp.com/panel/");
    define("DB", array(
        'host' => "eyw6324oty5fsovx.cbetxkdyhwsb.us-east-1.rds.amazonaws.com",
        'charset' => "utf8",
        'username' => "dcudisias8jnnqed",
        'password' => "lalcf6ef9d9z73dr",
        'database' => "wxfxzloo0agsgsxj"
    ));
}