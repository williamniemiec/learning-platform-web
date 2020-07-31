<?php
require 'environment.php';


if (ENVIRONMENT == 'development') {
    define("BASE_URL", "http://localhost/wp_learningPlatform/src/panel/");
}
else {
    define("BASE_URL", "http://mySite.com/");
}