<?php

/*
 * @CODOLICENSE
 */

$smarty = \CODOF\Smarty\Single::get_instance();

$db = \DB::getPDO();

CODOF\Util::get_config($db);

$reg_req_admin = \CODOF\Util::get_opt('reg_req_admin');


if (isset($_POST['action']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

    $action = $_POST['action'];

    if ($action == 'approve') {

        \DB::table(PREFIX . 'codo_users')
                ->whereIn('id', $_POST['ids'])
                ->update(array('user_status' => 1));
        \DB::table(PREFIX . 'codo_user_roles')
                ->whereIn('uid', $_POST['ids'])
                ->update(array('rid' => ROLE_USER));
    } else {

        foreach($_POST['ids'] as  $id) {

            $user = CODOF\User\User::get((int)$id);
            $user->deleteAccount();
        }
    }
}

$qry = "SELECT id,username,mail,created,user_status FROM " . PREFIX . "codo_users WHERE user_status=2 OR user_status=0 AND username<>'anonymous'";

$obj = $db->query($qry);
$res = $obj->fetchAll();

$users = array();

foreach ($res as $user) {


    $users[] = array(
        'id' => $user['id'],
        'username' => $user['username'],
        'mail' => $user['mail'],
        'created' => CODOF\Time::get_pretty_time($user['created']),
        'confirmed' => (int) $user['user_status'] == 2 ? 'yes' : 'no'
    );
}

$smarty->assign('reg_req_admin', $reg_req_admin);
$smarty->assign('users', $users);
$content = $smarty->fetch('moderation/approve_users.tpl');
