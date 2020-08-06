<?php
require 'environment.php';


if (ENVIRONMENT == 'development') {
    define("BASE_URL", "http://localhost/wp_learningPlatform/src/");
}
else {
    define("BASE_URL", "http://mySite.com/");
}