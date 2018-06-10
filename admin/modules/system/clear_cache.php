<?php

/*
 * @CODOLICENSE
 */


/**
 * Deletes all files in a directory
 *
 * @param [string] $folder
 * @return void
 */
function clearDirectory($folder) {

    foreach (glob($folder . "/*.*") as $filename) {
        if (is_file($filename)) {
            @unlink($filename);
        }
    }
}


$folders = array("css", "HB/compiled", "js", "smarty/templates_c");

$cacheDir = ABSPATH . 'cache/';

foreach($folders as $folder) {

    clearDirectory($cacheDir . $folder);
}

header("Location: index.php");
exit;