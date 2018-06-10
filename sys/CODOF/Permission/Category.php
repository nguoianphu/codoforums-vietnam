<?php

namespace CODOF\Permission;

class Category {

    /**
     * Get permissions specific to category and role id
     * @param int $cid
     * @param int $rid
     * @return array
     */
    public function getPermissions($cid, $rid) {

        $permissions = \DB::table(PREFIX . 'codo_permissions AS p')
                        ->join(PREFIX . 'codo_permission_list AS l', 'p.permission', '=', 'l.permission')
                        ->select('p.permission', 'p.granted', 'p.pid', 'p.cid', 'inherited', 'rid')
                        ->where('rid', '=', $rid)
                        ->where('cid', '=', $cid)
                        ->where('tid', '=', 0)
                        ->where('type', '=', 'forum')
                        ->orderBy('pid')->get();

        $set = array();
        foreach ($permissions as $permission) {

            $set[$permission['permission']] = array(
                'permission' => $permission['permission'],
                'rid' => $permission['rid'],
                'pid' => $permission['pid'],
                'id' => str_replace(' ', '_', $permission['permission']),
                'granted' => $permission['granted'],
                'inherited' => $permission['inherited'] === 1 ? 'yes' : 'no'
            );
        }
        return $set;
    }

    /**
     * Returns category ids of children of passed category
     * @param int $cid
     * @return array
     */
    public function getSubCategoryIds($cid) {

        $cats = \DB::table(PREFIX . 'codo_categories')
                ->select('cat_id')
                ->where('cat_pid', $cid)
                ->get();

        return array_column($cats, 'cat_id');
    }

    /**
     * Updates permissions of categories based on $updated_permission_set
     * @param array $cids
     * @param array $updated_permission_set
     */
    public function updatePermissionsOf($cids, $updated_permission_set, $notToUpdateHashes = array()) {

        $permissions = \DB::table(PREFIX . 'codo_permissions')->select('pid', 'cid', 'permission', 'rid', 'granted', 'inherited')
                //->where('inherited', 1)
                ->whereIn('cid', $cids)
                ->get();

        $updates = array();
        $thisNotToUpdateHashes = array();
        foreach ($permissions as $permission) {

            $hash = $permission['permission'] . '_' . $permission['rid'];
            if (isset($updated_permission_set[$hash])) {

                $granted = $updated_permission_set[$hash]['granted'];

                if ($permission['inherited'] == 0) {

                    //the children of this category should not update this permission
                    $thisNotToUpdateHashes[$permission['cid']][] = $hash;
                }

                $canUpdate = !in_array($hash, $notToUpdateHashes) && $permission['inherited'] == 1;

                if ($granted != $permission['granted'] && $canUpdate) {
                    $updates[] = array(
                        'pid' => $permission['pid'],
                        'granted' => $granted
                    );
                }
            }
        }

        foreach ($updates as $row) {

            \DB::table(PREFIX . 'codo_permissions')
                    ->where('pid', $row['pid'])
                    ->update(array('granted' => $row['granted']));
        }

        return $thisNotToUpdateHashes;
    }

    public function updateChildPermissions($cid, $updated_permission_set, $notToUpdateHashes = array()) {

        $sub_ids = $this->getSubCategoryIds($cid, $updated_permission_set);

        if (count($sub_ids) === 0) {

            return false;
        }

        $thisNotToUpdateHashes = $this->updatePermissionsOf($sub_ids, $updated_permission_set, $notToUpdateHashes);

        //update children of each $sub_ids
        foreach ($sub_ids as $sub_id) {

            $this->updateChildPermissions($sub_id, $updated_permission_set, 
                    isset($thisNotToUpdateHashes[$sub_id]) ? $thisNotToUpdateHashes[$sub_id] : array());
        }
    }

    /**
     * Updates category permissions based on changes in $granted_permissions
     * @param array $permissions
     * @param array $granted_permissions
     * @return array
     */
    public function updateCategoryPermissions($permissions, $granted_permissions) {

        $updated_permission_set = array();
        foreach ($permissions as $permission) {

            $granted = (int) in_array($permission['pid'], $granted_permissions);

            //EX-OR says whether the permission is changed or not
            $changed = $granted ^ $permission['granted'];
            if ($changed) {

                $updated_permission_set[$permission['permission'] . '_' . $permission['rid']] = array(
                    'pid' => $permission['pid'],
                    'granted' => $granted //the new value
                );
            }
        }

        foreach ($updated_permission_set as $row) {

            \DB::table(PREFIX . 'codo_permissions')
                    ->where('pid', $row['pid'])
                    ->update(array('granted' => $row['granted'], 'inherited' => 0));
        }

        return $updated_permission_set;
    }

}
