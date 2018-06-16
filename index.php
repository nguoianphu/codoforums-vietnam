<?php

/*
 * @CODOLICENSE
 */
//phpinfo();

// ini_set('xdebug.var_display_max_depth', '100');
ini_set("display_errors", "on");

//echo phpversion();
date_default_timezone_set('Asia/Saigon');

error_reporting(0);

define('IN_CODOF', true);

//contains config.php and path definitions
require 'sys/load.php';
// ini_set('display_errors', 0);
//everything related to routing
require 'routes.php';
