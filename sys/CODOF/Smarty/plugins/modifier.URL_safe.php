<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.URL_safe.php
 * Type:     modifier
 * Name:     URL_safe
 * Purpose:  makes an url safe 
 * -------------------------------------------------------------
 */

/*
 * @CODOLICENSE
 */


function smarty_modifier_URL_safe($string)
{

    return \CODOF\Filter::URL_safe($string);
}

