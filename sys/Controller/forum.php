<?php

/*
 * @CODOLICENSE
 */

namespace Controller;

/*
 *
 * Links
 *
 * When new topic is created
 * inc num_posts, num_topics in codo_topics
 * inc num_posts in codo_users
 *
 * When new post is created
 * inc num_posts in codo_topics
 * inc num_posts in codo_users
 *
 */

/*
 * forum
 * topics -> displays all topics
 * category/:cat_name -> displays topics of that category
 * topic/:topicid/ [add topicname to the url] -> displays posts for that topic
 * topic/:topicid#post-postid -> displays(scrolls to) that post for that topic
 *
 * post <=> comment
 */

use CODOF\Access\Access;

class forum {

    private $db;
    private $smarty;
    public $css_files = array();
    public $js_files = array();

    public function __construct() {
        $this->smarty = \CODOF\Smarty\Single::get_instance();
        $this->db = \DB::getPDO();
    }

    public function manage_topic($id = false) {

        $topic_info = '';
        $topic = new \CODOF\Forum\Topic($this->db);

        if ($id) {

            $tid = (int) $id;
            $qry = 'SELECT t.topic_id,t.title, t.cat_id, t.uid,t.topic_close,t.topic_status, c.cat_name, p.imessage '
                    . 'FROM ' . PREFIX . 'codo_topics AS t '
                    . 'INNER JOIN ' . PREFIX . 'codo_categories AS c ON c.cat_id=t.cat_id '
                    . 'INNER JOIN ' . PREFIX . 'codo_posts AS p ON p.topic_id=t.topic_id '
                    . 'WHERE t.topic_id=' . $tid;
            $res = $this->db->query($qry);

            $topic_info = $res->fetch();
            //i have come to edit the topic

            $tuid = $topic_info['uid'];
            $cid = $topic_info['cat_id'];

            $has_permission = $topic->canViewTopic($tuid, $cid, $tid) &&
                    $topic->canEditTopic($tuid, $cid, $tid);
        } else {

            $topic_info = array(
                "title" => "",
                "imessage" => "",
                "topic_status" => 0,
                "cat_id" => 0,
                "topic_close" => 0,
                "topic_id" => 0
            );

            //i have come to create a new topic

            $has_permission = $topic->canCreateTopicInAtleastOne();
        }

        if ($has_permission) {


            $tags = '';
            if ($id) {

                $_tags = $topic->getTags($id);

                if ($_tags) {

                    $tags = implode(",", $_tags);
                }
                \CODOF\Store::set('sub_title', _t('Edit topic ') . $topic_info['title']);
            } else {

                \CODOF\Store::set('sub_title', _t('Create topic'));
            }

            $this->smarty->assign('tags', $tags);

            $cat = new \CODOF\Forum\Category($this->db);

            $cats = $cat->generate_tree($cat->getCategoriesWhereUserCanCreateTopic());

            $this->smarty->assign('cats', $cats);
            $this->assign_editor_vars();

            $this->smarty->assign('topic', $topic_info);

            //$this->smarty->assign('sticky_checked', \CODOF\Forum\Forum::isSticky($topic_info['topic_status']));
            //$this->smarty->assign('frontpage_checked', $topic_info['topic_status'] == \CODOF\Forum\Forum::STICKY);

            if ($topic_info['topic_status'] == \CODOF\Forum\Forum::STICKY ||
                    $topic_info['topic_status'] == \CODOF\Forum\Forum::STICKY_CLOSED) {

                $radio = 'stickyfc';
            } else if ($topic_info['topic_status'] == \CODOF\Forum\Forum::STICKY_ONLY_CATEGORY || $topic_info['topic_status'] == \CODOF\Forum\Forum::STICKY_ONLY_CATEGORY_CLOSED) {

                $radio = 'stickyc';
            } else {

                $radio = 'notsticky';
            }

            $this->smarty->assign('radio_topic_status', $radio);
            $this->smarty->assign('is_topic_open', !\CODOF\Forum\Forum::isClosed($topic_info['topic_status']));
            $this->smarty->assign('is_auto_close', $topic_info['topic_close'] > 0);

            if ($topic_info['topic_close'] > 0) {

                $this->smarty->assign('auto_close_date', date('Y-m-d', $topic_info['topic_close']));
            }

            $user = \CODOF\User\User::get();
            $this->smarty->assign('can_make_sticky', $user->can('make sticky'));
            $this->smarty->assign('can_close_topics', $user->can('close topics'));
            $this->smarty->assign('can_add_tags', $user->can('add tags'));

            $this->css_files = array('new_topic', 'editor', 'jquery.textcomplete', 'datetimepicker');

            $arr = array(
                array(DATA_PATH . "assets/js/bootstrap-tagsinput.min.js", array('type' => 'defer')),
                array(DATA_PATH . "assets/js/moment.js", array('type' => 'defer')),
                array(DATA_PATH . "assets/js/jquery.datetimepicker.js", array('type' => 'defer'))
            );

            $this->js_files = array_merge($arr, $cat->get_js_editor_files());

            $this->view = 'forum/new_topic';
        } else if (!\CODOF\User\CurrentUser\CurrentUser::loggedIn()) {

            header('Location: ' . \CODOF\User\User::getProfileUrl());
        } else {

            \CODOF\Store::set('sub_title', _t('Access denied'));
            $this->view = 'access_denied';
        }
    }

    public function category($catid, $page) {

        $cat = new \CODOF\Forum\Category($this->db);

        $cat_info = $cat->get_cat_info($catid);
        $cid = $cat_info['cat_id'];

        $user = \CODOF\User\User::get();
        if (!$cat_info) {

            $this->view = 'not_found';
            return;
        }
        if (!$user->can('view category', $cid)) {

            $this->view = 'access_denied';
            return;
        }


        $cats = $cat->get_categories();
        $cats_tree = $cat->generate_tree($cats);

        $sub_cats = $cat->get_sub_categories($cats_tree, $cid);
        $this->smarty->assign('parents', $cat->find_parents($cats, $cid));

        $this->smarty->assign('cats', $cats_tree);
        $this->smarty->assign('sub_cats', $sub_cats);
        //$num_results = \CODOF\Util::get_opt("num_posts_cat_topics");

        $subscriber = new \CODOF\Forum\Notification\Subscriber();
        $this->smarty->assign('no_followers', $subscriber->followersOfCategory($cid));

        if (\CODOF\User\CurrentUser\CurrentUser::loggedIn()) {

            $this->smarty->assign('my_subscription_type', $subscriber->levelForCategory($cid));
        }

        $api = new Ajax\forum\category();
        $num_topics_page = \CODOF\Util::get_opt('num_posts_cat_topics');
        $data = $api->get_topics($cid, $page);

        $this->smarty->assign('load_more_hidden', false);
        if (($page) * $num_topics_page >= $cat_info['no_topics']) {

            $this->smarty->assign('load_more_hidden', true);
        }

        if (isset($_GET['search']) && $_GET['search'] != null) {

            //$search_conds = json_decode($_GET['search']);
            $search_data = $_GET['search'];
        } else {

            $search_data = '{}';
        }

        $user = \CODOF\User\User::get();
        $this->smarty->assign('new_topics', $data['new_topics']);
        $this->smarty->assign('can_create_topic', $cat->canCreateTopicIn($cid));
        $this->smarty->assign('can_search', $user->can('use search'));
        $this->smarty->assign('search_data', $search_data);
        $this->smarty->assign('topics', \CODOF\HB\Render::tpl('forum/category', $data));
        $this->smarty->assign('cat_info', $cat_info);
        $this->smarty->assign('cat_alias', $catid);
        $this->smarty->assign('curr_page', $page);
        $this->smarty->assign('num_posts_per_page', $num_topics_page);
        $this->assign_editor_vars();

        $no_topics = $no_posts = '&nbsp;&nbsp;&nbsp;-- ';
        if ($user->can('view all topics', $cid)) {

            $no_topics = \CODOF\Util::abbrev_no($cat_info['no_topics'], 2);
            $no_posts = \CODOF\Util::abbrev_no($cat_info['no_posts'], 2);
        }

        $this->smarty->assign('no_topics', $no_topics . " ");
        $this->smarty->assign('no_posts', $no_posts . " ");


        $this->css_files = array('category', 'editor', 'jquery.textcomplete', 'datetimepicker');
        $this->js_files = array(
            array('category/category.js', array('type' => 'defer')),
            array('category/jquery.easing.1.3.js', array('type' => 'defer')),
            array('bootstrap-tagsinput.js', array('type' => 'defer')),
            array('bootstrap-slider.js', array('type' => 'defer')),
            array(DATA_PATH . "assets/js/jquery.datetimepicker.js", array('type' => 'defer'))
        );
        $this->js_files = array_merge($this->js_files, $cat->get_js_editor_files());

        $this->smarty->assign('can_make_sticky', $user->can('make sticky'));

        $this->view = 'forum/category';


        $this->smarty->assign('radio_topic_status', 'notsticky');
        $this->smarty->assign('is_topic_open', true);
        $this->smarty->assign('is_auto_close', 0);

        $this->smarty->assign('can_make_sticky', $user->can('make sticky'));
        $this->smarty->assign('can_add_tags', $user->can('add tags'));
        $this->smarty->assign('can_close_topics', $user->can('close topics'));

        \CODOF\Hook::call('on_category_view', array($cat_info));

        \CODOF\Store::set('rel:canonical_page', '/');
        \CODOF\Store::set('sub_title', $cat_info['cat_name']);
        \CODOF\Store::set('og:url', RURI . 'category/' . $catid);
        \CODOF\Store::set('og:desc', $cat_info['cat_description']);
        \CODOF\Store::set('og:image', DURI . CAT_IMGS . $cat_info['cat_img']);
    }

    public function topics($page) {

        $cat = new \CODOF\Forum\Category($this->db);
        $topic = new \CODOF\Forum\Topic($this->db);

        //gets category name and no of topics each hold
        $raw_cats = $cat->get_categories();
        $cats = $cat->generate_tree($raw_cats);
        //get complete list of topics
        $new_topics = array();

        if (\CODOF\User\CurrentUser\CurrentUser::loggedIn()) {

            $tracker = new \CODOF\Forum\Tracker($this->db);
            $new_topics = $tracker->get_new_topic_counts();
        }

        $this->smarty->assign('new_topics', $new_topics);
        //$cat->update_count($cats);

        $api = new Ajax\forum\topics();
        $num_topics_page = \CODOF\Util::get_opt('num_posts_all_topics');
        $data = $api->get_topics($num_topics_page * ($page - 1));
        $total_topics = $topic->get_total_num_topics();

        $this->smarty->assign('load_more_hidden', false);
        if (($page) * $num_topics_page >= $total_topics) {

            $this->smarty->assign('load_more_hidden', true);
        }

        $user = \CODOF\User\User::get();
        $this->smarty->assign('can_search', $user->can('use search'));
        $this->smarty->assign('topics', \CODOF\HB\Render::tpl('forum/topics', $data));
        $this->smarty->assign('total_num_topics', $total_topics);
        $this->smarty->assign('cats', $cats);
        $this->smarty->assign('subcategory_dropdown', \CODOF\Util::get_opt('subcategory_dropdown'));
        $this->smarty->assign('num_posts_per_page', $num_topics_page);
        $this->smarty->assign('curr_page', $page);

        $this->css_files = array('topics');
        $this->js_files = array(array('topics/topics.js', array('type' => 'defer')));
        $this->view = 'forum/topics';
        \CODOF\Store::set('sub_title', _t('All topics'));

        $report = new \CODOF\Forum\Report();
        $this->smarty->assign('report_types', $report->getReportTypes());
        $this->smarty->assign('can_delete', $user->can(array('delete my topics', 'delete all topics')));
        $this->smarty->assign('can_merge', $user->can('merge topics'));
        $this->smarty->assign('can_move', $user->can('move topics'));

        //var_dump($user->can('move topics'));
    }

    public function topic($tid, $page) {

        $topic = new \CODOF\Forum\Topic($this->db);
        $post = new \CODOF\Forum\Post($this->db);
        $user = \CODOF\User\User::get();

        if (isset($_GET['page']) && $_GET['page'] == 'from_notify') {
            $nid = $_GET['nid'];
            \DB::table(PREFIX . 'codo_notify')
                    ->where('is_read', '=', '0')
                    ->where('id', '=', $nid)
                    ->where('uid', '=', $user->id) //security purposes
                    ->update(array("is_read" => '1'));
        }


        $topic_info = $topic->get_topic_info($tid);

        if ($topic_info['topic_status'] == \CODOF\Forum\Forum::MERGED_REDIRECT_ONLY) {

            $tid = $topic_info['redirect_to'];
            $topic_info = $topic->get_topic_info($tid);
        }

        if ($topic_info['topic_status'] == \CODOF\Forum\Forum::MODERATION_BY_FILTER) {

            $topic_is_spam = true;
        } else {

            $topic_is_spam = false;
        }

        $this->smarty->assign('topic_is_spam', $topic_is_spam);

        if ($topic_is_spam) {
            if (!($user->can('moderate topics') || $user->id == $topic_info['uid'])) {

                $this->view = 'access_denied';
                return false;
            }
        }

        if (!$topic->canViewTopic($topic_info['uid'], $topic_info['cat_id'], $topic_info['topic_id'])) {

            //\CODOF\Hook::call('page not found', array('type' => 'topic', 'id' => $tid));
            \CODOF\Store::set('sub_title', _t('Access denied'));
            $this->view = 'access_denied';
            return;
        }

        $tracker = new \CODOF\Forum\Tracker($this->db);
        $tracker->mark_topic_as_read($topic_info['cat_id'], $tid);

        if (!$topic_info) {

            $this->view = 'not_found';
        } else {

            $posts_per_page = \CODOF\Util::get_opt("num_posts_per_topic");

            if (strpos($page, "post-") !== FALSE) {

                $pid = (int) str_replace("post-", "", $page);

                $prev_posts = $post->get_num_prev_posts($tid, $pid);
                $from = floor(($prev_posts) / $posts_per_page);
            } else {

                $from = ((int) $page) - 1;
            }

            $topic_info['no_replies'] = $topic_info['no_posts'] - 1;
            $name = \CODOF\Filter::URL_safe($topic_info['title']);

            $subscriber = new \CODOF\Forum\Notification\Subscriber();

            $this->smarty->assign('no_followers', $subscriber->followersOfTopic($topic_info['topic_id']));

            if (\CODOF\User\CurrentUser\CurrentUser::loggedIn()) {

                $this->smarty->assign('my_subscription_type', $subscriber->levelForTopic($topic_info['topic_id']));
            }

            $this->smarty->assign('tags', $topic->getTags($topic_info['topic_id']));

            $topic_status = $topic_info['topic_status'];

            $api = new Ajax\forum\topic();
            $posts_data = $api->get_posts($tid, $from, $topic_info);
            $num_pages = $posts_data['num_pages'];
            $posts = $posts_data['posts'];
            $is_closed = $topic_status == \CODOF\Forum\Forum::APPROVED_CLOSED ||
                    $topic_status == \CODOF\Forum\Forum::STICKY_CLOSED || $topic_status == \CODOF\Forum\Forum::STICKY_ONLY_CATEGORY_CLOSED;

            $posts_data['is_closed'] = $is_closed;
            $posts_tpl = \CODOF\HB\Render::tpl('forum/topic', $posts_data);
            $this->smarty->assign('posts', $posts_tpl);
            $this->smarty->assign('topic_info', $topic_info);

            $this->smarty->assign('is_closed', $is_closed);
            // $this->smarty->assign('title', htmlentities($topic_info['title'], ENT_QUOTES, "UTF-8"));
            $this->smarty->assign('title', $topic_info['title']);

            $search_data = array();
            if (isset($_GET['str'])) {
                $search_data = array('str' => strip_tags($_GET['str']));
            }
            $this->smarty->assign('search_data', json_encode($search_data));


            $url = 'topic/' . $topic_info['topic_id'] . '/' . $name . '/';
            $this->smarty->assign('pagination', $post->paginate($num_pages, $from + 1, $url, false, $search_data));


            if (ceil(($topic_info['no_posts'] + 1 ) / $posts_per_page) > $num_pages) {

                //next reply will go to next page
                $this->smarty->assign('new_page', 'yes');
            } else {

                $this->smarty->assign('new_page', 'nope');
            }

            $cat = new \CODOF\Forum\Category($this->db);
            $cats = $cat->get_categories();
            $cid = $topic_info['cat_id'];
            $parents = $cat->find_parents($cats, $cid);

            array_push($parents, array(
                "name" => $topic_info['cat_name'],
                "alias" => $topic_info['cat_alias']
            ));

            $this->smarty->assign('can_search', $user->can('use search'));

            $this->smarty->assign('parents', $parents);
            $this->smarty->assign('num_pages', $num_pages);
            $this->smarty->assign('curr_page', $from + 1); //starts from 1
            $this->smarty->assign('url', RURI . $url);
            $this->assign_editor_vars();

            $tuid = $topic_info['uid'];
            $this->assign_admin_vars($tuid);

            $this->css_files = array('topic', 'editor', 'jquery.textcomplete');
            $arr = array(
                array('topic/topic.js', array('type' => 'defer')),
                array('modal.js', array('type' => 'defer')),
                array('bootstrap-slider.js', array('type' => 'defer'))
            );

            $this->js_files = array_merge($arr, $post->get_js_editor_files());

            \CODOF\Hook::call('on_topic_view', array($topic_info));

            $this->view = 'forum/topic';
            \CODOF\Store::set('sub_title', $topic_info['title']);
            \CODOF\Store::set('og:type', 'article');
            \CODOF\Store::set('og:title', $topic_info['title']);
            \CODOF\Store::set('og:url', RURI . $url);

            $mesg = $posts[0]['imessage'];
            \CODOF\Store::set('og:desc', (strlen($mesg) > 200) ? substr($mesg, 0, 197) . "..." : $mesg);

			// nguoianphu
			// get image link for Facebook sharing
			// get the featured image
			$image = $posts[0]['message'];
			// get the src for that image
			$img_src = 'http://www.nguoianphu.com/sites/default/assets/img/cats/5520bd0f7d055nguoianphu_logo420.png';
			$pattern = '/src="([^"]*)"/';
			preg_match($pattern, $image, $matches);
			if (!empty($matches[1])){
				$img_src = $matches[1];
			}
			unset($matches);
			// \CODOF\Store::set('og:image', ($img_src != '') ? $img_src : 'http://www.nguoianphu.com/sites/default/assets/img/cats/5520bd0f7d055nguoianphu_logo420.png');
			\CODOF\Store::set('og:image', $img_src);
			// nguoianphu
			
            if ($from > 0) {

                //previous page exists
                \CODOF\Store::set('rel:prev', RURI . $url . $from);
            }
            $curr_page = $from + 1;

            if ($curr_page < $num_pages) {
                //next page exists
                \CODOF\Store::set('rel:next', RURI . $url . ($curr_page + 1));
            }

            \CODOF\Store::set('article:published', date('c', $topic_info['topic_created']));

            if ($topic_info['topic_updated'] > 0) {

                \CODOF\Store::set('article:modified', date('c', $topic_info['topic_updated']));
            }
        }
    }

    public function listTaggedTopics($tag, $page = 1) {

        $posts_per_page = \CODOF\Util::get_opt("num_posts_all_topics");

        if ($page == null) {

            $page = 1;
        }

        $page = (int) $page;

        if ($page <= 1) {

            $from = 0;
        } else {

            $from = ($page - 1) * $posts_per_page;
        }

        $topics = new \Controller\Ajax\forum\topics();
        $taggedTopics = $topics->getTaggedTopics($tag, $from);


        $topic = new \CODOF\Forum\Topic($this->db);

        $num_pages = $topic->get_num_pages(
                $topic->getTaggedTopicsCount($tag), $posts_per_page
        );

        $url = 'tags/' . $tag . '/';
        $curr_page = $page;

        //var_dump($taggedTopics);
        $this->smarty->assign('tag', $tag);
        $this->smarty->assign('curr_page', $curr_page);
        $this->smarty->assign('url', RURI . $url);
        $this->smarty->assign('num_pages', $num_pages);
        $this->smarty->assign('topics', json_encode($taggedTopics));
        $this->smarty->assign('tags', json_encode($taggedTopics['tags']));

        $this->css_files = array('tags');
        $this->js_files = array(array('tags/tags.js', array('type' => 'defer')));

        $this->view = 'forum/tags';

        \CODOF\Store::set('sub_title', $tag . ' - ' . _t('Tags'));
    }

    private function assign_editor_vars() {

        $this->smarty->assign('max_file_size', \CODOF\Util::get_opt('forum_attachments_size'));
        $this->smarty->assign('allowed_file_mimetypes', \CODOF\Util::get_opt('forum_attachments_mimetypes'));
        $this->smarty->assign('forum_attachments_parallel', \CODOF\Util::get_opt('forum_attachments_parallel'));
        $this->smarty->assign('forum_attachments_multiple', \CODOF\Util::get_opt('forum_attachments_multiple'));
        $this->smarty->assign('forum_attachments_max', \CODOF\Util::get_opt('forum_attachments_max'));
        $this->smarty->assign('forum_smileys', json_encode(\CODOF\Util::get_smileys($this->db)));
        $this->smarty->assign('reply_min_chars', \CODOF\Util::get_opt('reply_min_chars'));
    }

    private function assign_admin_vars($tuid) {

        if ($tuid == \CODOF\User\CurrentUser\CurrentUser::id()) {

            //this topic belongs to current user
            $this->smarty->assign('can_edit_topic', json_encode(Access::hasPermission(array('edit my topics', 'edit all topics'))));
            $this->smarty->assign('can_delete_topic', json_encode(Access::hasPermission(array('delete my topics', 'delete all topics'))));
        } else {

            $this->smarty->assign('can_edit_topic', json_encode(Access::hasPermission('edit all topics')));
            $this->smarty->assign('can_delete_topic', json_encode(Access::hasPermission('delete all topics')));
        }
    }

}
