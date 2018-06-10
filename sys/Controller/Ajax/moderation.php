<?php

namespace Controller\Ajax;

class moderation {

    public function approveTopics() {


        $tids = $_POST['tids'];

        foreach ($tids as $tid) {

            $this->approveTopic($tid);
        }

        echo 'success';
    }

    public function approveTopic($_tid) {

        $db = \DB::getPDO();
        $tid = (int) $_tid;

        $qry = 'SELECT t.topic_status, t.cat_id, p.imessage FROM ' . PREFIX . 'codo_topics AS t'
                . ' INNER JOIN ' . PREFIX . 'codo_posts AS p ON p.topic_id=t.topic_id '
                . ' WHERE t.topic_id=' . $tid;

        $res = $db->query($qry);

        if ($res) {

            $row = $res->fetch();

            $status = $row['topic_status'];
            $cid = $row['cat_id'];
            $text = $row['imessage'];

            $user = \CODOF\User\User::get();

            if ($user->can('moderate topics', $cid)) {

                $qry = 'UPDATE ' . PREFIX . 'codo_topics SET topic_status=' . \CODOF\Forum\Forum::APPROVED
                        . ' WHERE topic_id=' . $tid;

                $db->query($qry);

                $topic = new \CODOF\Forum\Topic($db);
                $topic->incTopicCount($cid);
                
                //If a post considered as spam by filter is being approved
                //it means the filter needs to relearn that it is not spam
                if ($status == \CODOF\Forum\Forum::MODERATION_BY_FILTER) {

                    $filter = new \CODOF\SpamFilter();
                    $filter->ham($text);
                }
            }
        }
    }

    public function deleteTopics() {


        $tids = $_POST['tids'];

        foreach ($tids as $tid) {

            $this->deleteTopic($tid);
        }

        echo 'success';
    }

    public function deleteTopic($_tid) {

        $db = \DB::getPDO();
        $tid = (int) $_tid;

        $qry = 'SELECT t.topic_status, t.cat_id, p.imessage FROM ' . PREFIX . 'codo_topics AS t'
                . ' INNER JOIN ' . PREFIX . 'codo_posts AS p ON p.topic_id=t.topic_id '
                . ' WHERE t.topic_id=' . $tid;

        $res = $db->query($qry);

        if ($res) {

            $row = $res->fetch();

            $status = $row['topic_status'];
            $cid = $row['cat_id'];
            $text = $row['imessage'];

            $user = \CODOF\User\User::get();

            if ($user->can('moderate topics', $cid)) {

                $qry = 'UPDATE ' . PREFIX . 'codo_topics SET topic_status=' . \CODOF\Forum\Forum::DELETED
                        . ' WHERE topic_id=' . $tid;

                $db->query($qry);

                if ($status == \CODOF\Forum\Forum::PRE_MODERATION) {

                    $filter = new \CODOF\SpamFilter();
                    $filter->spam($text);
                }
            }
        }
    }

    public function approveReplies() {


        $tids = $_POST['tids'];
        foreach ($tids as $tid) {

            $this->approveReply($tid);
        }

        echo 'success';
    }

    public function approveReply($_pid) {

        $db = \DB::getPDO();
        $pid = (int) $_pid;

        $qry = 'SELECT p.post_status, p.cat_id, p.topic_id, p.uid,p.post_created, p.imessage FROM ' . PREFIX . 'codo_posts AS p'
                . ' WHERE p.post_id=' . $pid;

        $res = $db->query($qry);
        if ($res) {

            $row = $res->fetch();
            $status = $row['post_status'];
            $cid = $row['cat_id'];
            $text = $row['imessage'];

            $user = \CODOF\User\User::get();

            if ($user->can('moderate posts', $cid)) {

                $qry = 'UPDATE ' . PREFIX . 'codo_posts SET post_status=' . \CODOF\Forum\Forum::APPROVED
                        . ' WHERE post_id=' . $pid;

                $db->query($qry);

                $post = new \CODOF\Forum\Post($db);
                $post->incPostCount($cid, $row['topic_id'], $row['uid']);
                
                $options = array(
                    ":pid" => $pid,
                    ":uid" => $user->id,
                    ":name" => $user->name,
                    ":time" => $row['post_created'],
                    ":tid" => $row['topic_id']
                );

                $topic = new \CODOF\Forum\Topic($db);
                $topic->update_last_post_details($options);
                
                //If a post considered as spam by filter is being approved
                //it means the filter needs to relearn that it is not spam
                if ($status == \CODOF\Forum\Forum::MODERATION_BY_FILTER) {

                    $filter = new \CODOF\SpamFilter();
                    $filter->ham($text);
                }
            }
        }
    }

    public function deleteReplies() {


        $tids = $_POST['tids'];

        foreach ($tids as $tid) {

            $this->deleteReply($tid);
        }

        echo 'success';
    }

    public function deleteReply($_tid) {

        $db = \DB::getPDO();
        $pid = (int) $_tid;

        $qry = 'SELECT p.post_status, p.cat_id, p.topic_id,p.uid, p.imessage FROM ' . PREFIX . 'codo_posts AS p'
                . ' WHERE p.post_id=' . $pid;

        $res = $db->query($qry);

        if ($res) {

            $row = $res->fetch();

            $status = $row['post_status'];
            $cid = $row['cat_id'];
            $text = $row['imessage'];

            $user = \CODOF\User\User::get();

            if ($user->can('moderate posts', $cid)) {

                $qry = 'UPDATE ' . PREFIX . 'codo_posts SET post_status=' . \CODOF\Forum\Forum::DELETED
                        . ' WHERE post_id=' . $pid;

                $db->query($qry);

                if ($status == \CODOF\Forum\Forum::PRE_MODERATION) {

                    $filter = new \CODOF\SpamFilter();
                    $filter->spam($text);
                }
            }
        }
    }

}
