<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Importer;

use \CODOF\Format;

/**
 *
 * Look at the Import Interface for how to use this class
 *
 */
class Import {

    /**
     *
     * @var \PDO 
     */
    private $db;
    private $preps;
    public $def_cat_img = "general.png";
    public $query;

    /**
     * Instance of Imprt driver
     * @var type 
     */
    private $driver;

    public function __construct($db, $driver) {

        $this->db = $db;

        $this->backup_admin_account();
        $this->driver = $driver;

        $this->preps = new \stdClass();
    }

    public function empty_tables($what) {

        $this->db->exec('SET FOREIGN_KEY_CHECKS=0;');
        $qry = array();

        switch ($what) {

            case 'categories' :
                $qry[] = 'TRUNCATE TABLE codo_categories';
                $qry[] = 'TRUNCATE TABLE codo_permissions';
                break;

            case 'topics' :
                $qry[] = 'TRUNCATE TABLE codo_topics';
                break;

            case 'posts' :
                $qry[] = 'TRUNCATE TABLE codo_posts';
                break;

            case 'users' :
                $qry[] = 'TRUNCATE TABLE codo_users';
                $qry[] = 'TRUNCATE TABLE codo_user_roles';
                break;

            default :
                $qry[] = 'TRUNCATE TABLE ' . $what;
        }

        foreach ($qry as $q) {

            $this->db->query($q);
        }
    }

    public function ins_cat($cat_info) {

        $cats = array();
        $i = 0;

        //blank -> 100 users 100 posts 
        //import -

        $defs = array(
            "cat_pid" => 0,
            "cat_description" => "",
            "cat_img" => $this->def_cat_img,
            "cat_order" => 0
        );

        $manager = new \CODOF\Permission\Manager();

        foreach ($cat_info as $cat) {

            $cats[$i] = $this->set_value($cat, $defs);
            $cats[$i]["cat_alias"] = \CODOF\Forum\Category::get_alias($cat['cat_name']);
            $cats[$i]["cat_name"] = $cat['cat_name'];
            $cats[$i]["cat_id"] = $cat['cat_id'];

            $cats[$i]["no_topics"] = isset($cat["no_topics"]) ? $cat["no_topics"] : 0;
            $cats[$i]["no_posts"] = isset($cat["no_posts"]) ? $cat["no_posts"] : 0;

            $manager->copyCategoryPermissionsFromRole($cat['cat_id']);
            $i++;
        }

        $attrs = array("cat_id", "cat_pid", "cat_name", "cat_alias", "cat_description", "cat_img", "no_topics", "no_posts", "cat_order");

        $qry = $this->prepare_ins_qry($cats, $attrs, "codo_categories");

        //$this->query .= $qry;
    }

    public function ins_topics($topic_info, $pid, $use_passed_pid) {

        $cats = array();
        $i = 0;

        $defs = array(
            "last_post_id" => 0,
            "topic_updated" => 0,
        );


        foreach ($topic_info as $cat) {

            $cats[$i] = $this->set_value($cat, $defs);
            $cats[$i] += $cat;
            //$cats[$i]['topic_id'] = $tid;
            if ($use_passed_pid) {

                $cats[$i]['post_id'] = ++$pid;
            }
            $cats[$i]['title'] = Format::title($cat['title']);

            //does all last post details exist ?            
            if (\CODOF\Util::is_set($cat, array('last_post_id', 'last_post_uid', 'last_post_name', 'last_post_time'))) {

                //correct last post time 
                if ($cat['last_post_time'] == null || $cat['last_post_time'] == 0) {

                    $cats[$i]['last_post_time'] = $cat['topic_created'];
                }
            } else {

                $cats[$i]['last_post_id'] = 0;
                $cats[$i]['last_post_uid'] = NULL;
                $cats[$i]['last_post_name'] = NULL;
                $cats[$i]['last_post_time'] = $cat['topic_created'];
            }

            $cats[$i]["no_posts"] = isset($cat["no_posts"]) ? $cat["no_posts"] : 0;
            $cats[$i]["no_views"] = isset($cat["no_views"]) ? $cat["no_views"] : 0;

            $i++;
        }

        // var_dump($cats);
        $attrs = array("topic_id", "title", "cat_id", "post_id", "uid", "last_post_id",
            "last_post_uid", "last_post_name", "topic_created", "topic_updated", "last_post_time", "no_posts", "no_views");

        $qry = $this->prepare_ins_qry($cats, $attrs, "codo_topics");

        $this->query .= $qry;

        return $pid;
    }

    public function create_codopm_tables() {

        \Schema::dropIfExists('codopm_messages');
        \Schema::create('codopm_messages', function($table) {
            $table->increments('id');
            $table->string('thread_hash', 30);
            $table->integer('msg_from');
            $table->string('msg_from_name', 255);
            $table->integer('msg_to');
            $table->string('msg_to_name', 255);
            $table->text('message');
            $table->text('attachments');
            $table->integer('owner');
            $table->dateTime('sent')->default('0000-00-00 00:00:00');
            $table->integer('recd')->unsigned()->default('0');
            $table->double('time', 15, 4);
        });

        \Schema::dropIfExists('codopm_config');
        \Schema::create('codopm_config', function($table) {
            $table->increments('id');
            $table->string('option_name', 50);
            $table->text('option_value');
        });

        $columns = array('id', 'option_name', 'option_value');
        $values = array(
            array(1, 'max_filename_len', '50'),
            array(2, 'msgs_per_page', '10'),
            array(3, 'valid_exts', 'jpeg,jpg,png,gif,doc,docx,zip'),
            array(4, 'per_filesize_limit', '2000'),
            array(5, 'total_filesize_limit', '10000'),
            array(6, 'conv_per_page', '10'),
            array(7, 'conv_load_offset', '10'));

        function build_arr($columns, $values) {

            $multi_value = array();

            foreach ($values as $value) {

                $multi_value[] = combine($columns, $value);
            }

            return $multi_value;
        }

        function combine($col, $row) {

            $arr = array();

            $i = 0;
            foreach ($row as $val) {

                $arr[$col[$i]] = $val;

                $i++;
            }

            return $arr;
        }

        $configs = build_arr($columns, $values);
        \DB::table(PREFIX . 'codopm_config')->insert(
                $configs
        );
    }

    public function ins_table($table, $data) {

        if ($table == 'codopm_messages') {
            $this->create_codopm_tables();
        }

        \DB::table(PREFIX . $table)
                ->insert($data);
    }

    public function ins_posts($post_info, $offset = 0) {

        $posts = array();
        $i = 0;

        //$defs = array();
        $html = new \Ext\Html();

        //imessage -> pure text MD or BBCode can be used
        //omessage -> HTML

        foreach ($post_info as $post) {

            //$posts[$i] = $this->set_value($post, $defs);
            $posts[$i] = $post;
            $posts[$i]["post_id"] += $offset;

            if (isset($post['imessage']) && isset($post['omessage'])) {

                //everything is perfect
            } else {

                $posts[$i]["imessage"] = Format::br2nl(Format::imessage($post['message']));
                $posts[$i]["omessage"] = $html->filter(Format::parseBBCode(($post['message'])), false, true);
            }

            if (method_exists($this->driver, 'modify_posts')) {

                $posts[$i] = $this->driver->modify_posts($posts[$i]);
            }

            $i++;
        }

        // var_dump($cats);
        $attrs = array("post_id", "topic_id", "cat_id", "uid", "imessage", "omessage",
            "post_created");

        $qry = $this->prepare_ins_qry($posts, $attrs, "codo_posts");

        //$this->query .= $qry;
    }

    public function ins_users($user_info) {

        $users = array();
        $roles = array();
        $i = 0;

        $defs = array(
            "token" => "",
            "profile_views" => 0,
            "oauth_id" => null
        );

        $time_now = time();

        foreach ($user_info as $user) {

            //some CMS create dummy users 
            if ($user['id'] == 0) {

                continue;
            }

            $users[$i] = $this->set_value($user, $defs);
            $users[$i] += $user;

            $users[$i]['read_time'] = isset($user['read_time']) ? $user['read_time'] : $time_now;
            $users[$i]['no_posts'] = isset($user['no_posts']) ? $user['no_posts'] : 0;

            $roles[$i] = array(
                'uid' => $user['id'],
                'rid' => ROLE_USER,
                'is_primary' => 1
            );
            $i++;
        }

        $attrs = array("id", "username", "name", "pass", "token", "mail", "created",
            "last_access", "read_time", "user_status", "avatar", "signature", "no_posts", "profile_views", "oauth_id");
        $qry = $this->prepare_ins_qry($users, $attrs, "codo_users");

        $attrs = array("uid", "rid", "is_primary");
        $qry = $this->prepare_ins_qry($roles, $attrs, "codo_user_roles");
    }

    private function set_value($arr, $defs) {

        $_arr = array();

        foreach ($defs as $index => $def_value) {

            if (!isset($arr[$index])) {

                $_arr[$index] = $def_value;
            } else {

                $_arr[$index] = $arr[$index];
            }
        }

        return $_arr;
    }

    /**
     * 
     */
    public function get_last_post_id() {

        $qry = 'SELECT MAX(post_id) AS max_post_id FROM ' . PREFIX . 'codo_posts';
        $res = $this->db->query($qry);

        $max = $res->fetch();

        return ((int) $max['max_post_id']);
    }

    /**
     * 
     */
    private function backup_admin_account() {

        $user = \CODOF\User\User::get();

        $_SESSION['backup_admin_account'] = $user->getInfo();
        $_SESSION['backup_admin_account']['avatar'] = $user->rawAvatar;
    }

    public function reset_admin_account($admin_mail) {

        $admin = $_SESSION['backup_admin_account'];

        //we need to preserve the imported user id, the no of posts and
        //profile views
        unset($admin['id'], $admin['no_posts'],$admin['created'], $admin['profile_views'], $admin['signature'], $admin['rawAvatar'], $admin['rid'], $admin['rids'], $admin['read_time']);

        \DB::table('codo_user_roles')->where('uid', $_SESSION['new_admin_uid'])
                ->update(array('rid' => ROLE_ADMIN));
        $me = \CODOF\User\User::getByMail($admin_mail);
        //update user with $admin where mail=$admin_mail
        $me->set($admin);
        //reset admin userid
        $_SESSION[UID . 'USER']['id'] = $_SESSION['new_admin_uid'];
    }

    /**
     * 
     * Prepares a multivalued single insert query to perform a fast multiple insert
     * @param type $cats
     * @param type $attrs
     * @param type $table
     */
    private function prepare_ins_qry($cats, $attrs, $table) {

        $fields = implode(", ", $attrs);

        $values = array();

        if (empty($cats)) {

            return false;
        }

        foreach ($cats as $cat) {

            $qry = "INSERT IGNORE INTO " . PREFIX . "$table ($fields) VALUES ";
            $cat = array_merge(array_flip($attrs), $cat);

            $fil_cat = array();
            foreach ($cat as $key => $value) {

                if (!in_array($key, $attrs)) {

                    continue;
                }

                if (!is_numeric($value)) {

                    $value = "'" . addslashes($value) . "'";
                    //$value = str_replace('\n', '\\n', $value);
                }

                $fil_cat[] = $value;
            }

            $values[] = "\n(" . implode(", ", $fil_cat) . ")";
        }

        $qry .= implode(",", $values) . ";\n\n";

        //return $qry;
        //print_r(strip_tags($qry));
        $this->db->query($qry);
    }

}
