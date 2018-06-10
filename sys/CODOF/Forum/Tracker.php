<?php

/*
 * @CODOLICENSE
 */

/**
 *
 * All functions related to unread message tracking system
 *
 * Tables for unread message tracking:
 *  - codo_unread_categories
 *  - codo_unread_topics
 *
 */

namespace CODOF\Forum;

class Tracker {

    private $db;

    public function __construct($db = false) {

        if (!$db) {

            $this->db = \DB::getPDO();
        } else {

            $this->db = $db;
        }
    }

    /**
     *
     * Returns count of number of new topics for every category
     * @param type $catid
     * @return type
     */
    public function get_new_topic_counts() {

        $user = \CODOF\User\User::get();

        $time = max(array(NEW_CONTENT_TIME, $user->read_time));

        $qry = 'SELECT COUNT(t.topic_id) AS new_topics,t.cat_id'
                . ' FROM ' . PREFIX . 'codo_topics AS t '
                . ' LEFT JOIN ' . PREFIX . 'codo_unread_topics AS r ON r.uid= ' . $user->id . ' AND t.topic_id=r.topic_id '
                . ' LEFT JOIN ' . PREFIX . 'codo_unread_categories AS c ON c.uid=' . $user->id . ' AND t.cat_id=c.cat_id '
                . ' WHERE '
                . ' ('
                . '     (t.topic_created > ' . $user->created . '  AND r.uid IS NULL) '
                . '     OR (t.topic_created > r.read_time)'
                . ' ) '
                . ' AND (t.topic_created > c.read_time OR c.uid IS NULL)'
                . ' AND t.uid <> ' . $user->id
                . ' AND t.topic_status <> 0 '
                . ' AND t.topic_created > ' . $time
                . ' GROUP BY t.cat_id';

        $obj = $this->db->query($qry);
        $new_topics = $obj->fetchAll();
        $count_new_topics = array();

        foreach ($new_topics as $new_topic) {

            $count_new_topics[$new_topic['cat_id']] = $new_topic['new_topics'];
        }

        return $count_new_topics;
    }

    /**
     *
     * Returns topic ids that are unread i.e new for a category
     * @param type $catid
     */
    public function get_new_topic_ids($catid, $tids) {

        $new_topics = array();

        if (empty($tids)) {

            return $new_topics;
        }

        $user = \CODOF\User\User::get();

        //reduce one sql condition by comparing with the greater time
        $time = max(array(NEW_CONTENT_TIME, $user->read_time));

        $qry = 'SELECT t.topic_id'
                . ' FROM ' . PREFIX . 'codo_topics AS t '
                . ' LEFT JOIN ' . PREFIX . 'codo_unread_topics AS r ON r.uid= ' . $user->id . ' AND t.topic_id=r.topic_id '
                . ' LEFT JOIN ' . PREFIX . 'codo_unread_categories AS c ON c.uid=' . $user->id . ' AND c.cat_id= ' . $catid
                . ' WHERE '
                . ' ('
                . '     (t.topic_created > ' . $user->created . '  AND r.uid IS NULL) '
                . '     OR (t.topic_created > r.read_time)'
                . ' ) '
                . ' AND (t.topic_created > c.read_time OR c.uid IS NULL)'
                . ' AND t.uid <> ' . $user->id
                . ' AND t.cat_id = ' . $catid
                . ' AND t.topic_id IN (' . implode(",", $tids) . ')'
                . ' AND t.topic_status <> 0 '
                . ' AND t.topic_created >= ' . $time;

        $obj = $this->db->query($qry);

        $res = $obj->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($res as $r) {

            $new_topics[$r['topic_id']] = $r['topic_id'];
        }

        return $new_topics;
    }

    /**
     *
     * Calculates topic ids that are unread
     */
    public function get_all_new_topic_ids($tids) {

        $topics = array();

        if (empty($tids)) {

            return $topics;
        }

        $user = \CODOF\User\User::get();
        //reduce one sql condition by comparing with the greater time
        $time = max(array(NEW_CONTENT_TIME, $user->read_time));

        $qry = 'SELECT t.topic_id'
                . ' FROM ' . PREFIX . 'codo_topics AS t '
                . ' LEFT JOIN ' . PREFIX . 'codo_unread_topics AS r ON r.uid= ' . $user->id . ' AND t.topic_id=r.topic_id '
                . ' LEFT JOIN ' . PREFIX . 'codo_unread_categories AS c ON c.uid=' . $user->id . ' AND c.cat_id=t.cat_id '
                . ' WHERE '
                . ' ('
                . '     (t.topic_created > ' . $user->created . '  AND r.uid IS NULL) '
                . '     OR (t.topic_created > r.read_time)'
                . ' ) '
                . ' AND (t.topic_created > c.read_time OR c.uid IS NULL)'
                . ' AND t.uid <> ' . $user->id
                . ' AND t.topic_status <> 0 '
                . ' AND t.topic_id IN (' . implode(",", $tids) . ')'
                . ' AND t.topic_created > ' . $time;
        //   . ' ORDER BY t.last_post_time DESC,t.topic_created DESC  LIMIT 30 OFFSET 0 ';

        $obj = $this->db->query($qry);

        $res = $obj->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($res as $r) {

            $topics[$r['topic_id']] = $r['topic_id'];
        }
        return $topics;
    }

    /**
     *
     * Calculates number of new replies for given topics
     * @param array $tids
     * @return array
     */
    public function get_new_reply_counts($tids) {

        $count_new_posts = array();

        if (empty($tids)) {

            return $count_new_posts;
        }

        $user = \CODOF\User\User::get();

        $time = max(array(NEW_CONTENT_TIME, $user->read_time));

        $qry = 'SELECT COUNT(p.post_id) AS new_posts,p.topic_id, MIN(p.post_id) AS first_unread_post'
                . ' FROM ' . PREFIX . 'codo_posts AS p '
                . ' LEFT JOIN ' . PREFIX . 'codo_unread_topics AS r ON r.uid= ' . $user->id . ' AND p.topic_id=r.topic_id '
                . ' LEFT JOIN ' . PREFIX . 'codo_unread_categories AS c ON c.uid=' . $user->id . ' AND p.cat_id=c.cat_id '
                . ' LEFT JOIN ' . PREFIX . 'codo_topics AS t ON t.topic_id=p.topic_id '
                . ' WHERE '
                . ' ('
                . '     (p.post_created > ' . $user->created . '  AND r.uid IS NULL) '
                . '     OR (p.post_created > r.read_time)'
                . ' ) '
                . ' AND (p.post_created > c.read_time OR c.uid IS NULL)'
                . ' AND p.uid <> ' . $user->id
                . ' AND p.post_id <> t.post_id'
                . ' AND p.topic_id IN (' . implode(",", $tids) . ') '
                . ' AND p.post_status <> 0 '
                . ' AND p.post_created > ' . $time
                . ' GROUP BY p.topic_id';

        $obj = $this->db->query($qry);
        $new_posts = $obj->fetchAll();

        foreach ($new_posts as $new_post) {

            $count_new_posts[$new_post['topic_id']] = array($new_post['new_posts'], $new_post['first_unread_post']);
        }

        return $count_new_posts;
    }

    /**
     *
     * Marks entire forum as read
     */
    public function mark_forum_as_read() {

        $me = \CODOF\User\User::get();

        if ($me->loggedIn()) {

            $uid = $me->id;

            //set the user last read time as current time
            $me->set(array("read_time" => time()));

            $del_cats = "DELETE FROM " . PREFIX . "codo_unread_categories WHERE uid=$uid";
            $this->db->query($del_cats);

            $del_topics = "DELETE FROM " . PREFIX . "codo_unread_topics WHERE uid=$uid";
            $this->db->query($del_topics);
        }
    }

    /**
     *
     * Marks a category as read
     * @param type $cid
     */
    public function mark_category_as_read($cid) {

        if (\CODOF\User\CurrentUser\CurrentUser::loggedIn()) {

            $cat_id = (int) $cid;
            $uid = \CODOF\User\CurrentUser\CurrentUser::id();
            $time = time();


            $cnt = \DB::table(PREFIX . 'codo_unread_categories')
                    ->where('cat_id', '=', $cat_id)
                    ->where('uid', '=', $uid)
                    ->count();

            if ($cnt == 0) {
                
                $qry = "INSERT INTO " . PREFIX . "codo_unread_categories VALUES($cat_id, $uid, $time)";
                $this->db->query($qry);
            } else {

                $qry = "UPDATE " . PREFIX . "codo_unread_categories SET read_time=$time WHERE cat_id=$cat_id AND uid=$uid";
                $this->db->query($qry);
            }


            $qry = "DELETE FROM " . PREFIX . "codo_unread_topics WHERE cat_id=$cat_id AND uid=$uid";
            $this->db->query($qry);
        }
    }

    /**
     *
     * Marks a topic as read
     * @param int $cid Category id
     * @param int $tid Topic id
     */
    public function mark_topic_as_read($cid, $tid) {

        if (\CODOF\User\CurrentUser\CurrentUser::loggedIn()) {

            $tid = (int) $tid;
            $cid = (int) $cid;
            $uid = \CODOF\User\CurrentUser\CurrentUser::id();
            $time = time();

            $pre = PREFIX;
            $res = \DB::select("SELECT COUNT(topic_id) AS cnt FROM {$pre}codo_unread_topics WHERE topic_id=$tid AND uid=$uid");

            if ($res[0]['cnt']) {
                $qry = "UPDATE " . PREFIX . "codo_unread_topics SET read_time=$time WHERE topic_id=$tid AND uid=$uid";
                $this->db->query($qry);
            } else {

                $qry = "INSERT INTO " . PREFIX . "codo_unread_topics VALUES($cid, $tid, $uid, $time)";
                $this->db->query($qry);
            }
        }
    }

    /**
     * TO BE IMPLEMENTED IN FUTURE
     * Marks a post as read
     * @param type $post_id
     */
    public function mark_post_as_read($post_id) {

        return $post_id;
    }

}
