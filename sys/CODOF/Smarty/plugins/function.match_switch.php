<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.match_switch.php
 * Type:     function
 * Name:     match_switch
 * Purpose:  returns codo_switch_on or codo_switch_off
 * -------------------------------------------------------------
 */

/*
 * @CODOLICENSE
 */

function smarty_function_match_switch($params) {

    $user = CODOF\User\User::get();

    $preference = $user->prefers($params['key']);

    if ($preference && $preference == $params['value']) {

        echo 'codo_switch_on';
    } else {

        echo 'codo_switch_off';
    }
}
