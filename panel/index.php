<?php
session_start();

require 'src/main/php/config.php';
require 'vendor/autoload.php';

error_reporting(E_ALL);
ini_set("display_errors", "On");

$core = new config\Core();
$core->run();