<?php
require 'environment.php';


if (ENVIRONMENT == 'development') {
    define("BASE_URL", "http://localhost/");
    define("DB", array(
        'host' => "127.0.0.1",
        'charset' => "utf8",
        'username' => "root",
        'password' => "",
        'database' => "learning_platform"
    ));
}
else {
    define("BASE_URL", "https://wniemiec-learning-platform.000webhostapp.com/");
    define("DB", array(
        'host' => "localhost",
        'charset' => "utf8",
        'username' => "id20101994_root",
        'password' => "%pq1bG7EA8|>c{3^",
        'database' => "id20101994_learning_platform"
    ));
}