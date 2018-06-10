<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Forum\Notification;

/**
 *
 *
 * NOTE
 * ====
 *
 * Only Digest requires tuid & message.
 *
 */

/**
 *
 *  Notifications
 *  =============
 *
 *  Notify API is used to connect users of each others' actions.
 *
 *  ---
 *
 *
 *  ###How does it work ?
 *
 *  The Codoforum core or any of its plugins define a notification data
 *  in codo_notify_text and then store all the notifications in the
 *  codo_notify table.
 *
 *  The 'data' stored must be in JSON format.
 *  Example. User X (id = 2) liked User Y(id = 3)'s post
 *
 *  ---Table row data---
 *
 *  userid = 3 //
 *  type = 1 //1 is notfication type for "LIKE"
 *  data : {
 *
 *     fromId: 2
 *  }
 *
 *  With the above data, you can then format the comlete message as "User X liked ''"
 *
 *  The user will continuously request this table for any new notifications .
 *  The notifications are not just limited to user knowing "someone liked your post".
 *
 *
 *
 *  Note: Email notifications should not be included here . They are stored in
 *  a different table , so this table should not be used to send email
 *  notification. The reason for this simple, to have quick response times .
 *  The email notifications use the concept of CRON, so the response time does
 *  not matter, whereas these notifications use the concept of piggybacking and
 *  fast response time has the highest priority .
 *
 */
class Notifier {

    /**
     *
     * @var \PDO
     */
    private $db;

    public function __construct() {

        $this->db = \DB::getPDO();
    }

    /**
     *  Dequeues notification queue and enqueues email queue
     *  Table codo_notify
     *  type           id data              is_read
     *  new_reply      1  {tid: 4, pid: 5}  0      --> depends on subscription
     *  new_topic      2  {tid: 4}          1      --> depends on subscription
     *  new_badge      2  {bid: 3}          0      --> system notification
     *  vote_up        2  {pid: 5}          1      --> depend on user settings
     *  new_like       1  {pid: 7}          1      --> depend on user settings
     *  mention        4  {pid: 3}          0      --> depends on subscription
     */
    public function dequeueNotify() {

        $qry = 'SELECT q.id,q.type,q.nid,t.data FROM ' . PREFIX . 'codo_notify_queue AS q'
                . ' INNER JOIN codo_notify_text AS t ON q.nid=t.id';
        $res = $this->db->query($qry);

        if (!$res) {

            return false;
        }

        $maxID = 0;

        $queue = $res->fetchAll();

        $subscriber = new Subscriber();
        //$user = \CODOF\User\User::get();
        //$frequency = $user->prefers('notification_frequency');

        foreach ($queue as $queuedNotification) {

            $maxID = max($queuedNotification['id'], $maxID);
            $type = $queuedNotification['type'];
            $nid = $queuedNotification['nid'];
            $data = json_decode($queuedNotification['data']);

            if (!empty($data->mentions)) {

                $mutedIds = $subscriber->mutedOf($type, $data);
                $notMuted = array_diff($data->mentions, $mutedIds);
                $this->notify($notMuted, 'mention', $nid);
            }

            $offset = 0;

            //get all subscribers for this notification type
            while ($ids = $subscriber->callType($type, $data, $offset)) {

                //add notifications for subscribers
                $this->notify($ids, $type, $nid);

                $offset += Subscriber::$maxRows;
            }

            //queue all emails which will be sent in different cron run
            //For now only works for Forum entity
            //TODO: Extend to other entities like PMs
            if (property_exists($data, 'pid')) {

                \CODOF\Hook::call('after_notify_insert', array(
                    "cid" => $data->cid,
                    "tid" => $data->tid,
                    "pid" => $data->pid,
                    "type" => $type
                ));
            }
        }

        //delete old queued notifications
        $qry = 'DELETE FROM ' . PREFIX . 'codo_notify_queue WHERE id <= ' . $maxID;
        $this->db->query($qry);
    }

    /**
     * Inserts notification for each userid in $ids
     * @param array $ids
     * @param string $type
     * @param id $nid notification id -> codo_notify_text.id
     * @return boolean
     */
    public function notify($ids, $type, $nid) {

        if (empty($ids)) {

            return false;
        }

        $insertData = array();
        $created = time();
        $is_read = 0;

        foreach ($ids as $uid) {

            $insertData[] = array(
                "type" => $type,
                "uid" => $uid,
                "nid" => $nid,
                "created" => $created,
                "is_read" => $is_read
            );
        }

        return \DB::table(PREFIX . 'codo_notify')->insert($insertData);
    }

    /**
     * @param string $type - Type of notification
     * @param array $data - JSON data saved for notification
     * @param int $statusLink - A link to an external entity to maintain status
     * For eg. notifications for topic reply will have a statusLink as the topic id
     * Which can be then used to fetch notification based on topic type
     * $type helps to know what type of entity information will $statusLink hold
     * @return bool
     */
    public function queueNotify($type, $data, $statusLink = -1) {

        if (!isset($data['actor'])) {

            $user = \CODOF\User\User::get();
            $data["actor"] = array(
                "username" => $user->username,
                "id" => $user->id,
                "role" => \CODOF\User\User::getRoleName($user->rid),
                "avatar" => $user->rawAvatar
            );
        }

        //Insert notification data JSON encoded
        $nid = \DB::table(PREFIX . 'codo_notify_text')->insertGetId(
                array("data" => json_encode($data), "status_link" => $statusLink)
        );

        //queue notification
        $qry = "INSERT INTO " . PREFIX . "codo_notify_queue (type, nid) "
                . " VALUES(:type, :nid)";

        $stmt = $this->db->prepare($qry);

        $created = $stmt->execute(array(
            "type" => $type,
            "nid" => $nid
        ));

        $cron = new \CODOF\Cron\Cron();
        $cron->setOnce('notify', 0);

        return $created;
    }

    /**
     * Deletes rows from codo_notify_text and codo_notify with matching external entity id
     * @param string $type - Type of notification
     * @param int $statusLink - Matching id of external entity
     */
    public function unlinkNotify($type, $statusLink) {

        $q = "DELETE n, t "
                . " FROM " . PREFIX . " codo_notify n INNER JOIN " . PREFIX . "codo_notify_text t"
                . " ON n.nid=t.id "
                . " WHERE n.type = :type"
                . " AND t.status_link = " . $statusLink;

        $status = \DB::delete($q, array(":type" => $type));

        return $status;
    }

    /**
     * COMMENTED -> Note: Marks fetched notifications as read
     * @param bool $wantOnlyUnread fetch only unread(true) or all notifications(false)
     * @param int $take no. of rows to fetch
     * @return array
     */
    public function get($wantOnlyUnread = false, $take = 10, $orderBy = 'desc', $offset = 0) {

        $sql = \DB::table(PREFIX . 'codo_notify AS n')
                ->join(PREFIX . 'codo_notify_text AS t', 't.id', '=', 'n.nid')
                ->select('n.id', 'n.type', 't.data', 'n.created', 'n.is_read', 't.status_link')
                ->where('n.uid', '=', \CODOF\User\CurrentUser\CurrentUser::id());

        if ($wantOnlyUnread) {

            $sql->where('n.is_read', '=', '0');
        }

        $sql->orderBy('n.created', $orderBy); //->groupBy('n.nid');
        //want some notifications
        if ($take > 0) {

            $sql->take($take);
        }
        if ($offset > 0) {

            $sql->skip($offset);
        }

//        echo $sql->toSql();
        $events = $sql->get();

        //if latest event itself is read then the older events obviously will be read
        /* if ($events && $events[0]['is_read'] == 0) {

          $latestEvent = $events[0]['created'];
          $this->markAsRead($latestEvent);
          } */
        return $events;
    }

    /**
     * @return array
     */
    public function getLatest($afterLatest, $take = 10) {

        $sql = \DB::table(PREFIX . 'codo_notify AS n')
                ->join(PREFIX . 'codo_notify_text AS t', 't.id', '=', 'n.nid')
                ->select('n.id', 'n.type', 't.data', 'n.created', 'n.is_read', 't.status_link')
                ->where('n.uid', '=', \CODOF\User\CurrentUser\CurrentUser::id())
                ->where('n.is_read', '=', '0')
                ->where('n.created', '>', $afterLatest)
                ->orderBy('n.created', 'desc');

        //want some notifications
        if ($take > 0) {

            $sql->take($take);
        }

        $events = $sql->get();

        return $events;
    }

    public function getNoOfUnread() {

        $qry = 'SELECT COUNT(n.id) AS count FROM codo_notify n
                    INNER JOIN  
                        (select max(t.id) AS max_node FROM codo_notify_text t group by t.status_link) AS node
                        ON n.nid=node.max_node
                    INNER JOIN 
                        codo_users u ON u.id=n.uid
                    WHERE n.is_read=0 
                        AND n.created > u.last_notification_view_time
                        AND n.uid= ' . \CODOF\User\CurrentUser\CurrentUser::id();

        $res = $this->db->query($qry);

        if ($res) {

            $rows = $res->fetch();
            return $rows['count'];
        }

        return 0;
    }

    public function getNoOfAll() {

        $count = \DB::table(PREFIX . 'codo_notify AS n')
                ->select(\DB::raw('COUNT(id) AS number'))
                ->where('n.uid', '=', \CODOF\User\CurrentUser\CurrentUser::id())
                ->first();

        return $count['number'];
    }

    /**
     *
     * @param array $notifications
     * @return array
     */
    public function getFormattedForInline($notifications) {

        $_notes = array();

        foreach ($notifications as $notification) {

            $data = json_decode($notification['data'], true);

            $data['label'] = _t($data['label']);
            $data['notification'] = _t($data['notification']);

            if (!isset($data['bindings']['actor'])) {
                $data['bindings']['actor'] = $data['actor']['username'];
            }

            if (isset($data['bindings'])) {
                $body = $this->applyBindings($data['notification'], $data['bindings']);
            }

            $_notes[] = array(
                "data" => $data,
                "status_link" => $notification['status_link'],
                "body" => $body,
                "id" => $notification['id'],
                "created" => \CODOF\Time::get_pretty_time($notification['created']),
                "is_read" => $notification['is_read'],
                "type" => $notification['type']
            );
        }

        return $_notes;
    }

    protected function applyBindings($notification, $bindings) {

        foreach ($bindings as $key => $value) {

            $binding = "%$key%";
            $notification = str_replace($binding, $value, $notification);
        }

        return $notification;
    }

    /**
     * Marks notification prior or euqal to $eventTime as read
     * @param int $eventTime
     * @return bool
     */
    protected function markAsRead($eventTime) {

        return \DB::table(PREFIX . 'codo_notify')
                        ->where('is_read', '=', '0')
                        ->where('uid', '=', \CODOF\User\CurrentUser\CurrentUser::id())
                        ->where('created', '<=', $eventTime)
                        ->update(array("is_read" => '1'));
    }

}
