<?php

/*
 * @CODOLICENSE
 */
//phpinfo();

// ini_set('xdebug.var_display_max_depth', '100');
ini_set("display_errors", "off");

//echo phpversion();
date_default_timezone_set('Asia/Saigon');

error_reporting(0);

define('IN_CODOF', true);

if (!isset($_COOKIE['user_id']))
    setcookie("user_id", 0);

//contains config.php and path definitions
require 'sys/load.php';
ini_set('display_errors', 0);
//everything related to routing
require 'routes.php';
