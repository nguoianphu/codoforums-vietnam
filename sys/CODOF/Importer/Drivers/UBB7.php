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

class UBB7 {

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

        $qry = "SELECT b.FORUM_ID AS cat_id, b.FORUM_TITLE AS cat_name, b.FORUM_DESCRIPTION AS cat_description,
                    b.FORUM_SORT_ORDER AS cat_order, b.FORUM_PARENT AS cat_pid
                    FROM  ".DBPRE."FORUMS AS b WHERE b.FORUM_IS_ACTIVE=1";

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

        $qry = "SELECT t.TOPIC_ID AS topic_id, t.TOPIC_SUBJECT AS title,t.FORUM_ID AS cat_id,t.TOPIC_CREATED_TIME AS topic_created,
                t.TOPIC_LAST_REPLY_TIME AS topic_updated, t.TOPIC_LAST_POSTER_ID AS uid, t.POST_ID AS post_id,
                p.POST_BODY AS message
                 FROM ".DBPRE."TOPICS AS t 
                 INNER JOIN ".DBPRE."POSTS AS p ON t.POST_ID=p.POST_ID
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

        $qry = "SELECT t.FORUM_ID AS cat_id, p.TOPIC_ID AS topic_id, p.USER_ID AS uid,
                p.POST_ID AS post_id,p.POST_BODY AS message, p.POST_POSTED_TIME AS post_created,
                p.POST_LAST_EDITED_TIME AS post_modified
                FROM ".DBPRE."POSTS AS p
                INNER JOIN ".DBPRE."TOPICS as t ON t.TOPIC_ID=p.TOPIC_ID
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
        
        $qry = "SELECT u.USER_ID AS id, u.USER_LOGIN_NAME AS username, u.USER_DISPLAY_NAME AS name, u.USER_PASSWORD AS pass,
                 u.USER_REGISTRATION_EMAIL AS mail, p.USER_SIGNATURE AS signature, u.USER_REGISTERED_ON AS created,
                 d.USER_LAST_VISIT_TIME AS last_access, 
                 CASE WHEN u.USER_IS_APPROVED='yes' THEN 1 ELSE 0 END AS user_status,               
                 p.USER_AVATAR AS avatar
                FROM ".DBPRE."USERS AS u
                INNER JOIN ".DBPRE."USER_PROFILE AS p ON p.USER_ID=u.USER_ID
                INNER JOIN ".DBPRE."USER_DATA AS d ON d.USER_ID=u.USER_ID
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
        
        $qry = 'SELECT USER_ID AS uid FROM '.DBPRE.'USERS WHERE USER_REGISTRATION_EMAIL=?';
        $obj = $this->db->prepare($qry);

        $obj->execute(array($mail));
        $res = $obj->fetch();

        if (!empty($res)) {

            return $res['uid'];
        }
        
        return false;
    }
    
    public function modify_posts($post) {
        
     
        $post['imessage'] = str_replace('<<GRAEMLIN_URL>>/', DURI . SMILEY_PATH, html_entity_decode($post['imessage']));
        $post['omessage'] = str_replace('<<GRAEMLIN_URL>>/', DURI . SMILEY_PATH, html_entity_decode($post['imessage']));

        return $post;
    }
    
}