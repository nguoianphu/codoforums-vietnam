<?php

/*
 * @CODOLICENSE
 */


/*

  This class may be used to solve any consistency issues in the codoforum database
  that may arise when upgrading, importing etc

 */

namespace CODOF\Importer;

class Concise {

    private $driver;

    public function __construct(\PDO $db, $driver) {

        $this->db = $db;
        $this->driver = $driver;
    }

    private function update_counts() {

        $this->update_count_categories();
        $this->update_count_topics();
        $this->update_count_users();
    }

    public function update_count_users() {

        if ($this->driver == 'Codoforum') {
            return;
        }
        $qry = 'SELECT uid,COUNT(post_id) AS post_count FROM ' . PREFIX . 'codo_posts WHERE post_status<>0 GROUP BY uid';
        $obj = $this->db->query($qry);
        $userCounts = $obj->fetchAll();


        $upd = "UPDATE " . PREFIX . "codo_users SET no_posts=:no_posts WHERE id=:id";
        $update = $this->db->prepare($upd);

        foreach ($userCounts as $userCount) {

            $uid = $userCount['uid'];
            $num_posts = $userCount['post_count'];

            $update->execute(array("no_posts" => $num_posts, "id" => $uid));
        }
    }

    public function update_count_topics() {

        if ($this->driver == 'Codoforum') {
            return;
        }
        $qry = 'SELECT topic_id, COUNT(post_id) AS post_count FROM ' . PREFIX . 'codo_posts WHERE post_status<>0 GROUP BY topic_id';
        $res = $this->db->query($qry);
        $topicCounts = $res->fetchAll();

        $upd = "UPDATE " . PREFIX . "codo_topics SET no_posts=:no_posts WHERE topic_id=:tid";
        $update = $this->db->prepare($upd);


        foreach ($topicCounts as $topicCount) {

            $update->execute(array("no_posts" => $topicCount['post_count'], "tid" => $topicCount['topic_id']));
        }
    }

    public function update_count_categories() {

        if ($this->driver == 'Codoforum') {
            return;
        }
        $qry = 'SELECT cat_id, COUNT(topic_id) AS topic_count FROM ' . PREFIX . 'codo_topics WHERE topic_status<>0 GROUP BY cat_id';
        $res = $this->db->query($qry);
        $catCounts = $res->fetchAll();

        $qry = 'SELECT cat_id, COUNT(post_id) AS post_count FROM ' . PREFIX . 'codo_posts WHERE post_status<>0 GROUP BY cat_id';
        $res = $this->db->query($qry);
        $postCounts = $res->fetchAll();


        $posts = array();
        foreach ($postCounts as $postCount) {

            $posts[$postCount['cat_id']] = $postCount['post_count'];
        }


        $upd = "UPDATE " . PREFIX . "codo_categories SET no_topics=:no_topics, no_posts=:no_posts WHERE cat_id=:cid";
        $update = $this->db->prepare($upd);

        foreach ($catCounts as $catCount) {

            $update->execute(
                    array(
                        "no_topics" => $catCount['topic_count'],
                        "no_posts" => $posts[$catCount['cat_id']],
                        "cid" => $catCount['cat_id']
                    )
            );
        }
    }

}
