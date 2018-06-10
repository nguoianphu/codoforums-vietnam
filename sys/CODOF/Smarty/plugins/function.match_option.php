<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.match_option.php
 * Type:     function
 * Name:     match_option
 * Purpose:  returns selected for <option>
 * -------------------------------------------------------------
 */

/*
 * @CODOLICENSE
 */

function smarty_function_match_option($params) {

    $user = CODOF\User\User::get();
    
    $preference = $user->prefers($params['key']);
    
    if($preference && $preference == $params['value']) {
        
        echo 'selected';
    }
    
}

