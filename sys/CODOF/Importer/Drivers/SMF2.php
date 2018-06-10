<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Importer\Drivers;

/**
 * 
 * SMF uses concept of boards . 
 * They have boards -> categories -> topics -> posts
 * 
 * But codoforum has categories -> topics -> posts 
 * 
 * We have seen from the tables that all topics and posts of SMF are linked 
 * with their boards . 
 * 
 * So, we will treat boards of SMF as categories of codoforum . We will not
 * concern ourselves with categories of SMF . 
 * 
 */

class SMF2 {

    public $max_rows = 100;
    
    /**
     * Mention whether your posts table contain topic message as a post or not ?
     * 
     * If it is set to true , make sure the query in get_posts() below returns
     * messages of all topics too
     * 
     * Note: Importer runs faster when posts table has the message of topics
     *       but sadly not all forum systems are the same :(
     * @var type 
     */    
    public $post_has_topic = true;

        
    private $db;    
    public function __construct($db) {

        $this->db = $db;
    }

    /**
     * Table prefix 
     * @var type 
     */    
    public function set_prefix($prefix) {
        
        define('DBPRE', $prefix);
    }
    
    
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

        $qry = "SELECT b.id_board AS cat_id, b.name AS cat_name, b.description AS cat_description, b.board_order AS cat_order, b.id_parent AS cat_pid
                    FROM  ".DBPRE."boards AS b";

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
     * @param type $start
     * @return type
     */
    public function get_topics($start) {

        $qry = "SELECT t.id_topic AS topic_id, p.subject AS title,t.id_board AS cat_id,p.poster_time AS topic_created,
                p.modified_time AS topic_updated, p.id_member AS uid, p.id_msg AS post_id,
                p.body AS message
                 FROM ".DBPRE."topics AS t 
                 INNER JOIN ".DBPRE."messages AS p ON t.id_first_msg=p.id_msg
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
     * @param type $start
     * @return type
     */
    public function get_posts($start) {

        $qry = "SELECT p.id_board AS cat_id, p.id_topic AS topic_id, p.id_member AS uid,
                p.id_msg AS post_id,p.body AS message, p.poster_time AS post_created,
                p.modified_time AS post_modified
                FROM ".DBPRE."messages AS p
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
        
        $qry = "SELECT u.id_member AS id, u.member_name AS username, u.real_name AS name, u.passwd AS pass,
                 u.email_address AS mail, u.signature AS signature, u.date_registered AS created,
                 u.last_login AS last_access, u.is_activated AS user_status, u.avatar AS avatar,
                 u.id_group AS rid
                FROM ".DBPRE."members AS u
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
     * @param type $mail
     */
    public function get_user_by_mail($mail) {
        
        $qry = 'SELECT id_member AS uid FROM '.DBPRE.'members WHERE email_address=?';
        $obj = $this->db->prepare($qry);

        $obj->execute(array($mail));
        $res = $obj->fetch();

        if (!empty($res)) {

            return $res['uid'];
        }
        
        return false;
    }
    
}