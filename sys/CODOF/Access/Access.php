<?php

/*
 * @CODOLICENSE
 */

/*
 * 
 * Access permissions are governed by priorities.
 * 
 * Grant permission has higher priority than Deny permission
 * 
 * That means, if a user has 2 roles .
 * And, if any one of them has permission to do something,
 * He will be allowed to do so.
 * 
 */

/**
 * 
 * view forum
 *   
 * 
 * 
 */
namespace CODOF\Access;

use CODOF\Hook as Hook;

class Access {

    /**
     * Array of all permissions
     * @var array 
     */
    static protected $permissions;

    const GRANTED = 1;

    const CAN_VIEW_TOPIC = 'can view topic';
    
    
    
    /**
     * 
     * @param array|string $permission true if any is allowed
     * @param int $rid
     * @return boolean
     * 
     * Checks if the user with $rid else the current user has
     * permission($permission)
     *
     * If an array of permissions are passed it returns true if any of them 
     * are satisfied 
     */
    public static function hasPermission($permission, $uid = false, $cid = 0, $tid = 0) {

        if (!is_array($permission)) {

            $permissions = array($permission);
        } else {

            $permissions = $permission;
        }

        if (!$uid) {

            $user = \CODOF\User\User::get();
            $uid = $user->id;
        }

        //Hook::call('has_permission', $permissions);

        if (!isset(self::$permissions[$uid])) {
            self::getPermissions($uid);
        }

        foreach ($permissions as $permission) {

            if (!isset(self::$permissions[$uid][$permission])) {

                //\CODOF\Log::notice("Permission $permission not found in ACL");
                continue;
            }

            if ($tid == 0 && !isset(self::$permissions[$uid][$permission][$cid])) {
                $cid = 0;
            }

            if ($tid > 0 && !isset(self::$permissions[$uid][$permission][$cid][$tid])) {
                $tid = 0;
            }
            if (isset(self::$permissions[$uid][$permission][$cid][$tid]) 
                    && self::$permissions[$uid][$permission][$cid][$tid] === self::GRANTED) {

                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * 
     * @param array|string $permissions true if all are allowed
     * @param int $rid
     * @param string $module
     * @return boolean
     * 
     * Checks if the user with $rid has
     * permissions($permissions) for the module(default core)
     *
     * If an array of permissions are passed it returns true if all of them 
     * are satisfied 
     */
    public static function hasAllPermissions(array $permissions, $uid, $cid = 0, $tid = 0) {

        //Hook::call('has_permission', $permissions);

        if (!isset(self::$permissions[$uid])) {

            self::getPermissions($uid);
        }


        foreach ($permissions as $permission) {
            if (!isset(self::$permissions[$uid][$permission])) {

                \CODOF\Log::notice("Permission $permission not found in ACL");
                return FALSE;
            }

            if ($cid > 0 && !isset(self::$permissions[$uid][$permission][$cid])) {
                $cid = 0;
            }
            if ($tid > 0 && !isset(self::$permissions[$uid][$permission][$cid][$tid])) {
                $tid = 0;
            }

            if (self::$permissions[$uid][$permission][$cid][$tid] !== self::GRANTED) {

                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Saves permissions of all roles from the database
     */
    private static function getPermissions() {

        $db = \DB::getPDO();
        $user = \CODOF\User\User::get();
        $uid = $user->id;
        $rids = $user->rids;

        $qry = 'SELECT * FROM codo_permissions WHERE rid IN (' . implode(",", $rids) . ')';
        $obj = $db->query($qry);
        $result = $obj->fetchAll();

        $permissions = self::$permissions;
        foreach ($result as $res) {

            if (isset($permissions[$uid][$res['permission']][$res['cid']][$res['tid']])) {

                if ($res['granted'] == '1') {
                    //change only if higher priority i.e Granted
                    $permissions[$uid][$res['permission']][$res['cid']][$res['tid']] = 1;
                }
            } else {

                $permissions[$uid][$res['permission']][$res['cid']][$res['tid']] = (int) $res['granted'];
            }
        }
        
        self::$permissions = $permissions;
    }

}

/**
 * Owners
 * B
 * Catereres
 * Customers
 * 
 * 3 0   view 1
 * 
 * 5 0 view ?
 * 
 * 
 * Registered + Premium => Normal + 1 Premium category
 * Registered + Moderator => 
 * 
 * Registered + PC1 + PC2 =>
 * Registered + PC1 =>
 * 
 * Banned 
 * 
 */


/**
 * 
 * -1 -> General permissions
 * 0 -> All categories
 * >=1 -> specific category
 * 1,2
 * 
 * 0  create new topic  1  1
 * 1  create new topic  2  0 
 * 
 * [permission]
 * 
 */