<?php
require 'environment.php';


if (ENVIRONMENT == 'development') {
	define("BASE_URL", "http://localhost/wp_learningPlatform/src/panel/");
	define("DB", array(
	    'host' => "127.0.0.1",
	    'charset' => "utf8",
	    'username' => "root",
	    'password' => "",
	    'database' => "learning_platform"
	));
} 
else {
	define("BASE_URL", "http://wp-lp.infinityfreeapp.com/panel/");
	define("DB", array(
	    'host' => "learning-platform.mysql.database.azure.com",
	    'charset' => "utf8",
	    'username' => "adm@learning-platform",
	    'password' => "test12345@A",
	    'database' => "learning_platform"
	));
}