<?php

/*
 * @CODOLICENSE
 */
$smarty = \CODOF\Smarty\Single::get_instance();

$db = \DB::getPDO();


$query = "SELECT * FROM " . PREFIX . "codo_config";


if (isset($_POST['site_title']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {
    $file = $_FILES['forum_logo'];
    $file_name = $file['name'];
    $file_type = $file ['type'];
    $file_size = $file ['size'];
    $file_path = $file ['tmp_name'];
    if (!move_uploaded_file($file_path, '../sites/default/assets/img/attachments/' . $file_name)) {
    } else {
        \CODOF\Util::set_opt('forum_logo',$file_name);
    }

    $cfgs = array();

    if (!isset($_POST['reg_req_admin'])) {
        $_POST['reg_req_admin'] = 'off';
    }
    foreach ($_POST as $key => $value) {

        if ($key == 'reg_req_admin') {


            $value = "on" == $value ? "yes" : "no";
        }
        if ($key == "image") {
            $value = $file_name;
        }
        $query = "UPDATE " . PREFIX . "codo_config SET option_value=:value WHERE option_name=:key";
        $ps = $db->prepare($query);
        $ps->execute(array(':key' => $key, ':value' => htmlentities($value, ENT_QUOTES, 'UTF-8')));
        //echo $query."<br>\n";    
    }
}

CODOF\Util::get_config($db);
$content = $smarty->fetch('config.tpl');
