<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Importer\Drivers;

/**
 * Kunena 3 requires Joomla 3, hence no compatibility concerns with older
 * Joomla versions
 */

class Kunena3 {

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

        $qry = "SELECT c.id AS cat_id, c.name AS cat_name, c.description AS cat_description, c.ordering AS cat_order, c.parent_id AS cat_pid
                    FROM  ".DBPRE."kunena_categories AS c";

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
     *  user id who creaed -> uid
     * post message       -> message [Must be selected when $post_has_topic=false Otherwise OPTIONAL]
     * post id            -> post_id [Must be selected when $post_has_topic=true  Otherwise OPTIONAL]
     * @param type $start
     * @return type
     */
    public function get_topics($start) {

        $qry = "SELECT t.id AS topic_id, t.subject AS title,t.category_id AS cat_id,t.first_post_time AS topic_created,
                t.last_post_time AS topic_updated, t.last_post_id, t.last_post_userid AS last_post_uid, 
                t.last_post_guest_name AS last_post_name,t.last_post_time AS last_post_time,
                t.first_post_userid AS uid, t.first_post_id AS post_id
                FROM ".DBPRE."kunena_topics AS t 
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

        $qry = "SELECT m.catid AS cat_id, m.thread AS topic_id, m.id AS post_id, m.userid AS uid,
                p.message AS message, m.time AS post_created,
                m.modified_time AS post_modified
                FROM ".DBPRE."kunena_messages AS m 
                INNER JOIN ".DBPRE."kunena_messages_text AS p ON m.id = p.mesid
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
       
        //In Joomla, users do not have a status column but have a block column
        //which is logically opposite to status, hence a CASE is used below
        
        //In Joomla, DATETIME is used instead of UNIX timestamo hence the 
        //UNIX_TIMESTAMP() is used
        $qry = "SELECT u.id AS id, u.username AS username, u.name AS name, u.password AS pass,
                 u.email AS mail, k.signature AS signature, UNIX_TIMESTAMP(u.registerDate) AS created,
                 UNIX_TIMESTAMP(u.lastvisitDate) AS last_access, k.avatar AS avatar,
                 CASE WHEN u.block=1 THEN 0 ELSE 1 END AS user_status
                FROM ".DBPRE."users AS u
                LEFT JOIN ".DBPRE."kunena_users AS k ON k.userid = u.id
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
        
        $qry = 'SELECT id AS uid FROM '.DBPRE.'users WHERE email=?';
        $obj = $this->db->prepare($qry);

        $obj->execute(array($mail));
        $res = $obj->fetch();

        if (!empty($res)) {

            return $res['uid'];
        }
        
        return false;
    }
}
