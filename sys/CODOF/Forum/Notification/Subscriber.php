<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Forum\Notification;

//codo_subscribers
/**
 *
 * subscriptions
 *
 *  -> only new topics
 *  -> new topics as well as replies to existing topics
 *
 *
 *  When you are not subscribed , codoforum will set the subscription as 'Default'.
 *  When you post a reply or create new topic the subscription type will be set to 'Notified'.
 *
 *
 * Subscription to a topic has more preference than subscription to the topic's
 * category .
 *
 * Table codo_subscribers
 *
 * mentions = new topics = new replies [they are distinguished in codo_notify]
 *
 *
 *
 * id uid cid   tid  type
 * 1  2     1    following
 * 2  1     null
 *
 */
class Subscriber {

    /**
     * You will not be notified of anything
     * @var int
     */
    static $MUTED = 1;

    /**
     * You will be notified of mentions
     * if category level: see "new" label
     * @var int
     */
    static $DEFAULT = 2;

    /**
     * You will be notified of new replies/topics/mentions & see unread count of each topic.
     * @var int
     */
    static $FOLLOWING = 3;

    /**
     * FOLLOWING plus you will receive email notifications for the same
     * @var int
     */
    static $NOTIFIED = 4;

    /**
     *
     * @var int
     */
    static $maxRows = 1000;



    /**
     *
     * @var \PDO
     */
    private $db;

    public function __construct() {

        $this->db = \DB::getPDO();
    }

    /**
     * Adds a new type of subscription
     */
    public function addType($type, $function) {

        //subscriber_ is prepended to distinguish beetween other hooks
        \CODOF\Hook::add('subscriber_' . $type, $function);
    }


    /**
     * Calls registered type of subscription
     */
    public function callType($type, $data, $offset) {

        $result = \CODOF\Hook::call('subscriber_' . $type, array($data, $offset));
        
        return isset($result[0]) ? $result[0] : FALSE; 
        //Hook may return results from multiple calls, we know there is only
        //one call so [0]
    }


    /**
     * Registers all core types of subscription
     */
    public function registerCoreTypes() {

        $this->addType('new_topic', array(new \CODOF\Forum\Notification\Subscriber, 'ofCategory'));
        $this->addType('new_reply', array(new \CODOF\Forum\Notification\Subscriber, 'ofTopic'));
        $this->addType('move_topic', array(new \CODOF\Forum\Notification\Subscriber, 'ofTopic'));
    }


    /**
     * Get user ids following this category
     * @param int $cid
     * @return array
     */
    /* public function followersOfCategory($cid) {

      //purpose: to notify new topic
      $qry = 'SELECT uid FROM ' . PREFIX . 'codo_notify_subscribers WHERE cid = ' . $cid . ' AND type > 1';
      $res = $this->db->query($qry);

      if ($res) {

      return $res->fetchAll();
      }

      return array(); //return empty array on no results
      } */

    /**
     * Get all subscribed users of this category
     * @param array $args
     * @return array
     */
    public function ofCategory($args) {

        $data = $args[0];
        $offset = $args[1];

        $cid = $data->cid;

        $uid = \CODOF\User\CurrentUser\CurrentUser::id();
        if(property_exists($data, 'notifyFrom')) {
            
            //>3.7 notification
            $uid = $data->notifyFrom; 
        }

        //purpose: to notify mention, new post
        //tid=NULL to get users subscribed at category level , as long as this
        //function is called only when a new topic is created it will work .

        $subscribers = \DB::table(PREFIX . 'codo_notify_subscribers')
                        ->select('uid', 'type')
                        ->where('cid', '=', $cid)
                        ->where('tid', '=', 0) //category level subscription
                        ->where('uid', '<>', $uid)
                        ->skip($offset)->take(self::$maxRows)->get();

        $idTypes = $this->groupBySubscriptionType($subscribers);

                //add notifications for FOLLOWING & NOTIFIED that a new topic is created
        return array_merge($idTypes['FOLLOWING'], $idTypes['NOTIFIED']);
    }

    /**
     * Filter users $uids that have muted $cid category
     * @param int $catid
     * @param array $uids
     * @return array
     */
    protected function mutedOfCategory($catid, $uids) {

        $cid = (int) $catid;
        //tid=NULL to get users subscribed at category level , as long as this
        //function is called only when a new topic is created it will work .
        $qry = 'SELECT uid FROM ' . PREFIX . 'codo_notify_subscribers WHERE cid = ' . $cid . ' AND tid=0 AND type= ' . self::$MUTED
                . ' AND uid IN (' . implode(",", $uids) . ')';
        $res = $this->db->query($qry);

        if ($res) {

            return $res->fetchAll(\PDO::FETCH_COLUMN, 0);
        }

        return array(); //return empty array on no results          }
    }

    /**
     * Filter $uids that have muted topic's category or topic with preference to topic
     * @param int $cid
     * @param int $tid
     * @param array $uids
     * @return array
     */
    protected function mutedOfTopic($cid, $tid, $uids) {

        $cid = (int) $cid;
        $tid = (int) $tid;
        //tid=NULL to get users subscribed at category level , as long as this
        //function is called only when a new topic is created it will work .
        $qry = 'SELECT MAX(tid), uid FROM ' . PREFIX . 'codo_notify_subscribers '
                . ' WHERE '
                . ' cid = ' . $cid
                . ' AND ( tid=0 OR  tid= ' . $tid . ') '
                . ' AND type= ' . self::$MUTED
                . ' AND uid IN (' . implode(",", $uids) . ') GROUP BY uid';

        $res = $this->db->query($qry);

        if ($res) {

            return $res->fetchAll(\PDO::FETCH_COLUMN, 0);
        }

        return array(); //return empty array on no results          }
    }

    /**
     * Get users subscribed to topic or category giving preference to topic
     * @param int $cid
     * @param int $tid
     * @param int $offset
     * @return array
     */
    public function ofTopic($args) {

        $data = $args[0];
        $offset = $args[1];

        $cid = $data->cid;
        $tid = $data->tid;

        $uid = \CODOF\User\CurrentUser\CurrentUser::id();
        if(property_exists($data, 'notifyFrom')) {
            
            //>3.7 notification
            $uid = $data->notifyFrom; 
        }
        
        //purpose to notify mention, new post
        //group by to remove duplicates and max(tid) to give more priority
        //to subscription status of topic than the category
        $subscribers = \DB::table(PREFIX . 'codo_notify_subscribers')
                        ->select(\DB::raw('MAX(tid)'), 'uid', 'type')
                        ->where('cid', '=', $cid)
                        ->where(function($query) use ($tid) {

                            $query->where('tid', '=', '0')
                            ->orWhere('tid', '=', $tid);
                        })
                        ->where('uid', '<>', $uid)
                        ->groupBy('uid')
                        ->skip($offset)->take(self::$maxRows)->get();

        $idTypes = $this->groupBySubscriptionType($subscribers);

        //add notifications for FOLLOWING & NOTIFIED that a new topic is created
        return array_merge($idTypes['FOLLOWING'], $idTypes['NOTIFIED']);
    }

    /**
     * Get no. of followers of a particular topic
     * @param int $tid
     * @return int
     */
    public function followersOfTopic($tid) {

        $result = \DB::table(PREFIX . 'codo_notify_subscribers')
                ->select(\DB::raw('COUNT(id) AS followers'))
                ->where('type', '>', self::$DEFAULT)
                ->where('tid', '=', $tid)
                ->groupBy('tid')
                ->first();

        return ($result['followers'] === null) ? '0 ' : $result['followers'];
    }

    /**
     * Get no. of followers of a particular category
     * @param int $cid
     * @return int
     */
    public function followersOfCategory($cid) {

        $result = \DB::table(PREFIX . 'codo_notify_subscribers')
                ->select(\DB::raw('COUNT(id) AS followers'))
                ->where('type', '>', self::$DEFAULT)
                ->where('cid', '=', $cid)
                ->where('tid', '=', '0')
                ->groupBy('tid')
                ->first();

        return ($result['followers'] === null) ? '0 ' : $result['followers'];
    }

    /**
     *
     * @param string $type
     * @param object $data
     * @return array
     */
    public function mutedOf($type, $data) {

        if ($type == 'new_topic') {

            return $this->mutedOfCategory($data->cid, $data->mentions);
        }

        if ($type == 'new_reply') {

            return $this->mutedOfTopic($data->cid, $data->tid, $data->mentions);
        }

        return array();
    }

    /**
     * Insert a subscriber for topic
     * @param int $cid
     * @param int $tid
     */
    public function toTopic($cid, $tid, $type = 3) {

        $uid = \CODOF\User\CurrentUser\CurrentUser::id();

        $qry = "SELECT type FROM " . PREFIX . "codo_notify_subscribers WHERE "
                . " cid=$cid AND tid=$tid AND uid=$uid";

        $res = $this->db->query($qry);
        $row = $res->fetch();

        if (!empty($row)) {


            $presentType = $row['type'];

            if ($type != $presentType) {

                $qry = "UPDATE " . PREFIX . "codo_notify_subscribers "
                        . " SET type=$type WHERE cid=$cid AND tid=$tid AND uid=$uid";

                $this->db->query($qry);
            }
        } else {

            $qry = "INSERT INTO " . PREFIX . "codo_notify_subscribers (cid, tid, uid, type) "
                    . " VALUES ($cid, $tid, $uid, $type) ";

            $this->db->query($qry);
        }
    }

    /**
     * Insert a subscriber for topic
     * @param int $cid
     */
    public function toCategory($cid, $type = 3) {

        $this->toTopic($cid, 0, $type);
    }

    /**
     * Get subscription level for a topic
     * @param int $tid
     * @return int
     */
    public function levelForTopic($tid) {

        $result = \DB::table(PREFIX . 'codo_notify_subscribers')
                ->select('type')
                ->where('tid', '=', $tid)
                ->where('uid', '=', \CODOF\User\CurrentUser\CurrentUser::id())
                ->first();

        //default subscription is 2
        return (empty($result)) ? self::$DEFAULT : $result['type'];
    }

    /**
     * Get subscription level for a category
     * @param int $cid
     * @return int
     */
    public function levelForCategory($cid) {

        $result = \DB::table(PREFIX . 'codo_notify_subscribers')
                ->select('type')
                ->where('cid', '=', $cid)
                ->where('tid', '=', '0')
                ->where('uid', '=', \CODOF\User\CurrentUser\CurrentUser::id())
                ->first();

        //default subscription is 2
        return (empty($result)) ? self::$DEFAULT : $result['type'];
    }

    /**
     * Group subscribers(users) by their subscription type
     * @param array $subscribers
     * @return array
     */
    public function groupBySubscriptionType($subscribers) {

        $idTypes = array(
            "MUTED" => array(),
            "DEFAULT" => array(),
            "FOLLOWING" => array(),
            "NOTIFIED" => array()
        );

        $groups = array(
            self::$MUTED => "MUTED",
            self::$DEFAULT => "DEFAULT",
            self::$FOLLOWING => "FOLLOWING",
            self::$NOTIFIED => "NOTIFIED"
        );

        foreach ($subscribers as $subscriber) {

            $idTypes[$groups[$subscriber['type']]][] = (int) $subscriber['uid'];
        }

        return $idTypes;
    }

    /**
     * Extracts array of @mentions from a string
     * @param string $str
     * @return array
     */
    public function getMentions($str) {

        $matches = array();
        preg_match_all('/(^|\s)(@\w+)/', $str, $matches);

        return $matches[2];
    }

    /**
     * Returns userids from mentions that exist in the database
     * @param array $mentions
     * @return array
     */
    public function getIdsThatExisits($mentions) {

        if (empty($mentions)) {

            return array();
        }

        $names = array();
        //build usernames array from @mentions
        foreach ($mentions as $name) {

            //usernames can't have @ , so it is fine.
            $names[] = str_replace("@", "", $name);
        }

        $ids = \DB::table(PREFIX . 'codo_users')
                        ->whereIn('username', $names)->lists('id');

        return $ids;
    }

    /**
     * Get category and topic ids of all subscriptions of a user
     * @param int $uid
     * @return array
     */
    public function getCategorySubscriptions($uid) {

        return \DB::table(PREFIX . 'codo_notify_subscribers')
                        ->select('cid', 'tid', 'type', 'cat_name', 'cat_img', 'cat_alias')
                        ->join(PREFIX . 'codo_categories', 'cat_id', '=', 'cid')
                        ->where('uid', $uid)
                        ->where('tid', '0')
                        ->get();
    }

    /**
     * Gets ONLY topic subsciptions and NOT category subscriptions
     * @param int $uid
     * @return array
     */
    public function getTopicSubscriptions($uid) {

        return \DB::table(\DB::raw(PREFIX . 'codo_notify_subscribers AS n'))
                        ->join(\DB::raw(PREFIX . 'codo_topics AS t'), 't.topic_id', '=', 'n.tid')
                        ->join(\DB::raw(PREFIX . 'codo_users AS u'), 'u.id', '=', 't.uid')
                        ->select('u.id', 'n.cid', 'n.tid', 'u.avatar', 't.title', 'n.type')
                        ->where('n.uid', $uid)
                        ->where('n.tid', '<>', '0')
                        ->get();
    }

    /**
     * Does a subscription already exists for this topic ?
     */
    public function existsForTopic($cid, $tid, $uid) {

        $subs = \DB::table(PREFIX . 'codo_notify_subscribers')
                ->select('type')
                ->where('uid', $uid)
                ->where('cid', $cid)
                ->where('tid', $tid)
                ->get();

        return !empty($subs);
    }

}
