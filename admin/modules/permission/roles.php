<?php

$smarty = \CODOF\Smarty\Single::get_instance();

$smarty->assign('err', 0);
$smarty->assign('msg', "");

class role {

    static function delete_role($id) {

        DB::table(PREFIX . 'codo_roles')->where('rid', '=', $id)->delete();
        DB::table(PREFIX . 'codo_user_roles')->where('rid', '=', $id)->delete();
        DB::table(PREFIX . 'codo_permissions')->where('rid', '=', $id)->delete();
        DB::table(PREFIX . 'codo_block_roles')->where('rid', '=', $id)->delete();
    }

}

//NEW
if (isset($_POST['role_name']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

    $manager = new CODOF\Permission\Manager();
    $rid = $manager->addRole($_POST['role_name']);
    $fromRid = $_POST['copy_from_role_id'];
    $manager->copyRole($fromRid, $rid);


    $smarty->assign('msg', "Role added successfully.");
}

$smarty->assign('msgType', 'info');
//Delete
if (isset($_POST['del_role_id']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

    $rid = (int) $_POST['del_role_id'];
    $systemRoles = array(ROLE_ADMIN, ROLE_BANNED, ROLE_GUEST, ROLE_MODERATOR,
        ROLE_UNVERIFIED, ROLE_USER);

    if (!in_array($rid, $systemRoles)) {

        role::delete_role($_POST['del_role_id']);
        $smarty->assign('msg', "Role deleted successfully.");
    } else {

        $smarty->assign('msg', "System defined roles cannot be deleted.");
        $smarty->assign('msgType', 'danger');
    }
}


$roles = DB::table(PREFIX . 'codo_roles')->get();
$smarty->assign('roles', $roles);
$content = $smarty->fetch('permission/roles.tpl');
