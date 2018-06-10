<?php

$smarty = \CODOF\Smarty\Single::get_instance();

$smarty->assign('err', 0);
$smarty->assign('msg', "");

if (!isset($_GET['role_id'])) {
    header("Location: index.php?page=permission/roles");
    exit();
}

class RoleEdit {

    static function getRoleInfo($rid) {

        return DB::table(PREFIX . 'codo_roles')
                        ->where('rid', $rid)->first();
    }

    static function getPermissionsByGroup($permissions) {

        $permission_group = array();
        foreach ($permissions as $permission) {

            $permission_group[$permission['type']][] = array(
                'pid' => $permission['pid'],
                'permission' => $permission['permission'],
                'id' => str_replace(' ', '_', $permission['permission']),
                'granted' => $permission['granted']
            );
        }
        return $permission_group;
    }

    static function getPermissions($rid) {

        $permissions = \DB::table(PREFIX . 'codo_permissions AS p')
                ->join(PREFIX . 'codo_permission_list AS l', 'p.permission', '=', 'l.permission')
                ->select('p.permission', 'p.granted', 'p.pid', 'l.type')
                ->where('rid', '=', $rid)
                ->where('cid', '=', 0)
                ->where('tid', '=', 0)
                ->get();

        return $permissions;
    }

}

$rid = (int) $_GET['role_id'];

$permissions = RoleEdit::getPermissions($rid);

//SAVE
if (isset($_POST['save']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

    $granted_permissions = array();
    foreach ($_POST as $pid => $value) {

        if ($value === 'on') {

            $granted_permissions[] = $pid;
        }
    }

    $updated_permission_set = array();
    foreach ($permissions as $permission) {

        $granted = (int) in_array($permission['pid'], $granted_permissions);

        if ($granted != $permission['granted']) {

            $updated_permission_set[$permission['permission'] . '_' . $rid] = array(
                'pid' => $permission['pid'],
                'granted' => $granted //the new value
            );
        }
    }

    foreach ($updated_permission_set as $row) {

        DB::table(PREFIX . 'codo_permissions')
                ->where('pid', $row['pid'])
                ->update(array('granted' => $row['granted']));
    }

    $catPerm = new CODOF\Permission\Category();
    $level1Cats = $catPerm->getSubCategoryIds(0);
    $catPerm->updatePermissionsOf($level1Cats, $updated_permission_set);

    foreach ($level1Cats as $cid) {

        $catPerm->updateChildPermissions($cid, $updated_permission_set);
    }

    $permissions = RoleEdit::getPermissions($rid); //refetch updated permissions

    $smarty->assign('msg', 'Permissions successfully saved');
}

//RESET
if (isset($_POST['reset']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {
    
}


$smarty->assign('role', RoleEdit::getRoleInfo($rid));
$smarty->assign('permissions', RoleEdit::getPermissionsByGroup($permissions));
$smarty->assign('A_RURI', A_RURI);
$content = $smarty->fetch('permission/role_edit.tpl');
