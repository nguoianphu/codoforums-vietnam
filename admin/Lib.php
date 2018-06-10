<?php

/*
 * @CODOLICENSE
 */

class codoForumAdmin {

    public static $action = array();

    public static function show_layout($index) {

        $smarty = \CODOF\Smarty\Single::get_instance();

        if (isset($_SESSION[UID . 'A_loggedin']) && ($_SESSION[UID . 'A_loggedin'] == 'admin' || $_SESSION[UID . 'A_loggedin'] == 'moderator' ) && isset($_SESSION[UID . 'USER']['id'])) {

            //$user = CODOF\User\CurrentUser\CurrentUser::get();
            //$user=new CODOF\User\User(\DB::getPDO());
            //$user=$user->get($id);
            //var_dump($user);

            $smarty->assign('A_avatar', $_SESSION[UID . 'A_loggedin_avatar']);
            $smarty->assign('A_created', $_SESSION[UID . 'A_loggedin_created']);
            $smarty->assign('A_username', $_SESSION[UID . 'A_loggedin_username']);
            $smarty->assign('A_layout_type', "admin_layout");


            $smarty->assign('logged_in', 'yes');
        } else {
            $index = 'login';
        }

        //moderator can access path which begins with "moderation/"
        if (isset($_SESSION[UID . 'A_loggedin']) && $_SESSION[UID . 'A_loggedin'] == 'moderator') {
            
            //set moderator view flag
            $smarty->assign('A_layout_type', "moderator_layout");

            if ($index == "index" || $index == "login" || strpos($index, "moderation/") === 0) {

                //everything is ok. :)
            } else {

                $index = "noperm";
            }
        }
        require "modules/$index.php";

        if (!isset($_GET['raw'])) { //raw output
            $smarty->assign('A_RURI', A_RURI);

            $active = array_fill_keys(codoForumAdmin::$action, '');

            if (isset($_GET['page'])) {

                $active[$index] = 'active';
            } else {
                $active['index'] = 'active';
            }


            $smarty->assign('active', $active);
            $smarty->assign('content', $content);

            echo $smarty->fetch('layout.tpl');
        }
    }

    public static function run() {

        $smarty = \CODOF\Smarty\Single::get_instance(ABSPATH . ADMIN . 'layout/');
        $url = RURI;
        $smarty->assign('home', str_replace(ADMIN, '', $url));
        $smarty->assign('self', $_SERVER['PHP_SELF']);
        $smarty->assign('token', CODOF\Access\CSRF::get_token());

        if (isset($_GET['page']) && isset(codoForumAdmin::$action[$_GET['page']]))
            codoForumAdmin::show_layout(codoForumAdmin::$action[$_GET['page']]);
        else
            codoForumAdmin::show_layout('index');
    }

}
