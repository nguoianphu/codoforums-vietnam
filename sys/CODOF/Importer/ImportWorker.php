<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Importer;

use Illuminate\Database\Capsule\Manager as Capsule;

class ImportWorker {

    public $max_rows = 100;
    public $connected = false;
    public $_DB;
    public $import_admin_mail;

    /**
     *
     * @var Import 
     */
    protected $im;

    /**
     * Below is definition of local variable $offset used in
     * ins_posts()
     * 
     * Used when posts table do not contain topic content
     * In such a case ins_topics() itself creates posts and
     * inserts in the codoforum posts table . But this 
     * will interfere with post ids of ins_posts(), so in 
     * ins_posts() we use an offset which is the id of the 
     * last inserted post . 
     * @var type 
     */
    private $offset = 0;
    private $importer;

    public function __construct($_DB, $import_from) {

        //database connection info of remote server
        $this->_DB = $_DB;
        $this->importer = $import_from;
    }

    public function connect_db() {

        $localPDO = \DB::getPDO();

        $capsule = new Capsule;
        $capsule->addConnection($this->_DB, 'remote');
        $connection = $capsule->getConnection('remote');
        $remotePDO = $connection->getPdo();

        $class = '\\CODOF\\Importer\\Drivers\\' . $this->importer;
        $this->fetch = new $class($remotePDO);
        $this->connected = true; //\CODOF\DB::$connected ? true : false;

        $this->fetch->max_rows = $this->max_rows;
        $this->fetch->set_prefix($this->_DB['prefix']);

        $this->im = new Import($localPDO, $this->fetch);
    }

    /**
     * Empty all tables
     */
    public function empty_tables($what) {

        $this->im->empty_tables($what);
    }

    public function import_cats() {

        $cats = $this->fetch->get_cats();
        $this->im->ins_cat($cats);
    }

    public function import_topics() {

        $start = 0;
        $t_pid = 0;
        $p_pid = 0;


        while ($topics = $this->fetch->get_topics($start)) {

            $topic_posts = array();

            //insert all topics
            $t_pid = $this->im->ins_topics($topics, $t_pid, !$this->fetch->post_has_topic);

            if (!$this->fetch->post_has_topic) {

                foreach ($topics as $topic) {

                    $topic_posts[] = array(
                        "cat_id" => $topic['cat_id'],
                        "topic_id" => $topic['topic_id'],
                        "post_id" => ++$p_pid,
                        "uid" => $topic['uid'],
                        "message" => $topic['message'],
                        "post_created" => $topic['topic_created'],
                        "post_modified" => $topic['topic_updated']
                    );
                }

                //insert all posts
                $this->im->ins_posts($topic_posts);
            }


            $start += $this->max_rows;
        }
    }

    public function import_posts() {

        $start = 0;
        $offset = 0;

        if (!$this->fetch->post_has_topic) {

            $offset = $this->im->get_last_post_id();
        }

        while ($posts = $this->fetch->get_posts($start)) {

            $this->im->ins_posts($posts, $offset);

            $start += $this->max_rows;
        }
    }

    public function import_users() {

        $start = 0;

        while ($users = $this->fetch->get_users($start)) {

            $this->im->ins_users($users);

            $start += $this->max_rows;
        }

        $this->im->reset_admin_account($this->import_admin_mail);
    }

    public function isset_admin_account() {

        $_SESSION['new_admin_uid'] = $this->fetch->get_user_by_mail($this->import_admin_mail);
        return $_SESSION['new_admin_uid'];
    }

    public function has_codopm_tables() {

        if ($this->importer == 'Codoforum') {

            return $this->fetch->has_codopm_tables();
        }

        return false;
    }

    public function import_table($table, $truncate = true) {

        if($this->fetch->tableExists($table)) {

            if ($truncate) {
                $this->im->empty_tables($table);
            }
            
            $start = 0;
            while ($permissions = $this->fetch->import_table($table, $start)) {

                $this->im->ins_table($table, $permissions);

                $start += $this->max_rows;
            }
        }

    }

    /**
     * Gets the version of codoforum being imported
     * @return int
     */
    public function get_imported_cf_ver() {

        return $this->fetch->get_cf_version();
    }

}
