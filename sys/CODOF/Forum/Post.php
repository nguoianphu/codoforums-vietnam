<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Forum;

class Post extends Forum {

    public $topic_post_id = 0;
    public $cat_id;
    public $topic_status = Forum::APPROVED;
    public $tid = 0;
    public $safe_title = "";
    public $tuid = 0;

    /**
     *
     * @var PDO 
     */
    protected $db;

    public function __construct(\PDO $db = null) {

        $this->db = $db;
    }

    /**
     * 
     * Gets information of posts of given topic id paginated
     * 
     * @param type $tid
     * @param type $from
     * @return type
     */
    public function get_posts($tid, $from = 0) {

        //$tid is converted to integer so its safe
        //show oldest first
        $posts = array();
        $num_posts = \CODOF\Util::get_opt("num_posts_per_topic");
        
        if($num_posts  < 0) $num_posts = 0;
        if($from < 0) $from = 0;
        $from *= $num_posts;

        $qry = "SELECT u.id, r.rid, u.name AS name, u.avatar, u.no_posts, "
                . "u.signature, p.post_id, p.omessage AS message,p.imessage, "
                . "p.post_created, p.post_modified, p.reputation "
                . "FROM codo_posts AS p "
                . "LEFT JOIN codo_users AS u ON u.id=p.uid "
                . "LEFT JOIN codo_user_roles AS r ON r.uid=p.uid AND r.is_primary=1 "
                . "WHERE p.topic_id=$tid AND p.post_status=1 "
                . "ORDER BY case when p.post_id=".$this->topic_post_id." then 0 else 1 end, post_created "
                . "LIMIT " . $num_posts . " OFFSET " . $from;

        $res = $this->db->query($qry);

        if ($res) {

            $posts = $this->gen_posts_arr($res->fetchAll());
        }

        return $posts;
    }

    /**
     * Gets information on a  post
     * @param int $pid
     * @return array [cat_id, topic_id, uid]
     */
    public function get_post_info($pid) {

        $pid = (int) $pid;

        $qry = 'SELECT cat_id, topic_id, uid FROM ' . PREFIX . 'codo_posts WHERE post_id=' . $pid;
        $obj = $this->db->query($qry);
        $res = $obj->fetch();

        return $res;
    }

    public function get_second_last_post_info($tid) {

        $tid = (int) $tid;

        $res = \DB::table(PREFIX . 'codo_posts AS p')
                        ->select('post_id AS last_post_id', 'p.uid AS last_post_uid', 'u.name AS last_post_name', 'p.post_created AS last_post_time')
                        ->leftJoin(PREFIX . 'codo_users AS u', 'u.id', '=', 'p.uid')
                        ->where('p.topic_id', '=', \DB::raw($tid))
                        ->where('p.post_status', '<>', 0)
                        ->orderBy('post_id', 'desc')
                        ->skip(1)->take(2)->get();


        if (count($res) === 2) {

            return $res[0]; //this is the second last post
        }

        return array(
            'last_post_id' => 0,
            'last_post_uid' => null,
            'last_post_name' => null,
            'last_post_time' => \DB::raw('topic_created')
        );
    }

    public function get_last_post_info($tid) {

        $tid = (int) $tid;

        $qry = 'SELECT post_id AS last_post_id,p.uid AS last_post_uid,u.name AS last_post_name,p.post_created AS last_post_time'
                . ' FROM ' . PREFIX . 'codo_posts AS p,'
                . PREFIX . 'codo_users AS u'
                . ' WHERE p.uid=u.id AND p.post_status<>0 AND p.post_id='
                . '     (SELECT MAX(post_id) FROM codo_posts WHERE topic_id=' . $tid . ' AND post_status<>0)';
        $obj = $this->db->query($qry);
        $res = $obj->fetch(\PDO::FETCH_ASSOC);

        return $res;
    }

    /**
     * 
     * Gets number of posts made before the post passed for the topic passed
     * @param int $tid topic id
     * @param int $pid post id
     * @return int
     */
    public function get_num_prev_posts($tid, $pid) {

        $qry = 'SELECT COUNT(post_id) FROM ' . PREFIX . 'codo_posts WHERE topic_id=' . $tid . ' AND post_id<' . $pid;
        $obj = $this->db->query($qry);
        $res = $obj->fetch();

        return (int) $res[0];
    }

    /**
     * 
     * Sets the given post(post id) status
     * @param type $pid
     */
    public function set_status_by_post_id($pid, $option) {

        $post_status = $this->get_status($option);
        $pid = (int) $pid;

        $qry = 'UPDATE ' . PREFIX . 'codo_posts SET post_status=' . $post_status . ' WHERE post_id=' . $pid;
        $this->db->query($qry);
    }

    /**
     * 
     * Sets the given post(topic id) status
     * @param type $pid
     */
    public function set_status_by_topic_id($pid, $option) {

        $post_status = $this->get_status($option);
        $_pid = (int) $pid;

        $qry = 'UPDATE ' . PREFIX . 'codo_posts SET post_status=' . $post_status . ' WHERE topic_id=' . $_pid;
        $stmt = $this->db->prepare($qry);
        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * Deletes all posts of a topic and updates all post counts
     * @param int $cid Category id
     * @param int $tid Topic id
     */
    public function deleteOfTopic($cid, $tid) {

        $qry = 'SELECT uid,COUNT(post_id) AS num_posts FROM ' . PREFIX . 'codo_posts WHERE topic_id=' . $tid . ' AND post_status<>0 GROUP BY uid';
        $obj = $this->db->query($qry);
        $userDetails = $obj->fetchAll();

        $qry = "UPDATE codo_users SET no_posts=no_posts-:count WHERE id=:uid";
        $userObj = $this->db->prepare($qry);


        foreach ($userDetails as $userDetail) {

            $userObj->execute(array("count" => $userDetail['num_posts'], "uid" => $userDetail['uid']));
        }

        $this->set_status_by_topic_id($tid, 'DELETE');

        $this->decPostCount($cid, $tid, false, $userDetail['num_posts']);
    }

    /**
     * Deletes post updates all post counts
     * @param int $cid Category id
     * @param int $tid Topic id
     */
    public function delete($pid) {

        $pid = (int) $pid;

        $info = $this->get_post_info($pid);
        $cid = $info['cat_id'];
        $tid = $info['topic_id'];
        $uid = $info['uid'];

        $recent_post = $this->get_last_post_info($tid);

        if ($recent_post['last_post_id'] === $pid) {

            //this was the recent post, now the post before this will 
            //become the most recent post
            $second_recent_post = $this->get_second_last_post_info($tid);

            //so second last post exists, but what if it is the topic post itself ?
            //that is handled by above method, so no worries.
            \DB::table(PREFIX . 'codo_topics')
                    ->where('topic_id', '=', $tid)
                    ->update($second_recent_post);
        }

        $this->set_status_by_post_id($pid, 'DELETE');
        $this->decPostCount($cid, $tid, $uid);
    }

    /**
     * Deletes post updates all post counts
     * @param int $cid Category id
     * @param int $tid Topic id
     */
    public function undelete($pid) {

        $this->set_status_by_post_id($pid, 'ACTIVE');

        $info = $this->get_post_info($pid);
        $cid = $info['cat_id'];
        $tid = $info['topic_id'];
        $uid = $info['uid'];

        $recent_post = $this->get_last_post_info($tid);

        if ($recent_post['last_post_id'] === $pid) {

            \DB::table(PREFIX . 'codo_topics')
                    ->where('topic_id', '=', $tid)
                    ->update($recent_post);
        }

        $this->incPostCount($cid, $tid, $uid);
    }

    /**
     * 
     * Used when editing post , updates post with new message
     * @param type $pid
     * @param type $imesg
     * @param type $omesg
     */
    public function update_post($pid, $imesg, $omesg) {

        $time = time();

        $old = \DB::table(PREFIX . 'codo_posts')->where('post_id', $pid)
                        ->select('imessage', 'post_created', 'post_modified')->first();

        \DB::table(PREFIX . 'codo_edits')->insert(array(
            'post_id' => $pid,
            'uid' => \CODOF\User\CurrentUser\CurrentUser::id(),
            'text' => \CODOF\Format::imessage($old['imessage']),
            //if first edit, then post_modified is null, so get time from post_created
            'time' => $old['post_modified'] == null ? $old['post_created'] : $old['post_modified']
        ));

        $qry = 'UPDATE ' . PREFIX . 'codo_posts SET imessage=:imesg, omessage=:omesg, post_modified=:time'
                . ' WHERE post_id=:pid';

        $stmt = $this->db->prepare($qry);
        $stmt->execute(array(
            ":imesg" => \CODOF\Format::imessage($imesg),
            ":omesg" => \CODOF\Format::omessage($omesg),
            ":time" => $time,
            ":pid" => $pid
        ));
    }

    /**
     * Inserts a new post in codo_posts
     * 
     * @param type $catid
     * @param type $tid
     * @param type $imesg
     * @param type $omesg
     */
    public function ins_post($catid, $tid, $imesg, $omesg, $needsModeration = false) {

        \CODOF\Hook::call('before_post_insert');

        $time = time();

        $uid = $_SESSION[UID . 'USER']['id'];

        $post_status = Topic::APPROVED;

        if ($needsModeration) {

            $post_status = Topic::MODERATION_BY_FILTER;
        }
        //$message = \CODOF\Filter::msg_safe($mesg);
        //$mesg = nl2br($message);

        $qry = 'INSERT INTO codo_posts (topic_id,cat_id,uid,imessage,omessage,post_created,post_status) '
                . 'VALUES(:tid, :cid, :uid, :imesg, :omesg, :post_created,:post_status)';

        $stmt = $this->db->prepare($qry);

        $params = array(
            ":tid" => $tid,
            ":cid" => $catid,
            ":uid" => $uid,
            ":imesg" => \CODOF\Format::imessage($imesg),
            ":omesg" => \CODOF\Format::omessage($omesg),
            ":post_created" => $time,
            ":post_status" => $post_status
        );

        $this->success = $stmt->execute($params);
        $pid = $this->db->lastInsertId();

        if ($this->success && !$needsModeration) {

            $this->incPostCount($catid, $tid, $uid);
            \CODOF\Hook::call('after_post_insert', $pid);
            return $pid;
        }

        return false;
    }

    public function getHistory($post_id) {

        $pid = (int) $post_id;

        $rows = \DB::table(PREFIX . 'codo_edits AS e')
                ->select('u.id', 'u.username', 'u.avatar', 'e.text', 'e.time')
                ->join(PREFIX . 'codo_users AS u', 'e.uid', '=', 'u.id')
                ->where('e.post_id', $pid)
                ->orderBy('e.time', 'DESC')
                ->get();

        $edits = array();
        foreach ($rows as $row) {

            $edits[] = array(
                'uid' => $row['id'],
                'username' => $row['username'],
                'avatar' => \CODOF\Util::get_avatar_path($row['avatar'], $row['id']),
                'text' => nl2br(htmlentities($row['text'], ENT_QUOTES, "UTF-8")),
                'time' => \CODOF\Time::get_pretty_time($row['time'])
            );
        }

        echo json_encode($edits);
    }

    /**
     * Increments post count in all tables by [$count]
     * 
     * @param int $cid Category id
     * @param int $tid Topic id
     * @param int $uid User id
     * @param int $count Increment count
     */
    public function incPostCount($cid, $tid, $uid, $count = 1) {

        $qry = 'UPDATE codo_categories SET no_posts = no_posts+' . $count . ' WHERE cat_id=' . $cid;
        $this->db->query($qry);

        $qry = 'UPDATE codo_topics SET no_posts = no_posts+' . $count . ' WHERE topic_id=' . $tid;
        $this->db->query($qry);

        $qry = 'UPDATE codo_users SET no_posts = no_posts+' . $count . ' WHERE id=' . $uid;
        $this->db->query($qry);

        \CODOF\Util::set_promoted_or_demoted_rid();
    }

    /**
     * Decrements post count in all tables by [$count]
     * 
     * @param int $cid Category id
     * @param int $tid Topic id
     * @param int $uid User id
     * @param int $count Increment count
     */
    public function decPostCount($cid, $tid, $uid = false, $count = 1) {

        $qry = 'UPDATE codo_categories SET no_posts = no_posts-' . $count . ' WHERE cat_id=' . $cid;
        $this->db->query($qry);

        $qry = 'UPDATE codo_topics SET no_posts = no_posts-' . $count . ' WHERE topic_id=' . $tid;
        $this->db->query($qry);

        if ($uid) {

            $qry = 'UPDATE codo_users SET no_posts = no_posts-' . $count . ' WHERE id=' . $uid;
            $this->db->query($qry);
        }

        \CODOF\Util::set_promoted_or_demoted_rid();
    }

    /** private functions --------------------------------------------------------- */
    public function gen_posts_arr($posts, $search = false) {

        $_posts = array();
        $user = \CODOF\User\User::get();
        $uid = $user->id;

        $i = 0;
        foreach ($posts as $post) {

            $message = \CODOF\Format::message($post['message']);

            if ($search) {

                $message = $search->get_matching_str($message);
            }

            $_posts[$i] = array(
                "id" => $post['id'], "avatar" => \CODOF \Util::get_avatar_path($post['avatar'], $post['id']),
                "name" => $post['name'],
                "role" => \CODOF\User\User::getRoleName($post['rid']),
                "post_created" => \CODOF\Time::get_pretty_time($post['post_created']),
                "post_modified" => \CODOF\Time::get_pretty_time($post['post_modified']),
                "post_id" => $post['post_id'],
                "message" => $message,
                "imessage" => htmlentities($post['imessage'], ENT_QUOTES, "UTF-8"),
                "reputation" => $post['reputation'],
                //
                "role" => \CODOF\User\User::getRoleName($post['rid']),
                "no_posts" => \CODOF\Util::abbrev_no($post['no_posts'], 1),
                "signature" => $post['signature']
            );

            $_posts[$i] ['tid'] = $this->tid;
            $_posts[$i]['page'] = $this->from + 1;
            $_posts[$i]['safe_title'] = $this->safe_title;


            if ($this->topic_post_id == $post['post_id']) {

                //is a topic
                $_posts[$i]['is_topic'] = true;

                if ($post['id'] == $uid) {

                    //this topic belongs to current user
                    $_posts[$i]['can_edit_topic'] = $user->can(array('edit my topics', 'edit all topics'), $this->cat_id);
                    $_posts[$i]['can_delete_topic'] = $user->can(array('delete my topics', 'delete all topics'), $this->cat_id);
                } else {
                    $_posts[$i]['can_edit_topic'] = $user->can('edit all topics', $this->cat_id);
                    $_posts[$i]['can_delete_topic'] = $user->can('delete all topics', $this->cat_id);
                }
                $_posts [$i]['can_manage_topic'] = $_posts[$i]['can_edit_topic'] ||
                        $_posts[$i]['can_delete_topic'];
                
                $_posts[$i]['can_move_topic'] = $user->can('move posts', $this->cat_id);
            } else {
                $_posts[$i]['is_topic'] = false;
                if ($post['id'] == $uid) {

                    //this topic belongs to current user
                    $_posts[$i]['can_edit_post'] = $user->can(array('edit my posts', 'edit all posts'), $this->cat_id);
                    $_posts[$i]['can_delete_post'] = $user->can(array('delete my posts', 'delete all posts'), $this->cat_id);
                } else {
                    $_posts[$i]['can_edit_post'] = $user->can('edit all posts', $this->cat_id);
                    $_posts[$i]['can_delete_post'] = $user->can('delete all posts', $this->cat_id);
                }
                $_posts [$i]['can_manage_post'] = $_posts[$i]['can_edit_post'] ||
                        $_posts[$i]['can_delete_post'];
                
                $_posts[$i]['can_move_post'] = $user->can('move posts', $this->cat_id);
                
            } $_posts[$i]['can_see_history'] = $user->can('see history', $this->cat_id);

            if ($this->tuid == $uid) {

                //if my topic
                $_posts[$i]['can_reply'] = true; //i can reply to my own topic
            } else {
                $_posts[$i]['can_reply'] = $user->can('reply to all topics', $this->cat_id, $this->tid);
            }

            $_posts[$i]['is_closed'] = $this->topic_status == Forum::APPROVED_CLOSED ||
            $this->topic_status == Forum::STICKY_CLOSED || $this->topic_status == Forum::STICKY_ONLY_CATEGORY_CLOSED;
            
            if($_posts[$i]['is_closed']) {
                
                $_posts[$i]['can_reply'] = false;
            }
            
            
            if ($search) {

                $_posts[$i]['in_search'] = true;
            }

            $i++;
        }

        return $_posts;
    }

    private function get_status($option) {

        $status = array(
            "DELETE" => 0,
            "ACTIVE" => 1
        );

        return $status[$option];
    }

}
