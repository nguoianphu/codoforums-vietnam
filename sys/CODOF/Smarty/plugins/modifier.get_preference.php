<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.get_preference.php
 * Type:     modifier
 * Name:     get_preference
 * Purpose:  returns users' preference settings
 * -------------------------------------------------------------
 */

/*
 * @CODOLICENSE
 */

function smarty_modifier_get_preference($key) {

    $user = CODOF\User\User::get();

    return $user->prefers($key);
}
