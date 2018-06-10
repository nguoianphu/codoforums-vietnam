<?php

/*
 * @CODOLICENSE
 */

/**
 * This class has been deprecated!
 * 
 */

namespace CODOF\Lang;

class Lang {

    private static $trans;

    public static function init() {

        $filename = DATA_PATH . 'locale/' . LOCALE . '/LC_MESSAGES/messages.mo';

        $mo = new Parser();
        self::$trans = $mo->get_translations($filename, LOCALE);
    }

    public static function gettext($str, $index = -1) {

        if ($index >= 0) {
            if (isset(self::$trans[LOCALE][$str][$index])) {

                $str = self::$trans[LOCALE][$str][$index];
            }
        } else {

            if (isset(self::$trans[LOCALE][$str])) {

                $str = self::$trans[LOCALE][$str];
            }
        }

        return $str;
    }

}
