<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.print_post_url.php
 * Type:     function
 * Name:     print_post_url
 * Purpose:  prints absolute post url
 * -------------------------------------------------------------
 */

/*
 * @CODOLICENSE
 */

function smarty_function_print_post_url($option) {

    if (isset($option['pid'])) {

        echo \CODOF\Forum\Forum::getPostURL($option['tid'], $option['title'], $option['pid']);
    } else {

        echo \CODOF\Forum\Forum::getPostURL($option['tid'], $option['title']);
    }
}
