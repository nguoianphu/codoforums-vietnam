<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.get_pretty_time.php
 * Type:     modifier
 * Name:     get_pretty_time
 * Purpose:  converts php time to readable format
 * -------------------------------------------------------------
 */

/*
 * @CODOLICENSE
 */


function smarty_modifier_get_pretty_time($string)
{

    $time = CODOF\Time::get_pretty_time($string);
    
    if(!$time) {
        
        return _t("just now");
    }
    
    return $time;
}

