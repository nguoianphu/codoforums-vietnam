<?php
/**
 * User: silva
 * Date: 16/12/2017
 * Time: 21:50
 */

$tpl = Admin_tpl::get();
$db = \DB::getPDO();
$flash = array('flash' => false);

if (isset($_POST['GOOGLE_ID']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {
    unset($_POST['CSRF_token']);
    foreach ($_POST as $key => $value) {
        $query = "UPDATE " . PREFIX . "codo_config SET option_value=:value WHERE option_name=:key";
        $ps = $db->prepare($query);
        $ps->execute(array(':key' => $key, ':value' => htmlentities($value, ENT_QUOTES, 'UTF-8')));
    }
    $flash = array('flash' => true, 'message' => 'Settings saved successfully.');

}
CODOF\Util::get_config($db);
$FB_ID = \CODOF\Util::get_opt("FB_ID");
if (is_float($FB_ID))
    $FB_ID = number_format($FB_ID, 0, '', '');
$tpl->assign('flash', $flash);
$tpl->assign('callback_url', RURI . 'uni_login/authorize');
$tpl->assign('FB_ID', $FB_ID);
echo Admin_tpl::render('uni_login/admin/uni_login.admin.tpl');