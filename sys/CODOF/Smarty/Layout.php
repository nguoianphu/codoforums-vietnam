<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Smarty;

class Layout {

    public static function load($tpl, $css_files = array(), $js_files = array()) {

        \CODOF\Util::inc_global_views();

        //This sets all variables which will be used by the theme
        require CURR_THEME_PATH . 'theme.php';

        $page = array();

        \CODOF\Hook::call('before_site_head');
        \CODOF\Hook::call('tpl_before_' . str_replace("/", "_", $tpl));

        $asset = new \CODOF\Asset\Stream();

        $page["head"]["css"] = $asset->dumpCSS();

        //\CODOF\Theme\Js::sort_js();

        $page["head"]["js"] = $asset->dumpJS('head');
        $page["body"]["js"] = $asset->dumpJS('body');
        $page["defer"] = json_encode($asset->deferred());

        //after all modification its time for smarty to display the mod data
        $smarty = Single::get_instance();

        $site_title = \CODOF\Util::get_opt('site_title');
        $sub_title = \CODOF\Store::get('sub_title');

        $smarty->assign('site_title', $site_title);
        $smarty->assign('sub_title', $sub_title);
        $smarty->assign('home_title', \CODOF\Store::get('home_title', _t('All topics')));

        $smarty->assign('site_url', \CODOF\Util::get_opt('site_url'));
        $smarty->assign('logged_in', \CODOF\User\CurrentUser\CurrentUser::loggedIn());

        $smarty->assign('login_url', \CODOF\User\User::getLoginUrl());
        $smarty->assign('logout_url', \CODOF\User\User::getLogoutUrl());
        $smarty->assign('register_url', \CODOF\User\User::getRegisterUrl());
        $smarty->assign('profile_url', \CODOF\User\User::getProfileUrl());

        $smarty->assign('page', $page);
        $smarty->assign('CSRF_token', \CODOF\Access\CSRF::get_token());
        $smarty->assign('php_time_now', time());

        $smarty->assign('forum_tags_num', \CODOF\Util::get_opt('forum_tags_num'));
        $smarty->assign('forum_tags_len', \CODOF\Util::get_opt('forum_tags_len'));

        $category = new \CODOF\Forum\Category();
        $canCreateTopicInAtleastOneCategory = $category->canCreateTopicInAtleastOne();
        $smarty->assign('canCreateTopicInAtleastOneCategory', $canCreateTopicInAtleastOneCategory);

        $page = \CODOF\Store::get('rel:canonical_page', isset($_GET['u']) ? $_GET['u'] : '');
        $smarty->assign('canonical', rtrim(RURI, '/') . strip_tags($page));

        if (\CODOF\Store::has('rel:prev')) {

            $smarty->assign('rel_prev', \CODOF\Store::get('rel:prev'));
        }

        if (\CODOF\Store::has('rel:next')) {

            $smarty->assign('rel_next', \CODOF\Store::get('rel:next'));
        }

        if (\CODOF\Store::has('meta:robots')) {

            $smarty->assign('meta_robots', \CODOF\Store::get('meta:robots'));
        }

        $og = array(
            "type" => \CODOF\Store::get('og:type', 'website'),
            "title" => \CODOF\Store::get('og:title', $sub_title . ' | ' . $site_title),
        );

        if (\CODOF\Store::has('og:url')) {

            $og['url'] = \CODOF\Store::get('og:url');
        }
        if (\CODOF\Store::has('og:desc')) {

            $og['desc'] = \CODOF\Store::get('og:desc');
			// nguoianphu remove Multiple spaces and newlines are replaced with a single space.
			$og['desc'] = trim(preg_replace('/\s\s+/', ' ', $og['desc']));
			// nguoianphu Removes special characters.
			$og['desc'] = str_replace(array('#', '[', ']', '(', ')', '*', '/', '>', '!', ':'), '', $og['desc']);
			// $og['desc'] = preg_replace('/[^A-Za-z0-9\-\s]/', '', $og['desc']);
        } else {

            $og['desc'] = \CODOF\Util::get_opt('site_description');
        }
        if (\CODOF\Store::has('og:image')) {

            $og['image'] = \CODOF\Store::get('og:image');
        }


        $smarty->assign('og', $og);

        if (\CODOF\Store::has('article:published')) {

            $smarty->assign('article_published', \CODOF\Store::get('article:published'));
        }

        if (\CODOF\Store::has('article:modified')) {

            $smarty->assign('article_modified', \CODOF\Store::get('article:modified'));
        }

        $I = \CODOF\User\User::get();
        //current user details
        $smarty->assign('I', $I);
        $smarty->assign('can_moderate_posts', $I->can('moderate posts'));

        if (\CODOF\User\CurrentUser\CurrentUser::loggedIn()) {
            $notifier = new \CODOF\Forum\Notification\Notifier();
            $smarty->assign('unread_notifications', $notifier->getNoOfUnread());
        }else {

            $smarty->assign('unread_notifications', 0);
        }

        $html = $smarty->fetch("$tpl.tpl");

        require_once SYSPATH . 'Ext/simplehtmldom/simple_html_dom.php';

        $dom = new \simple_html_dom();
        $dom->load($html, true, false);

        //let plugins modify html
        \CODOF\Hook::call('tpl_after_' . str_replace("/", "_", $tpl), $dom);
        \CODOF\Hook::call('after_site_head', $dom);

        echo $dom->save();
    }

    public static function not_found() {

        $css_files = array();
        $view = "not_found";

        \CODOF\Store::set('sub_title', _t('Page not found'));

        Layout::load($view, $css_files);
    }

    public static function access_denied() {

        $css_files = array();
        $view = 'access_denied';

        \CODOF\Store::set('sub_title', _t('Access denied'));

        Layout::load($view, $css_files);
    }

}
