<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Permission;

class Permission {

    /**
     * Gets permission name and granted columns from table for roleid
     * @param int $rid
     * @return array
     */
    public function getPermissions($rid) {

        $permissions = \DB::table(PREFIX . 'codo_permissions')
                ->select('permission', 'granted', 'cid')
                ->where('rid', '=', $rid)
                ->where('tid', '=', 0)
                ->get();

        return $permissions;
    }

    public function getForumPermissions($rid) {

        $permissions = \DB::table(PREFIX . 'codo_permissions AS p')
                ->select('p.permission', 'granted')
                ->join(PREFIX . 'codo_permission_list AS l', 'p.permission', '=', 'l.permission')
                ->where('rid', '=', $rid)
                ->where('cid', '=', 0)
                ->where('tid', '=', 0)
                ->where('l.type', '=', 'forum')
                ->get();

        return $permissions;
    }

    /**
     * Creates a permissions set that can be inserted into permissions table
     * @param array $permissions
     * @param int $rid
     * @return array
     */
    public function createPermissionSet($permissions, $rid, $cid = 0) {

        $rows = array();
        $getFromArray = false;
        if ($cid === 0) {

            $getFromArray = true;
        }

        foreach ($permissions as $permission) {

            if ($permission['permission'] == 'view forum') {

                $permission['granted'] = 1; //it should always be granted for non-guest
            }

            if ($getFromArray) {

                //TODO: Is this required ?
                $cid = $permission['cid'];
            }

            $rows[] = array(
                'cid' => $cid, 'tid' => 0, 'rid' => $rid,
                'permission' => $permission['permission'],
                'granted' => $permission['granted'],
                'inherited' => ($cid > 0) ? 1 : -1 //inherited is applicable to only category level permissions
            );
        }

        /* if (!$getFromArray) {
          //it means this is a category permission
          $rows[] = array(
          'cid' => $cid, 'tid' => 0, 'rid' => $rid,
          'permission' => 'view category',
          'granted' => $permission['granted'],
          'inherited' => ($cid > 0) ? 1 : -1 //inherited is applicable to only category level permissions
          );
          } */
        return $rows;
    }

    public function addIfNotExists($_permission, $_type, $granted = 1, $new = true) {

        $row = \DB::table(PREFIX . 'codo_permission_list')
                ->where('permission', $_permission)
                ->first();

        if ($row == null) {
            $this->add($_permission, $_type, $granted, $new);
        }
    }

    public function add($_permission, $_type, $granted = 1, $new = true) {

        $permission = strip_tags($_permission);
        $type = in_array($_type, array('general', 'forum')) ? $_type : 'general';

        if ($new) {

            $row = \DB::table(PREFIX . 'codo_permission_list')
                    ->where('permission', $permission)
                    ->first();

            if ($row == null) {

                \DB::table(PREFIX . 'codo_permission_list')
                        ->insert(array(
                            'permission' => $permission,
                            'type' => $type
                ));
            }
        }

        $rids = \DB::table(PREFIX . 'codo_roles')->lists('rid');
        $cids = \DB::table(PREFIX . 'codo_categories')->lists('cat_id');

        $permissions = array();
        $_granted = $granted;

        foreach ($rids as $rid) {

            if (is_array($_granted)) {

                $granted = (int) in_array($rid, $_granted);
            }

            $permissions[] = array(
                'cid' => 0,
                'tid' => 0,
                'rid' => $rid,
                'permission' => $permission,
                'granted' => $granted,
                'inherited' => -1
            );

            foreach ($cids as $cid) {

                $permissions[] = array(
                    'cid' => $cid,
                    'tid' => 0,
                    'rid' => $rid,
                    'permission' => $permission,
                    'granted' => $granted,
                    'inherited' => 1
                );
            }
        }

        \DB::table(PREFIX . 'codo_permissions')
                ->insert($permissions);
    }

    /**
     * Adds all permission from permission list for all roles
     */
    public function addAll() {

        $permissions = \DB::table(PREFIX . 'codo_permission_list')
                ->select('permission', 'type')
                ->get();

        foreach ($permissions as $permission) {

            $this->add($permission['permission'], $permission['type'], 1, false);
        }
    }

}
