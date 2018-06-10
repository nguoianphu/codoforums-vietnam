<?php

$smarty = \CODOF\Smarty\Single::get_instance();

$db = \DB::getPDO();

if (isset($_POST['captcha_public_key']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {



    if (!isset($_POST['captcha'])) {

        $_POST['captcha'] = 'no';
    }

    foreach ($_POST as $key => $value) {

        if ($key == 'captcha') {


            $value = "on" == $value ? "enabled" : "disabled";
        }

        $query = "UPDATE " . PREFIX . "codo_config SET option_value=:value WHERE option_name=:key";
        $ps = $db->prepare($query);
        $ps->execute(array(':key' => $key, ':value' => htmlentities($value, ENT_QUOTES, 'UTF-8')));
    }
}

CODOF\Util::get_config($db);

$content = $smarty->fetch('spam/recaptcha.tpl');
