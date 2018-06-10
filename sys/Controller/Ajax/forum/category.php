<?php

/*
 * @CODOLICENSE
 */

namespace Controller\Ajax\forum;

class category {

    public function __construct() {
        $this->db = \DB::getPDO();
    }

    public function get_topics($catid, $page) {

        $topic = new \CODOF\Forum\Topic($this->db);
        $topics = array();
        $cid = (int) $catid;
        
        $num_pages = 'not_passed';
        if (isset($_GET['get_page_count']) && $_GET['get_page_count'] == 'yes') {

            $num_pages = 'calc_count';
        }

        $new_topics = array();
        $new_replies = array();

        if (isset($_GET['str']) && $_GET['str'] != "") {

            $user = \CODOF\User\User::get();
            if(!$user->can('use search')) {
                
                exit('permission denied');
            }
            
            $search = new \CODOF\Search\Search();
            $search->str = $_GET['str'];
            $search->num_results = \CODOF\Util::get_opt("num_posts_cat_topics");
            $search->from = ($page-1) * $search->num_results;

            if ($num_pages == 'calc_count') {

                $search->count_rows = true;
            }
            $cats = (int) $_GET['catid'];

            $search->cats = $cats;
            $search->match_titles = $_GET['match_titles'];
            $search->order = $_GET['order'];
            $search->sort = $_GET['sort'];
            $search->time_within = $_GET['search_within'];

            $res = $search->search();

            if ($num_pages == 'calc_count') {

                $num_pages = $search->get_total_count();
            }

            $_topics = $topic->gen_topic_arr_all_topics($res, $search);

            $tids = array();
            foreach ($topics as $_topic) {

                $tids[] = $_topic['topic_id'];
            }

            //var_dump($topics);
        } else {

            //$num_pages = $topic->get_num_pages(
            //        $topic->get_num_topics($cid), \CODOF\Util::get_opt("num_posts_cat_topics")
            //);
            $num_pages = 'not_passed';
            $topics = $topic->get_topics($cid, $page);
            $tids = array();
            foreach ($topics as $_topic) {

                $tids[] = $_topic['topic_id'];
            }


            if (\CODOF\User\CurrentUser\CurrentUser::loggedIn()) {

                $tracker = new \CODOF\Forum\Tracker($this->db);
                $topic->new_topic_ids = $tracker->get_new_topic_ids($cid, $tids);
                $topic->new_replies = $tracker->get_new_reply_counts($tids);
            }

            $topic->tags = $topic->getAllTags($tids);

            $_topics = $topic->gen_topic_arr($topics, $cid);
        }

        return array(
            "topics" => $_topics,
            "new_topics" => $topic->new_topic_ids,
            "page_no" => $page,
            "num_pages" => $num_pages
        );
    }

}
