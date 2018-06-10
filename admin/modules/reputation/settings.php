<?php

$smarty = \CODOF\Smarty\Single::get_instance();

$db = \DB::getPDO();


$query = "SELECT * FROM " . PREFIX . "codo_config";


if (isset($_POST['max_rep_per_day']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

    if(!isset($_POST['enable_reputation'])) {
        
        $_POST['enable_reputation'] = 'off';
    }
    
    foreach ($_POST as $key => $value) {

        if ($key == 'enable_reputation') {


            $value = "on" == $value ? "yes" : "no";
        }

        $query = "UPDATE " . PREFIX . "codo_config SET option_value=:value WHERE option_name=:key";
        $ps = $db->prepare($query);
        $ps->execute(array(':key' => $key, ':value' => htmlentities($value, ENT_QUOTES, 'UTF-8')));
    }
}

CODOF\Util::get_config($db);
$content = $smarty->fetch('reputation/settings.tpl');
