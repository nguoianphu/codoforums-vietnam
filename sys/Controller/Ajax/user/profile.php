<?php

/*
 * @CODOLICENSE
 */

namespace Controller\Ajax\user;

class profile {

    protected $db;

    protected $maxPostsPerTopic = 10;
    
    public function __construct() {

        $this->db = \DB::getPDO();
    }

    public function get_recent_posts($uid) {

        $posts = array();

        $id = (int) $uid;

        $access_conditions = '';
        if($id != \CODOF\User\CurrentUser\CurrentUser::id()) {
            
            $topic = new \CODOF\Forum\Topic(false);
            $access_conditions = "AND " . $topic->getViewTopicPermissionConditions();
        }
        
        $qry = 'SELECT c.cat_alias,c.cat_img,p.omessage AS message, t.title, t.topic_id,'
                . ' u.id, u.name, r.rid,  u.avatar, t.topic_created,t.no_posts,t.no_views, p.post_created,p.post_id '
                . ' FROM ' . PREFIX . 'codo_posts AS p '
                . ' LEFT JOIN ' . PREFIX . 'codo_categories AS c ON p.cat_id=c.cat_id '
                . ' LEFT JOIN ' . PREFIX . 'codo_topics AS t ON t.topic_id=p.topic_id '
                . ' LEFT JOIN ' . PREFIX . 'codo_users AS u ON t.uid=u.id '
                . ' LEFT JOIN ' . PREFIX . 'codo_user_roles AS r ON r.uid=u.id AND r.is_primary=1 '                
                . '  WHERE p.uid = ' . $id
                . '   AND p.post_status<>0 ' . $access_conditions
                . '   ORDER BY p.post_created DESC '
                . ' LIMIT 20 OFFSET 0';

        $obj = $this->db->query($qry);
        if ($obj) {

            $posts = $this->gen_posts_arr($obj->fetchAll());
        }

        $category = new \CODOF\Forum\Category();
        return array(
            "topics" => $posts,
            "RURI" => RURI,
            "DURI" => DURI,
            "CAT_IMGS" => CAT_IMGS,
            //"DEF_AVATAR" => DEF_AVATAR,
            "CURR_THEME" => CURR_THEME,
            "reply_txt" => _t("replies"),
            "views_txt" => _t("views"),
            "posted" => _t("posted"),
            "created" => _t("created"),
            "no_topics" => _t("You have no recent posts"),
            "new_topic" => _t("Create new topic"),
            "can_create" => $category->canCreateTopicInAtleastOne()
        );
    }

    private function gen_posts_arr($posts) {

        $_posts = array();
        $i = 0;
        $topics_set = array();
        
        foreach ($posts as $post) {

            if (isset($topics_set[$post['topic_id']])) {

                $_posts[$topics_set[$post['topic_id']]]['contents'][] = array("post_id" => $post['post_id'],
                    "message" => \CODOF\Format::message($post['message']),
                    "post_created" => \CODOF\Time::get_pretty_time($post['post_created'])
                );
                
                //$topics_set[$post['topic_id']]++;
                continue;
            }

            $_posts[$i] = array(
                "id" => $post['id'],
                "avatar" => \CODOF\Util::get_avatar_path($post['avatar'], $post['id']),
                "name" => $post['name'],
                "role" => \CODOF\User\User::getRoleName($post['rid']),
                "no_replies" => \CODOF\Util::abbrev_no(($post['no_posts'] - 1), 1),
                "no_views" => \CODOF\Util::abbrev_no($post['no_views'], 1),
                "topic_created" => \CODOF\Time::get_pretty_time($post['topic_created']),
                "cat_alias" => $post['cat_alias'],
                "cat_img" => $post['cat_img'],
                "contents" => array(array("post_id" => $post['post_id'],"message" => \CODOF\Format::message($post['message']), "post_created" => \CODOF\Time::get_pretty_time($post['post_created']))),
                "topic_id" => $post['topic_id'],
                "safe_title" => \CODOF\Filter::URL_safe(html_entity_decode($post['title'])),
                "title" => \CODOF\Util::mid_cut($post['title'], 200)
            );
            
            
            
            $topics_set[$post['topic_id']] = $i;
           
            $i++;
                        
        }
       
        return $_posts;
    }

}
