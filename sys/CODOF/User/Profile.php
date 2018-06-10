<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\User;

class profile {

    public function get_uid($id) {

        $uid = $id;

        if (!isset($_SESSION[UID . 'USER']['id']) && !$id) {

            //not passed id and not logged in
            header('Location: ' . User::getLoginUrl());
            exit;
        } else if (isset($_SESSION[UID . 'USER']['id']) && !$id) {

            //not passed id but is logged in so he is checking his own profile
            $uid = intval($_SESSION[UID . 'USER']['id']);
        } else {

            //passed id and may or not be logged in i.e checking someone else's 
            //profile            
            $user = \DB::table(PREFIX . 'codo_users')
                    ->where('id', '=', $id)
                    ->orWhere('username', '=', $id)
                    ->first();
            $uid = $user['id'];
            
        }

        return $uid;
    }

    public function get_edit_view($passed_id, $uid) {

        $view = 'access_denied';
        
        if ($passed_id && isset($_SESSION[UID . 'USER']['id'])) {

            if ( ($passed_id == $_SESSION[UID . 'USER']['id'] && \CODOF\Access\Access::hasPermission('edit my profile') )
                    || \CODOF\Access\Access::hasPermission('edit all profiles')) {

                $view = 'user/profile/edit';
                \CODOF\Hook::call('before_profile_edit_load', array($uid));
            }
        }
        
        return $view;
    }

}
