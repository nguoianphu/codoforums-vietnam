<?php

/*
 * @CODOLICENSE
 */

/*
 * 
 * This defines the default values for default user i.e guest
 */

namespace CODOF\User\CurrentUser;

class DefaultUser {

    /**
     *
     * @var int userid of the user
     */
    public $id = 0;
    
    /**
     *
     * @var string username
     */
    public $username;
    
    /**
     *
     * @var string email id of user
     */
    public $mail;
    
    public $reputation = 0;
    
    public $no_posts = 0;
    /**
     * 1 => guest
     * @var int role id of user
     */
    public $rids = array(ROLE_GUEST);
    
    public $rid = ROLE_GUEST;
    
    /**
     * Check if user has permission to  perform an action
     * @param string $permission
     * @return boolean
     */
    public function can($permission, $cid = 0, $tid = 0) {

        return \CODOF\Access\Access::hasPermission($permission, $this->id, $cid, $tid);
    }

    /**
     * Returns true only if user has permission to perform any of the actions
     * @param array $permission
     * @return boolean
     */
    public function canAny($permission, $cid = 0, $tid = 0) {

        return \CODOF\Access\Access::hasPermission($permission, $this->id, $cid, $tid);
    }

    /**
     * Returns true only if user has permission to perform all actions
     * @param array $permissions
     * @return boolean
     */
    public function canAll(array $permissions, $cid = 0, $tid = 0) {

        return \CODOF\Access\Access::hasAllPermissions($permissions, $this->id, $cid, $tid);
    }
            
    public function __call($method, $arguments) {
        
        return false;
    }
}
