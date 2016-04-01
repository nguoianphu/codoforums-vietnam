<?php

/*
 * @CODOLICENSE
 *  
 * 
 * issue system
 * 
 *    users can view own content
 *    users cannot view others content
 * 
 *    moderators/admin -> full priveleges
 * 
 * 
 * premium member system
 * 
 *   role A can access forum B
 * 
 * 
 * 
 * 
 */

namespace CODOF\Forum;

class Topic extends Forum {

    //put your code here

    protected $db;
    public $ajax = false;
    public $new_topic_ids = array();
    public $new_replies = array();
    public $tags = array();

    public function __construct($db) {

        $this->db = $db;
    }

    //TODO: make this a little more reusable
    public function get_topics($cat_id, $page = 1) {

        //$t = microtime(true);
        $cat_id = (int) $cat_id;
        $topics = array();
        $num_posts = \CODOF\Util::get_opt("num_posts_cat_topics");
        $from = ($page - 1) * $num_posts;
        //username , title, topic created, no of replies,
        $qry = 'SELECT p.post_id,p.omessage AS message, p.post_created, u.id, u.name AS name, u.avatar, '
                . 't.topic_id, t.uid, t.no_posts, t.no_views, t.title, t.topic_created,last_post_id, '
                . 't.last_post_name AS lname, t.last_post_uid AS luid, t.last_post_time AS lpost_time,'
                . 't.topic_status '
                . 'FROM ' . PREFIX . 'codo_topics AS t '
                . 'LEFT JOIN ' . PREFIX . 'codo_posts AS p ON (t.post_id=p.post_id AND p.post_status=1) '
                . 'LEFT JOIN ' . PREFIX . 'codo_users AS u ON u.id=p.uid '
                . 'WHERE t.cat_id=' . $cat_id
                . ' AND ('
                . ' t.topic_status=' . Forum::APPROVED . ' OR'
                . ' t.topic_status=' . Forum::APPROVED_CLOSED . ' OR'
                . ' t.topic_status=' . Forum::STICKY . ' OR'
                . ' t.topic_status=' . Forum::STICKY_CLOSED . ' OR'
                . ' t.topic_status=' . Forum::STICKY_ONLY_CATEGORY . ' OR'
                . ' t.topic_status=' . Forum::STICKY_ONLY_CATEGORY_CLOSED . '  ) AND '
                . $this->getViewTopicPermissionConditions()
                . 'ORDER BY t.topic_status DESC, t.last_post_time DESC LIMIT ' . $num_posts . ' OFFSET ' . $from;

        $stmt = $this->db->query($qry);
        if ($stmt) {

            return $stmt->fetchAll();
        }

        return $topics;
    }

    public function getTaggedTopics($tag, $from = 0) {

        $_topics = array();
        //$t = microtime(true);
        $num_posts = \CODOF\Util::get_opt("num_posts_all_topics");
        //username , title, topic created, no of replies,
        $qry = 'SELECT p.post_id, p.omessage AS message, p.post_created, u.id, u.name as name, u.avatar, c.cat_img, c.cat_alias,'
                . 't.topic_id, t.uid, t.title, t.no_posts, t.no_views, t.last_post_time, t.last_post_uid, '
                . 't.last_post_name AS last_post_name, t.topic_created, t.cat_id, last_post_id,t.topic_status '
                . 'FROM codo_topics AS t INNER JOIN codo_tags as g ON g.topic_id=t.topic_id '
                . 'LEFT JOIN codo_posts AS p ON (t.post_id=p.post_id AND p.post_status=1)'
                . 'LEFT JOIN codo_users AS u ON u.id=p.uid '
                . 'LEFT JOIN codo_categories AS c ON c.cat_id=t.cat_id '
                . ' WHERE ('
                . ' t.topic_status=' . Forum::APPROVED . ' OR'
                . ' t.topic_status=' . Forum::APPROVED_CLOSED . ' OR'
                . ' t.topic_status=' . Forum::STICKY . ' OR'
                . ' t.topic_status=' . Forum::STICKY_CLOSED . ' OR'
                . ' t.topic_status=' . Forum::STICKY_ONLY_CATEGORY . ' OR'
                . ' t.topic_status=' . Forum::STICKY_ONLY_CATEGORY_CLOSED . '  ) AND '
                . $this->getViewTopicPermissionConditions()
                . ' AND g.tag_name=' . $this->db->quote($tag)
                . ' ORDER BY t.last_post_time DESC LIMIT  ' . $num_posts . ' OFFSET ' . $from;

        $ans = $this->db->query($qry);
        //echo $qry;
        if ($ans) {

            $topics = $ans->fetchAll();
            $_topics = $this->gen_topic_arr_all_topics($topics);
        }
        //echo " <br/>get_all_topics() ";
        //echo microtime(true) - $t;

        return $_topics;
    }

    public function getTaggedTopicsCount($tag) {

        $status = array(Forum::APPROVED, Forum::APPROVED_CLOSED, Forum::STICKY,
            Forum::STICKY_CLOSED, Forum::STICKY_ONLY_CATEGORY, Forum::STICKY_ONLY_CATEGORY_CLOSED);

        $qry = 'SELECT COUNT(t.topic_id) AS num_tagged FROM ' . PREFIX . 'codo_topics AS t'
                . ' INNER JOIN ' . PREFIX . 'codo_tags AS g ON g.topic_id=t.topic_id '
                . ' WHERE t.topic_status IN (' . implode($status) . ') AND g.tag_name=' . $this->db->quote($tag);

        $obj = $this->db->query($qry);

        $res = $obj->fetch();

        return $res['num_tagged'];
    }

    //TODO: make this a little more reusable
    public function get_all_topics($from = 0) {

        $_topics = array();
        //$t = microtime(true);
        $num_posts = \CODOF\Util::get_opt("num_posts_all_topics");

        $qry = 'SELECT  p.post_id, p.omessage AS message, p.post_created, u.id, '
                . 'u.name as name, u.avatar, c.cat_id, c.cat_img, c.cat_alias, '
                . 't.topic_id, t.uid, t.title, t.no_posts, t.no_views, '
                . 't.last_post_time, t.last_post_uid, last_post_id, t.topic_status, '
                . 't.last_post_name AS last_post_name, t.topic_created '
                . 'FROM codo_topics AS t '
                . 'LEFT JOIN codo_posts AS p ON (t.post_id=p.post_id AND p.post_status=1)'
                . 'LEFT JOIN codo_users AS u ON u.id=p.uid '
                . 'LEFT JOIN codo_categories AS c ON c.cat_id=t.cat_id '
                . 'WHERE ( '
                . ' t.topic_status=' . Forum::APPROVED . ' OR'
                . ' t.topic_status=' . Forum::APPROVED_CLOSED . ' OR'
                . ' t.topic_status=' . Forum::STICKY . ' OR'
                . ' t.topic_status=' . Forum::STICKY_CLOSED . ' ) AND '
                . $this->getViewTopicPermissionConditions()
                . 'ORDER BY t.topic_status DESC, t.last_post_time DESC '
                . 'LIMIT  ' . $num_posts . ' OFFSET ' . $from;


        $ans = $this->db->query($qry);
        if ($ans) {

            $_topics = $ans->fetchAll();
        }

        return $_topics;
    }

    /**
     * Conditionns of SQL query that restrict users to view topics
     * based on user roles/groups assigned to them
     */
    public function getViewTopicPermissionConditions() {

        $user = \CODOF\User\User::get();
        $rids = implode(",", $user->rids);

        /**
         * 
         * 0   0   view all topics  0
         * 0   0   view my  topics  1
         * 3   0   view all topics  1
         * 3   0   view my  topics  0
         * 
         * 
         */
        //NOTE: 'view my topics' & 'view all topics' are mutuall exclusive
        //      so they both cannot be set as granted at once.
        //TODO: Is topic level permission really required ?
        $conditions = ' '
                . 'EXISTS (SELECT 1 FROM codo_permissions AS permission  '
                . ' WHERE  permission.rid IN (' . $rids . ') '
                . ' AND '
                . '  ('
                . '    ('
                . '      permission.cid = t.cat_id'
                . '      AND permission.tid=0 '
                . '    )'
                . '    OR '
                . '    permission.tid=t.topic_id'
                . '  ) '
                . ' AND permission.granted=1 '
                . ' AND '
                . '  ('
                . '    permission.permission=\'view all topics\' OR '
                . '    (permission.permission=\'view my topics\' AND t.uid=' . $user->id . ') '
                . '  ) '
                . ' )';

        return $conditions;
    }

    /**
     *
     * Inserts new topic inside codo_topics
     *
     * @param type $catid Category id of the new topic
     * @param type $title title of the new topic
     *
     * returns topic id of the newly inserted topic
     */
    public function ins_topic($catid, $title, $needsModeration, $topic_status = Forum::APPROVED) {

        $time = time();
        $uid = $_SESSION[UID . 'USER']['id'];
        $catid = (int) $catid;

        if ($needsModeration) {

            $topic_status = Forum::MODERATION_BY_FILTER;
        }

        $qry = "INSERT INTO codo_topics (title, cat_id, uid, topic_created, last_post_time,topic_status) "
                . "VALUES(:title, :catid, :uid, :created_time, :post_time, :topic_status)";

        $stmt = $this->db->prepare($qry);
        $this->success = $stmt->execute(array(
            ":title" => $title, //escaped in topic.php ***
            ":catid" => $catid,
            ":uid" => $uid,
            ":created_time" => $time,
            ":post_time" => $time,
            ":topic_status" => $topic_status
        ));

        $insertedTopicId = $this->db->lastInsertId();

        if (!$needsModeration) {
            $this->incTopicCount($catid);
        }
        return $insertedTopicId;
    }

    /**
     *
     * Gets category id, title and creator from given topic id
     * @param type $topic_id
     * @return boolean
     */
    public function get_catid_title_tuid($topic_id) {

        $topic_id = (int) $topic_id;

        $qry = 'SELECT cat_id,title,uid AS tuid FROM ' . PREFIX . 'codo_topics WHERE topic_id=' . $topic_id;
        $res = $this->db->query($qry);

        if ($res) {

            $result = $res->fetch();
            return $result;
        }

        return false; //an error occured
    }

    /**
     *
     * Links the gievn post with the given topic
     * @param type $pid
     * @param type $tid
     */
    public function link_topic_post($pid, $tid) {

        $qry = "UPDATE codo_topics SET post_id = $pid WHERE topic_id = $tid";
        $this->db->query($qry);
    }

    /**
     *
     * Gets information about the topic of the given topic id
     *
     * @param type $tid
     * @return type
     */
    public function get_topic_info($tid) {

        //$tid is converted to integer so its safe

        $qry = "SELECT t.redirect_to,t.topic_id,t.post_id, t.no_posts, t.no_views,t.uid,"
                . "t.title, c.cat_name,t.post_id, c.cat_alias, c.cat_id,"
                . "t.topic_created, t.topic_updated, t.topic_status "
                . "FROM codo_topics AS t "
                . "INNER JOIN codo_categories AS c ON c.cat_id=t.cat_id "
                . "WHERE t.topic_id=$tid AND t.topic_status<>0 LIMIT 1 OFFSET 0";

        $res = $this->db->query($qry);

        if ($res) {

            $info = $res->fetch();
            return $info;
        }
        return false;
    }

    /**
     *
     * Get topic information by id
     */
    public function get_topic_by_id($tid, $req = '*') {

        $tid = (int) $tid;

        $qry = "SELECT $req FROM " . PREFIX . "codo_topics WHERE topic_id=$tid";
        $res = $this->db->query($qry);

        if ($res) {
            return $res->fetch();
        } else {
            return false;
        }
    }

    /**
     *
     * updates all fileds of codo_topics to latest post
     * @param type $options
     */
    public function update_last_post_details($options) {

        $qry = 'UPDATE ' . PREFIX . 'codo_topics SET last_post_id=:pid, '
                . ' last_post_uid=:uid, last_post_name=:name, last_post_time=:time '
                . 'WHERE topic_id=:tid';

        $stmt = $this->db->prepare($qry);
        $stmt->execute($options);
    }

    /**
     *
     * Edits current topic
     */
    public function edit_topic($cid, $tid, $pid, $title, $imessage, $omessage, $topic_status = Forum::APPROVED) {

        $tid = (int) $tid;
        $pid = (int) $pid;
        $title = \CODOF\Format::title($title);

        $old = \DB::table(PREFIX . 'codo_posts')->where('post_id', $pid)
                        ->select('imessage', 'post_created', 'post_modified')->first();

        \DB::table(PREFIX . 'codo_edits')->insert(array(
            'post_id' => $pid,
            'uid' => \CODOF\User\CurrentUser\CurrentUser::id(),
            'text' => \CODOF\Format::imessage($old['imessage']),
            //if first edit, then post_modified is null, so get time from post_created
            'time' => $old['post_modified'] == null ? $old['post_created'] : $old['post_modified']
        ));


        $qry = 'UPDATE ' . PREFIX . 'codo_topics SET cat_id=:cat_id, title=:title, topic_updated=:time, topic_status=:topic_status '
                . 'WHERE topic_id=:tid';

        $t_stmt = $this->db->prepare($qry);
        $t_stmt->execute(
                array(":cat_id" => $cid, ":title" => $title,
                    ":time" => time(), ":tid" => $tid,
                    ":topic_status" => $topic_status
        ));

        $qry = 'UPDATE ' . PREFIX . 'codo_posts SET cat_id=:cat_id,imessage=:imesg, omessage=:omesg,'
                . 'post_modified=:time WHERE post_id=:pid';

        $p_stmt = $this->db->prepare($qry);
        $p_stmt->execute(
                array(
                    ":cat_id" => $cid,
                    ":imesg" => \CODOF\Format::imessage($imessage),
                    ":omesg" => \CODOF\Format::omessage($omessage),
                    ":time" => time(),
                    ":pid" => $pid
        ));
    }

    /**
     *
     * Gets number of topics present in given category(category id)
     *
     * @param type $cid
     * @return type
     */
    public function get_num_topics($cid) {

        //$t = microtime(true);
        $cid = (int) $cid;
        $qry = "SELECT no_topics FROM " . PREFIX . "codo_categories WHERE cat_id=$cid";
        $stmt = $this->db->query($qry);
        $res = $stmt->fetch();

        //echo " <br/>get_num_topics() ";
        //echo microtime(true) - $t;

        return $res['no_topics'];
    }

    /**
     *
     * Increments [$cid] by [$count] (default: 1)
     *
     * @param int $cid category id
     * @param int $count increment count
     */
    public function incTopicCount($cid, $count = 1) {

        $qry = 'UPDATE ' . PREFIX . 'codo_categories SET no_topics = no_topics+' . $count . ' WHERE cat_id=' . $cid;
        $this->db->query($qry);
    }

    /**
     *
     * Decrements [$cid] by [$count] (default: 1)
     *
     * @param int $cid category id
     * @param int $count increment count
     */
    public function decTopicCount($cid, $count = 1) {

        $qry = 'UPDATE ' . PREFIX . 'codo_categories SET no_topics = no_topics-' . $count . ' WHERE cat_id=' . $cid;
        $this->db->query($qry);
    }

    /**
     *
     * Returns total number of topics in all categories
     * @return type int
     */
    public function get_total_num_topics() {

        $qry = "SELECT COUNT(t.topic_id) AS total_num_topics FROM " . PREFIX . "codo_topics AS t"
                . " LEFT JOIN " . PREFIX . "codo_categories AS c ON c.cat_id=t.cat_id"
                . " WHERE t.topic_status <> 0 AND " . $this->getViewTopicPermissionConditions();

        $obj = $this->db->query($qry);
        $res = $obj->fetch();

        return $res['total_num_topics'];
    }

    /**
     * Changes status of the topic with given topic id
     * @param type $id post id
     * @param type $option
     */
    protected function set_status($id, $option) {

        $tid = (int) $id;

        $status = array(
            "DELETE" => Forum::DELETED,
            "ACTIVE" => Forum::APPROVED,
            "STICKY" => Forum::STICKY,
            "LOCKED" => 3
        );

        $qry = 'UPDATE ' . PREFIX . 'codo_topics SET topic_status=' . $status[$option] . ' '
                . 'WHERE topic_id=' . $tid;
        $this->db->query($qry);
    }

    /**
     * Deletes topic and decrements topic count of its category
     * @param int $cid Category id
     * @param int $tid Topic id
     */
    public function delete($cid, $tid) {

        $this->decTopicCount($cid);

        $this->set_status($tid, 'DELETE');
    }

    //Remember: cat_id is removed from codo_tags table
    /* public function getAllowedTags($catid) {

      $catid = (int) $catid;

      $qry = 'SELECT tag_text FROM ' . PREFIX . 'codo_tags_allowed WHERE cat_id=' . $catid;
      $res = $this->db->query($qry);

      if ($res) {

      return $res->fetch();
      }

      return '';
      } */

    /**
     * Get tags of a particular topic
     * @param int $tid
     * @return array
     */
    public function getTags($tid) {

        $qry = 'SELECT tag_name FROM ' . PREFIX . 'codo_tags WHERE topic_id=' . (int) $tid;
        $res = $this->db->query($qry);

        if ($res) {

            return $res->fetchAll(\PDO::FETCH_COLUMN, 0);
        }

        return false;
    }

    /**
     * Get tag list of all topics provided
     * @param array $tids
     * @return boolean
     */
    public function getAllTags($tids) {

        if (empty($tids))
            return false;

        $qry = 'SELECT g.tag_name,g.topic_id FROM ' . PREFIX . 'codo_tags AS g '
                . 'WHERE g.topic_id IN (' . implode(",", $tids) . ')';

        $res = $this->db->query($qry);

        if ($res) {

            $_tags = $res->fetchAll();
            $tags = array();

            //$allTagsLen = 0;
            //define('MAX_TAGNAMES_LEN', 40);
            //$tagNamesLenExceeded = false;

            foreach ($_tags as $tag) {

                if (!isset($tags[$tag['topic_id']])) {

                    $tags[$tag['topic_id']] = array();
                }

                $tags[$tag['topic_id']][] = array('tag' => $tag['tag_name'], 'safe_tag' => urlencode($tag['tag_name']));

                /* if(!isset($allTagsLen[$tag['topic_id']])) {

                  $allTagsLen[$tag['topic_id']] = 0;
                  $tagNamesLenExceeded[[$tag['topic_id']]] = false;
                  }

                  if(!$tagNamesLenExceeded[[$tag['topic_id']]]) {

                  $allTagsLen[$tag['topic_id']] += strlen($tag['tag_name']);

                  if ($allTagsLen[$tag['topic_id']] > MAX_TAGNAMES_LEN) {

                  $tagNamesLenExceeded[$tag['topic_id']] = true;
                  }
                  } */
            }


            return $tags;
        }

        return false;
    }

    public function getTagStatus($dbTags, $tagsToInsert) {

        $tags = array();

        $tags['toInsert'] = array_diff($tagsToInsert, $dbTags);
        $tags['toDelete'] = array_diff($dbTags, $tagsToInsert);

        return $tags;
    }

    /**
     *
     * @param array $tags
     * @param array $allowedTags
     * @return array
     */
    public function filterAllowedTags($tags, $allowedTags) {

        $_tags = array();

        if (!empty($allowedTags)) {

            foreach ($tags as $tag) {

                if (in_array($tag, $allowedTags)) {

                    $_tags[] = $tag;
                }
            }
        } else {

            return $tags;
        }

        return $_tags;
    }

    public function insertTags($tid, $_tags) {

        if (empty($_tags))
            return false;

        //$allowedTagsText = $this->getAllowedTags($catid);

        $allowedTags = array(); //explode(",", $allowedTagsText);
        $tags = $this->filterAllowedTags($_tags, $allowedTags);

        $qry = 'INSERT INTO ' . PREFIX . 'codo_tags (tag_name, topic_id) VALUES(:tag_name, :tid)';
        $stmt = $this->db->prepare($qry);

        foreach ($tags as $tag) {

            $stmt->execute(array("tag_name" => strip_tags($tag), "tid" => $tid));
        }
    }

    public function removeTags($tid, $tags) {

        if (empty($tags))
            return false;

        $qry = 'DELETE FROM ' . PREFIX . 'codo_tags WHERE tag_name=:tag_name AND topic_id=:tid';
        $stmt = $this->db->prepare($qry);

        foreach ($tags as $tag) {

            $stmt->execute(array("tag_name" => $tag, "tid" => $tid));
        }
    }

    /*     * ** private methods ------------------------------------------------------ ** */

    //TODO: make this a little more reusable
    public function gen_topic_arr_all_topics($topics, $search = false) {

        $_topics = array();
        $user = \CODOF\User\User::get();
        $uid = $user->id;

        $i = 0;
        foreach ($topics as $topic) {

            $message = \CODOF\Format::message($topic['message']);
            if ($search) {

                $message = $search->get_matching_str($message);
            }

            if (!$this->ajax) {
                //    $message = \CODOF\Filter::json_safe($message);
            }

            $_topics[$i] = array(
                "cat_alias" => $topic['cat_alias'],
                "cat_img" => $topic['cat_img'],
                "id" => $topic['id'],
                "avatar" => \CODOF\Util::get_avatar_path($topic['avatar'], $topic['id']),
                "name" => $topic['name'],
                "post_created" => \CODOF\Time::get_pretty_time($topic['post_created']),
                "topic_id" => $topic['topic_id'],
                "post_id" => $topic['post_id'],
				// nguoianphu Do not encode URL
                "safe_title" => \CODOF\Filter::URL_safe(html_entity_decode($topic['title'])),
				// nguoianphu Do not encode URL
                "title" => \CODOF\Util::mid_cut(html_entity_decode($topic['title']), 200),
                "no_replies" => \CODOF\Util::abbrev_no(($topic['no_posts'] - 1), 1),
                "no_views" => \CODOF\Util::abbrev_no($topic['no_views'], 1),
                "last_post_uid" => $topic['last_post_uid'],
                "last_post_name" => $topic['last_post_name'],
                "last_post_id" => $topic['last_post_id'],
                "sticky" => Forum::isSticky($topic['topic_status']),
                "closed" => Forum::isClosed($topic['topic_status']),                
                "last_post_time" => \CODOF\Time::get_pretty_time(($topic['last_post_time'] != $topic['topic_created']) ? $topic['last_post_time'] : NULL),
            );

            if ($search) {
                $_topics[$i]["message"] = $message;
            } else {
                $excerpt = \CODOF\Format::excerpt($message, $topic['topic_id'], $_topics[$i]["safe_title"]);
                $_topics[$i]["message"] = $excerpt['message'];
                $_topics[$i]["overflow"] = $excerpt['overflow'];
            }

            if ($search && $search->match_titles == 'Yes') {
                $_topics[$i]['title'] = $search->highlight($_topics[$i]['title']);
            }

            if ($topic['uid'] == $uid) {

                //this topic belongs to current user
                $_topics[$i]['can_edit_topic'] = $user->can(array('edit my topics', 'edit all topics'), $topic['cat_id']);
                $_topics[$i]['can_delete_topic'] = $user->can(array('delete my topics', 'delete all topics'), $topic['cat_id']);
            } else {

                $_topics[$i]['can_edit_topic'] = $user->can('edit all topics', $topic['cat_id']);
                $_topics[$i]['can_delete_topic'] = $user->can('delete all topics', $topic['cat_id']);
            }

            $_topics[$i]['can_manage_topic'] = $_topics[$i]['can_edit_topic'] ||
                    $_topics[$i]['can_delete_topic'];


            if ($search) {

                $_topics[$i]['in_search'] = true;
            }

            if (in_array($topic['topic_id'], $this->new_topic_ids)) {

                $_topics[$i]["new_topic"] = true;
            }

            if (array_key_exists($topic['topic_id'], $this->new_replies)) {

                $_topics[$i]["new_replies"] = $this->new_replies[$topic['topic_id']][0];
                $_topics[$i]["last_reply_id"] = $this->new_replies[$topic['topic_id']][1];
            }


            if (isset($this->tags[$topic['topic_id']])) {

                $_topics[$i]["tags"] = $this->tags[$topic['topic_id']];
            }

            $i++;
        }

        return $_topics;
    }

    //TODO: make this a little more reusable
    public function gen_topic_arr($topics, $cid) {

        $_topics = array();
        $user = \CODOF\User\User::get();
        $uid = $user->id;

        $i = 0;

        foreach ($topics as $topic) {

            $message = \CODOF\Format::message($topic['message']);

            $_topics[$i] = array(
                "id" => $topic['id'],
                "avatar" => \CODOF\Util::get_avatar_path($topic['avatar'], $topic['id']),
                "name" => $topic['name'],
                "post_created" => \CODOF\Time::get_pretty_time($topic['post_created']),
                "topic_created" => $topic['topic_created'],
                "topic_id" => $topic['topic_id'],
                "post_id" => $topic['post_id'],
                "safe_title" => \CODOF\Filter::URL_safe(html_entity_decode($topic['title'])),
				// nguoianphu Do not encode URL
                "title" => html_entity_decode($topic['title']),
                "no_replies" => \CODOF\Util::abbrev_no(($topic['no_posts'] - 1), 1),
                "no_views" => \CODOF\Util::abbrev_no($topic['no_views'], 1),
                "last_post_name" => $topic['lname'],
                "last_post_uid" => $topic['luid'],
                "sticky" => Forum::isSticky($topic['topic_status']),
                "closed" => Forum::isClosed($topic['topic_status']),                                
                "last_post_id" => $topic['last_post_id'],
                "last_post_time" => \CODOF\Time::get_pretty_time(($topic['lpost_time'] != $topic['topic_created']) ? $topic['lpost_time'] : NULL),
            );

            $excerpt = \CODOF\Format::excerpt($message, $topic['topic_id'], $_topics[$i]["safe_title"]);
            $_topics[$i]["message"] = $excerpt['message'];
            $_topics[$i]["overflow"] = $excerpt['overflow'];

            if ($topic['uid'] == $uid) {

                //this topic belongs to current user
                $_topics[$i]['can_edit_topic'] = $user->can(array('edit my topics', 'edit all topics'), $cid);
                $_topics[$i]['can_delete_topic'] = $user->can(array('delete my topics', 'delete all topics'), $cid);
            } else {

                $_topics[$i]['can_edit_topic'] = $user->can('edit all topics', $cid);
                $_topics[$i]['can_delete_topic'] = $user->can('delete all topics', $cid);
            }

            $_topics[$i]['can_manage_topic'] = $_topics[$i]['can_edit_topic'] ||
                    $_topics[$i]['can_delete_topic'];

            if (isset($search)) {

                $_topics[$i]['in_search'] = true;
            }

            if (in_array($topic['topic_id'], $this->new_topic_ids)) {

                $_topics[$i]["new_topic"] = true;
            }

            if (in_array($topic['topic_id'], $this->new_replies)) {

                $_topics[$i]["new_replies"] = $this->new_replies[$topic['topic_id']][0];
                $_topics[$i]["last_reply_id"] = $this->new_replies[$topic['topic_id']][1];
            }


            if (isset($this->tags[$topic['topic_id']])) {

                $_topics[$i]["tags"] = $this->tags[$topic['topic_id']];
            }


            $i++;
        }

        return $_topics;
    }

    /**
     * Sets the topic auto close date in unix time
     * @param int $tid
     * @param string $date
     */
    public function setTopicAutoCloseDate($tid, $date) {

        $time = 0;

        if ($date != null)
            $time = strtotime($date);

        $qry = "UPDATE codo_topics SET topic_close = $time WHERE topic_id = $tid";
        $this->db->query($qry);
    }

    /**
     * Checks if particular topic can be viewed by current user or not
     * @param int $tuid topic creator's userid
     * @param int $cid
     * @param int $tid
     */
    public function canViewTopic($tuid, $cid, $tid) {

        $user = \CODOF\User\User::get();

        return
                $tuid == $user->id &&
                $user->canAny(array('view my topics', 'view all topics'), $cid, $tid)
                //my topic, check permission to view my or all topics
                ||
                $tuid != $user->id &&
                $user->can('view all topics', $cid, $tid);
        //not my topic, check permission to view all topics
    }

    /**
     * Checks if particular topic can be viewed by current user or not
     * @param int $tuid topic creator's userid
     * @param int $cid
     * @param int $tid
     */
    public function canEditTopic($tuid, $cid, $tid) {

        $user = \CODOF\User\User::get();

        return
                $tuid == $user->id &&
                $user->canAny(array('edit my topics', 'edit all topics'), $cid, $tid)
                //can i edit my own topic ?
                ||
                $tuid != $user->id &&
                $user->can('edit all topics', $cid, $tid);
        //can i edit others' topic ?
    }

    /**
     * Checks if particular topic can be viewed by current user or not
     * @param int $tuid topic creator's userid
     * @param int $cid
     * @param int $tid
     */
    public function canReplyTopic($tuid, $cid, $tid) {

        $user = \CODOF\User\User::get();

        return
                $tuid == $user->id //&&
                //$user->canAny(array('reply my topics', 'reply all topics'), $cid, $tid)
                //can i reply to my own topic ?
                ||
                $tuid != $user->id &&
                $user->can('reply to all topics', $cid, $tid);
        //can i reply to others' topic ?
    }

    /**
     * Checks if particular topic can be viewed by current user or not
     * @param int $tuid topic creator's userid
     * @param int $cid
     * @param int $tid
     */
    public function canDeleteTopic($tuid, $cid, $tid) {

        $user = \CODOF\User\User::get();

        return
                $tuid == $user->id &&
                $user->canAny(array('delete my topics', 'delete all topics'), $cid, $tid)
                //can i reply to my own topic ?
                ||
                $tuid != $user->id &&
                $user->can('delete all topics', $cid, $tid);
        //can i reply to others' topic ?
    }

}
