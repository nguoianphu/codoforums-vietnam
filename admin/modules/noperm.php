<?php

$smarty = \CODOF\Smarty\Single::get_instance();
$smarty->assign('msg', 'You do not have enough permissions.');

global $CONF;

$content = $smarty->fetch('noperm.tpl');

