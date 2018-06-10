<?php

namespace CODOF\User;

use CODOF\Booter\IoC\DB;
use CODOF\Util;

$smarty = \CODOF\Smarty\Single::get_instance();
$dir = "../sites/default/locale/";
Util::get_config(\DB::getPDO());
$default_language = Util::get_opt('default_language');
$language_value = array();
if ($open = opendir($dir)) {

    while (false !== ($file = readdir($open))) {
        if ($file != "." && $file != ".." && $file != "lang.php") {
            $language = stripslashes(str_replace("/","",$file));
            $language_value[$language] = file_get_contents($dir . $file . '/' . $file . '.' . 'php');
        }
    }
    closedir($open);
}

$smarty->assign('dir_is_writable',is_writable($dir));
$smarty->assign('dir',($dir));
$smarty->assign('default_language', $default_language);
$smarty->assign('language_value', $language_value);
$content = $smarty->fetch('system/language_settings/language_settings.tpl');