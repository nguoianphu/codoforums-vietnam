<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.load_block.php
 * Type:     modifier
 * Name:     load block
 * Purpose:  Loads plugins functions attached to a block
 * -------------------------------------------------------------
 */

/*
 * @CODOLICENSE
 */

function smarty_modifier_load_block($block) {

    CODOF\Hook::call($block);
    if (isset(CODOF\Plugin::$data[$block])) {
        foreach (CODOF\Plugin::$data[$block] as $op) {

            echo $op;
        }
    }
}
