<?php



dispatch("pmx/", function(){


    $userId = CODOF\User\CurrentUser\CurrentUser::id();
    if($userId==0){
        CODOF\Smarty\Layout::load('access_denied');
        return;
    }

    require_once 'freichat/hardcode.php';
    $xhash =  md5($userId . $uid);
    \CODOF\Store::set('sub_title', _t('Messages'));
    \CODOF\Smarty\Single::get_instance()->assign('xhash', $xhash);
    CODOF\Smarty\Layout::load('pmx:pmx');
});


CODOF\Hook::add('before_site_head', function () {

    if (\CODOF\User\CurrentUser\CurrentUser::loggedIn()) {
        $translations = \CODOF\Store::get('translations', array());
        $translations['pmx_title'] = _t('Private Messenger');
        \CODOF\Store::set('translations', $translations);
        add_js(PLUGIN_PATH . 'pmx/assets/js/pmx.js', array('name' => 'pmx.js', 'type' => 'defer'));
    }
});

/**
 *
 * Known issues/ TODO:
 *
 * 1. A user is not removed from list if he comes online then goes offline until you refresh the page.
 * 2. A user from online list will not be moved to conversation list when a new message is received.
 * 3. Default messages when no conversations/ online users
 * 4. User avatars
 * 5. Time of messages with date partitions
 * 6. Receive messages [IN PROGRESS]
 * 7. IE 11 doesn't work due to includes method used in ChatFilter
 *
 * //Can't take now
 * 8. User is shown online only if he opens pmx page since freichat loads there
 */
