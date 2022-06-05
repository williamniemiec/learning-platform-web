<?php
session_start();

require 'src/main/php/config.php';
require 'vendor/autoload.php';

$core = new config\Core();
$core->run();