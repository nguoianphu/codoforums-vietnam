<?php

$smarty = \CODOF\Smarty\Single::get_instance();

$db = \DB::getPDO();



if (isset($_POST['CSRF_token']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {



    if (!isset($_POST['ml_spam_filter'])) {

        $_POST['ml_spam_filter'] = 'no';
    }

    foreach ($_POST as $key => $value) {

        if ($key == 'ml_spam_filter') {


            $value = "on" == $value ? "yes" : "no";
        }


        $query = "UPDATE " . PREFIX . "codo_config SET option_value=:value WHERE option_name=:key";
        $ps = $db->prepare($query);
        $ps->execute(array(':key' => $key, ':value' => htmlentities($value, ENT_QUOTES, 'UTF-8')));
    }
}

CODOF\Util::get_config($db);
$content = $smarty->fetch('spam/mldetect.tpl');
