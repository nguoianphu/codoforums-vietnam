<?php

/*
 * @CODOLICENSE
 */
$smarty = \CODOF\Smarty\Single::get_instance();

$db = \DB::getPDO();
$query = "SELECT * FROM " . PREFIX . "codo_config";
CODOF\Util::get_config($db);


if (isset($_POST['version']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

    $ver = $_POST['version'];
    
    if($ver > 0) {
        
        $upgrader = new \CODOF\Upgrade\Upgrade();
        $upgrader->upgradeDB($ver);        
    }    
}
$content = $smarty->fetch('manual_upgrade.tpl');
