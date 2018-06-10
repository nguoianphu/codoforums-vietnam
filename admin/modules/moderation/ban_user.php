<?php

/*
 * @CODOLICENSE
 */

$smarty = \CODOF\Smarty\Single::get_instance();

$db = \DB::getPDO();


define('HOUR', 3600);
define('DAY', HOUR * 24);
define('MONTH', DAY * 30);

$smarty->assign('msg', '');
$query = "SELECT * FROM " . PREFIX . "codo_config";

if (isset($_GET['t'])) {
    $smarty->assign('msg', 'The user has been banned successfully');
}

if (isset($_POST['ban_uid']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

    $uid = $_POST['ban_uid'];
    $type = $_POST['ban_type'];

    $user = CODOF\User\User::get();

    $by = $user->username;
    $on = time();

    $reason = $_POST['ban_reason'];

    $mul = array("hour" => HOUR, "day" => DAY, "month" => MONTH, "forever" => 0);

    $seconds = floor((int) $_POST['ban_expires'] * $mul[$_POST['ban_expires_type']]);

    if ($seconds == 0) {

        $till = 0;
    } else {

        $till = time() + (int) $seconds;
    }

    $values = array(
        "uid" => $uid,
        "ban_type" => $type,
        "ban_by" => $by,
        "ban_on" => $on,
        "ban_reason" => $reason,
        "ban_expires" => $till
    );

    $ban = new CODOF\User\Ban($db);
    $ban->values = $values;

    if (isset($_POST['id'])) {

        $ban->update_ban($_POST['id']);
    } else {

        $ban->add_ban();
    }

    header('Location: index.php?page=moderation/ban_user&t=banned');
}

if (isset($_POST['remove_ban'])) {

    $ban = new CODOF\User\Ban($db);
    $ban->remove_ban($_POST['id']);
    header('Location: index.php?page=moderation/ban_user');
}

$qry = 'SELECT * FROM ' . PREFIX . 'codo_bans';
$obj = $db->query($qry);
$res = $obj->fetchAll();

$bans = $res;
$i = 0;

//returns how many hours/days/months left
function get_expires($time) {


    if ($time < 0) {

        return '#forever';
    }

    if ($time < DAY) {

        return round($time / HOUR) . '#hour';
    }

    if ($time < MONTH) {

        return round($time / DAY) . '#day';
    }

    return round($time / MONTH) . '#month';
}

foreach ($res as $r) {

    $bans[$i]['ban_on'] = \CODOF\Time::get_pretty_time($r['ban_on']);
    if ($r['ban_expires'] == 0) {

        $bans[$i]['ban_expires'] = 'forever';
    } else {

        $bans[$i]['ban_expires'] = date('d-m-Y h:m:s', $r['ban_expires']);
    }

    $bans[$i]['ban_real_expires'] = get_expires((int) $r['ban_expires'] - time());

    $i++;
}


CODOF\Util::get_config($db);

$smarty->assign('bans', $bans);
$content = $smarty->fetch('moderation/ban_user.tpl');
