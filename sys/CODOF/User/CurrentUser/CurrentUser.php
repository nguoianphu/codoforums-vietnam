<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\User\CurrentUser;

class CurrentUser {

    public static function id() {

        if (isset($_SESSION[UID . 'USER']['id']))
            return $_SESSION[UID . 'USER']['id'];
        return 0;
    }

    public static function loggedIn() {

        return isset($_SESSION[UID . 'USER']['id']);
    }

}
