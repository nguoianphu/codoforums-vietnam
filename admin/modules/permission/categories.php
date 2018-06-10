<?php

if (!isset($_GET['cat_id'])) {

    header('Location: index.php?page=categories');
    exit;
}


$cid = (int) $_GET['cat_id'];

if (!isset($_GET['rid'])) {

    $rid = ROLE_GUEST;
} else {
    $rid = $_GET['rid'];
}
$smarty->assign('msg', '');


$info = DB::table(PREFIX . 'codo_categories')
        ->select('cat_id', 'cat_pid', 'cat_name')
        ->where('cat_id', '=', $cid)
        ->first();
$catPerm = new CODOF\Permission\Category();
$permissions = $catPerm->getPermissions($cid, $rid);
$roles = DB::table(PREFIX . 'codo_roles')->get();

foreach ($roles as $role) {

    if ($role['rid'] == $rid) {

        $curr_role = $role;
    }
}


//save permissions
if (isset($_POST['save']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

    $granted_permissions = array();
    foreach ($_POST as $pid => $value) {

        if ($value === 'on') {

            $granted_permissions[] = $pid;
        }
    }
    
    $updated_permission_set = $catPerm->updateCategoryPermissions($permissions, $granted_permissions);
    //now update permissions of categories that are inheriting these permissions
    $catPerm->updateChildPermissions($cid, $updated_permission_set);
    
    $permissions = $catPerm->getPermissions($cid, $rid); //refetch updated permissions

    $smarty->assign('msg', 'Permissions successfully saved');
}


$smarty->assign('roles', $roles);
$smarty->assign('curr_role', $curr_role);
$smarty->assign('info', $info);
$smarty->assign('permissions', $permissions);
$content = $smarty->fetch('permission/categories.tpl');
