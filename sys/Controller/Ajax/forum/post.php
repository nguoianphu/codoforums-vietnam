<?php

/*
 * @CODOLICENSE
 */

namespace Controller\Ajax\forum;

class post {

    public function __construct() {

        $this->db = \DB::getPDO();
    }

    public function edit() {

        //hacking attempt
        if ($_POST['end_of_line'] != "") {
            exit;
        }

        $pid = (int) $_POST['pid'];
        $qry = 'SELECT uid FROM ' . PREFIX . 'codo_posts WHERE post_id=' . $pid;
        $res = $this->db->query($qry);
        $result = $res->fetch();

        if ($result) {

            $puid = $result['uid'];

            if ($puid == \CODOF\User\CurrentUser\CurrentUser::id()) {

                $has_permission = \CODOF\Access\Access::hasPermission(array('edit my posts', 'edit all posts'));
            } else {

                $has_permission = \CODOF\Access\Access::hasPermission('edit all posts');
            }

            if ($has_permission &&
                    isset($_POST['input_txt']) && isset($_POST['output_txt']) && isset($_POST['tid'])) {


                $post = new \CODOF\Forum\Post($this->db);

                $in = $_POST['input_txt'];
                $out = $_POST['output_txt'];

                $pid = $post->update_post($pid, $in, $out);

                echo 'success';
            } else {

                echo 'you are not authorized to edit this post';
            }
        } else {

            echo 'no post found';
        }
    }

    public function delete($id) {

        //SQL injection safe
        $pid = (int) $id;
        $qry = 'SELECT uid FROM ' . PREFIX . 'codo_posts WHERE post_id=' . $pid;
        $res = $this->db->query($qry);
        $result = $res->fetch();

        if ($result) {

            $puid = $result['uid'];

            if ($puid == \CODOF\User\CurrentUser\CurrentUser::id()) {

                $has_permission = \CODOF\Access\Access::hasPermission(array('edit my posts', 'edit all posts'));
            } else {

                $has_permission = \CODOF\Access\Access::hasPermission('edit all posts');
            }

            if ($has_permission) {

                $post = new \CODOF\Forum\Post($this->db);
                //Delete post ie set status as 0
                $post->delete($pid);

                echo 'success';
            } else {

                echo "Unauthorized request to delete post " . $id;
                exit;
            }
        } else {

            echo 'no post found';
        }
    }

    public function undelete($id) {

        //SQL injection safe
        $pid = (int) $id;
        $qry = 'SELECT uid FROM ' . PREFIX . 'codo_posts WHERE post_id=' . $pid;
        $res = $this->db->query($qry);
        $result = $res->fetch();

        if ($result) {

            $puid = $result['uid'];

            if ($puid == \CODOF\User\CurrentUser\CurrentUser::id()) {

                $has_permission = \CODOF\Access\Access::hasPermission(array('edit my posts', 'edit all posts'));
            } else {

                $has_permission = \CODOF\Access\Access::hasPermission('edit all posts');
            }

            if ($has_permission) {

                $post = new \CODOF\Forum\Post($this->db);
                //Delete post ie set status as 0
                $post->undelete($pid);

                echo 'success';
            } else {

                echo "Unauthorized request to delete post " . $id;
                exit;
            }
        } else {

            echo 'no post found';
        }
    }

}
