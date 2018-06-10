<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Permission;

class Manager {

    /**
     * Adds role to the roles table
     * @param string $rname
     * @return int
     */
    public function addRole($rname) {

        \DB::table(PREFIX . 'codo_roles')->insert(
                array('rname' => $rname)
        );

        return \DB::getPDO()->lastInsertId('rid');
    }

    /**
     * Gets roleid from roles table
     * @param string $roleName
     * @return int
     * @throws \Exception
     */
    public function getRoleId($roleName) {

        $rid = \DB::table(PREFIX . 'codo_roles')
                ->where('rname', '=', $roleName)
                ->pluck('rid');

        if ($rid === NULL) {
            throw new \Exception('Role name not found');
        }

        return $rid;
    }

    /**
     * Copies permissions from one role id to other
     * @param int $fromRoleId
     * @param int $toRoleId
     */
    public function copyRole($fromRoleId, $toRoleId) {

        $permission = new Permission();
        $permissions = $permission->getPermissions($fromRoleId);
        $set = $permission->createPermissionSet($permissions, $toRoleId);
        \DB::table(PREFIX . 'codo_permissions')->insert($set);
    }

    /**
     * Copies forum permission of role to category
     * @param type $rid
     * @param type $cid
     */
    public function copyCategoryPermissionsFromRole($cid) {

        $permission = new Permission();
        $roles = \DB::table(PREFIX . 'codo_roles')->get();
        $sets = array();
        
        foreach ($roles as $role) {

            $rid = $role['rid'];
            $permissions = $permission->getForumPermissions($rid);//query in a loop
            $sets = array_merge($permission->createPermissionSet($permissions, $rid, $cid), $sets);
        }

        \DB::table(PREFIX . 'codo_permissions')->insert($sets);
    }

}
