<?php

/*
 * @CODOLICENSE
 */


require LOCALE . '/' . LOCALE . '.php';

function _t($str, $plural = null, $count = 0) {

    if ($plural != null && $count > 1) {

        //return plural translation
        return codo_get_translation($plural, $count);
    }


    //return singular translation
    return codo_get_translation($str, $count);
}

//removes a translation from language file
function _r($index) {

    global $CODOT;

    unset($CODOT[$index]);
    write_to_file($CODOT);
}

function codo_get_translation($index, $count) {

    global $CODOT;

    if (!isset($CODOT[$index])) {

        $CODOT[$index] = $index;

        //add translation if does not exist
        if (MODE == 'DEVELOPMENT') {

            asort($CODOT);
            write_to_file($CODOT);
        }
    }
    
    return str_replace("%s", $count, $CODOT[$index]);
}

function write_to_file($arr) {

    $pre = "/**
 * 
 * Creator: 
 * 
 * Translation in codoforum is very simple 
 * Copy paste this file into
 * locale/your_language/your_language.php
 * 
 * For eg. locale/ru_RU/ru_RU.php or locale/russian/russian.php
 * 
 * After that , write translations of left of => to the right of =>
 * in that file.
 * 
 * For eg.
 * 
 * 'My profile' => 'Мой профиль',
 * 
 * You can then select the language from the backend
 *
 */
";

    file_put_contents(DATA_PATH . 'locale/' . LOCALE . '/' . LOCALE . '.php', "<?php\n\n$pre\n \$CODOT = " . var_export($arr, true) . ";");
}
