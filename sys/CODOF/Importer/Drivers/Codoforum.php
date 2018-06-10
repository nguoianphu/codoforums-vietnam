<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Importer\Drivers;

/**
 * This is a self-importer created to import data from an 
 * older version of codoforum  
 * 
 */
class Codoforum extends Driver {

    public function __construct(\PDO $db) {

        parent::__construct($db);
    }

    /**
     * Mention whether your posts table contain topic message as a post or not ?
     * 
     * If it is set to true , make sure the query in get_posts() below returns
     * messages of all topics too
     * 
     * Note: Importer runs faster when posts table has the message of topics
     *       but sadly not all forum systems are the same :(
     * @var boolean 
     */
    public $post_has_topic = true;

    /**
     * 
     * Selects 
     * category id          -> cat_id
     * category name        -> cat_name
     * category description -> cat_description
     * category order       -> cat_order
     * category parent id   -> cat_pid 
     * @return type
     */
    public function get_cats() {

        $qry = "SELECT * FROM  " . DBPRE . "codo_categories";

        $res = $this->db->query($qry);
        return $res->fetchAll();
    }

    /**
     * 
     * Selects
     * topic id           -> topic_id
     * topic title        -> title
     * category id        -> cat_id
     * topic created time -> topic_created
     * topic updated time -> topic_updated
     * last post id       -> last_post_id    [optional]
     * last post uid      -> last_post_uid   [optional]
     * last post name     -> last_post_name  [optional]
     * last post time     -> last_post_time  [optional] 
     * user id who creaed -> uid
     * post message       -> message [Must be selected when $post_has_topic=false Otherwise OPTIONAL]
     * post id            -> post_id [Must be selected when $post_has_topic=true  Otherwise OPTIONAL]
     * @param int $start
     * @return array
     */
    public function get_topics($start) {

        $qry = "SELECT * FROM " . DBPRE . "codo_topics WHERE topic_status<>0
                 LIMIT $this->max_rows OFFSET $start";

        $res = $this->db->query($qry);
        $result = $res->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * 
     * Selects
     * category id        -> cat_id
     * topic id           -> topic_id
     * post id            -> post_id
     * user id            -> uid
     * post message       -> message
     * post created time  -> post_created
     * post modified time -> post_modified
     * @param int $start
     * @return array
     */
    public function get_posts($start) {

        $qry = "SELECT * FROM " . DBPRE . "codo_posts WHERE post_status <> 0
                LIMIT $this->max_rows OFFSET $start";

        $res = $this->db->query($qry);
        $result = $res->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     * Selects
     * user id              -> uid
     * username             -> username
     * nickname             -> name
     * password             -> pass
     * email                -> mail
     * forum signature      -> signature
     * user created time    -> created
     * user last login time -> last_access
     * user status          -> status
     * user avatar url      -> avatar
     * user role id         -> rid [OPTIONAL]
     * @param type $start
     * @return type
     */
    public function get_users($start) {

        $qry = "SELECT * FROM " . DBPRE . "codo_users
                LIMIT $this->max_rows OFFSET $start";

        $res = $this->db->query($qry);
        $result = $res->fetchAll(\PDO::FETCH_ASSOC);
        //var_dump($result);
        return $result;
    }

    /**
     * Get the userid by email
     * This is used pre-import to check if admin account email address
     * given is correct or not
     * 
     * @param string $mail
     */
    public function get_user_by_mail($mail) {

        $qry = 'SELECT id AS uid FROM ' . DBPRE . 'codo_users WHERE mail=?';
        $obj = $this->db->prepare($qry);

        $obj->execute(array($mail));
        $res = $obj->fetch();

        if (!empty($res)) {

            return $res['uid'];
        }

        return false;
    }

    /**
     * Checks if database has codopm related tables
     * @return type
     */
    public function has_codopm_tables() {

        try {

            $qry = "SELECT 1 FROM  " . DBPRE . "codopm_messages, " . DBPRE . "codopm_config";
            $res = $this->db->query($qry);
        } catch (\Exception $e) {

            return false;
        }
        return $res;
    }

    
    public function import_table($table, $start) {
 
        $qry = "SELECT * FROM " . DBPRE . $table . " 
                LIMIT $this->max_rows OFFSET $start";

        $res = $this->db->query($qry);
        $result = $res->fetchAll(\PDO::FETCH_ASSOC);
        //var_dump($result);
        return $result;
               
    }

    public function get_cf_version() {

        $qry = "SELECT option_value FROM codo_config WHERE option_name='version'";

        $res = $this->db->query($qry);
        $row = $res->fetch();

        return $row['option_value'];
    }    

}
