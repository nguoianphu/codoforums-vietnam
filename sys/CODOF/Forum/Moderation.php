<?php

namespace CODOF\Forum;

class Moderation extends Forum {

    /**
     *
     * @var \PDO 
     */
    private $db;

    public function __construct(\PDO $pdo) {

        $this->db = $pdo;
    }
    
    public function getTopics($from = 0) {

        $num_posts = \CODOF\Util::get_opt("num_posts_cat_topics");

        $qry = 'SELECT  p.post_id, p.omessage AS message, p.post_created, u.id, '
                . 'u.name as name, u.avatar, c.cat_id, c.cat_img, c.cat_alias, '
                . 't.topic_id, t.uid, t.title, t.no_posts, t.no_views, '
                . 't.last_post_time, t.last_post_uid, t.topic_status,'
                . 't.last_post_name AS last_post_name, t.topic_created '
                . 'FROM ' . PREFIX . 'codo_topics AS t '
                . 'LEFT JOIN ' . PREFIX . 'codo_posts AS p ON (t.post_id=p.post_id) '
                . 'LEFT JOIN ' . PREFIX . 'codo_users AS u ON u.id=p.uid '
                . 'LEFT JOIN codo_categories AS c ON c.cat_id=t.cat_id '                
                . 'WHERE ' . $this->topicInModeration('t')
                . ' AND ' . $this->getPermissionConditions('moderate topics')
                . ' ORDER BY t.last_post_time DESC';// LIMIT ' . $num_posts . ' OFFSET ' . $from;

        $res = $this->db->query($qry);

        return $this->format($res->fetchAll());
    }
    
    public function getReplies($from = 0) {

        $num_posts = \CODOF\Util::get_opt("num_posts_cat_topics");

        $qry = 'SELECT  p.post_id, p.omessage AS message, p.post_created, u.id, '
                . 'u.name as name, u.avatar, c.cat_id, c.cat_img, c.cat_alias, '
                . 't.topic_id, t.uid, t.title, t.no_posts, t.no_views, '
                . 't.last_post_time, t.last_post_uid, t.topic_status,'
                . 't.last_post_name AS last_post_name, t.topic_created '
                . 'FROM ' . PREFIX . 'codo_posts AS p '
                . 'LEFT JOIN ' . PREFIX . 'codo_topics AS t ON (t.topic_id=p.topic_id) '
                . 'LEFT JOIN ' . PREFIX . 'codo_users AS u ON u.id=p.uid '
                . 'LEFT JOIN codo_categories AS c ON c.cat_id=t.cat_id '                
                . 'WHERE ' . $this->postInModeration('p')
                . ' AND ' . $this->getPermissionConditions('moderate posts')
                . ' ORDER BY p.post_created DESC';// LIMIT ' . $num_posts . ' OFFSET ' . $from;

        $res = $this->db->query($qry);

        return $this->format($res->fetchAll());
    }
    
    
    public function getNumTopics() {
        
        $qry = 'SELECT COUNT(topic_id) FROM ' . PREFIX . 'codo_topics AS t'
                . ' WHERE ' . $this->topicInModeration() . ' AND ' . $this->getPermissionConditions('moderate topics') ;
        $res = $this->db->query($qry)->fetch();
        return $res[0];
    }

    public function getNumReplies() {
        
        $qry = 'SELECT COUNT(post_id) FROM ' . PREFIX . 'codo_posts p'
                . ' WHERE ' . $this->postInModeration() . ' AND ' . $this->getPermissionConditions('moderate posts', 'p');

        $res = $this->db->query($qry)->fetch();
        return $res[0];
    }
    
    private function format($topics) {

        $_topics = array();
        $i = 0;
        
        foreach ($topics as $topic) {

            $message = \CODOF\Format::message($topic['message']);

            $_topics[$i] = array(
                "cat_alias" => $topic['cat_alias'],
                "cat_img" => $topic['cat_img'],
                "id" => $topic['id'],
                "avatar" => \CODOF\Util::get_avatar_path($topic['avatar'], $topic['id']),
                "name" => $topic['name'],
                "post_created" => \CODOF\Time::get_pretty_time($topic['post_created']),
                "topic_id" => $topic['topic_id'],
                "post_id" => $topic['post_id'],
                "safe_title" => \CODOF\Filter::URL_safe($topic['title']),
                "title" => \CODOF\Util::mid_cut($topic['title'], 200),
                "no_replies" => \CODOF\Util::abbrev_no(($topic['no_posts'] - 1), 1),
                "no_views" => \CODOF\Util::abbrev_no($topic['no_views'], 1),
                "last_post_uid" => $topic['last_post_uid'],
                "last_post_name" => $topic['last_post_name'],
                "last_post_time" => \CODOF\Time::get_pretty_time(($topic['last_post_time'] != $topic['topic_created']) ? $topic['last_post_time'] : NULL),
            );

            $excerpt = \CODOF\Format::excerpt($message, $topic['topic_id'], $_topics[$i]["safe_title"]);
            $_topics[$i]["message"] = $excerpt['message'];
            $_topics[$i]["overflow"] = $excerpt['overflow'];
            $_topics[$i]["status"] = $topic['topic_status'];
            $_topics[$i]["what"] = 'is_topic';

            
            $i++;
        }

        return $_topics;
    }

}
