<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.abbrev.php
 * Type:     modifier
 * Name:     abbrev
 * Purpose:  abbreviates numbers to k, m, b, t
 * -------------------------------------------------------------
 */

/*
 * @CODOLICENSE
 */


function smarty_modifier_abbrev_no($string)
{

    return \CODOF\Util::abbrev_no($string, 2);
}

