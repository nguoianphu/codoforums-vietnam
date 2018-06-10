<?php

/*
 * @CODOLICENSE
 */
//phpinfo();

ini_set('xdebug.var_display_max_depth', '100');
ini_set("display_errors", "on");

//echo phpversion();
date_default_timezone_set('Europe/London');

error_reporting(-1);

define('IN_CODOF', true);

//contains config.php and path definitions
require 'sys/load.php';
ini_set('display_errors', 1);
//everything related to routing
require 'routes.php';
