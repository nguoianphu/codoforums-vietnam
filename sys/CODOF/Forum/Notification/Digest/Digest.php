<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Forum\Notification\Digest;

/**
 * 
 * Digest class generates email digests .
 * Email digests may be of two types :
 * 
 * 1. Daily
 * 2. Weekly 
 *
 * Digest will only send you unread notifications every X interval
 * 
 * TODO: Send digest when topic /category gets popular .
 */
class Digest {

    /**
     *
     * @var \PDO 
     */
    protected $db;
    public $daily;
    public $dailyText;

    /**
     * No. of new posts
     * @var int 
     */
    private $newPosts = 0;

    /**
     * No. of new topics
     * @var int 
     */
    private $newTopics = 0;

    /**
     * 
     * I create a topic because 
     *   --> i am interested in its replies
     * 
     * I follow a topic because
     *   --> i am interested in its replies
     * 
     * I follow a category because
     *   --> i am interested in its topics
     *  
     * 
     */
    
    
    public function sendDailyDigest() {
        
        $user = \CODOF\User\User::get();
        $frequency = $user->prefers('notification_frequency');

        if ($frequency == 'daily') {

            $this->sendDigest();
        }
        
    }
    
    public function sendWeeklyDigest() {
                
        $user = \CODOF\User\User::get();
        $frequency = $user->prefers('notification_frequency');

        if ($frequency == 'weekly') {

            $this->sendDigest();
        }
    }
    
    public function sendDigest() {

        $smarty = \CODOF\Smarty\Single::get_instance(SYSPATH . 'CODOF/Forum/Notification/Digest/', true);

        $user = \CODOF\User\User::get();

        $smarty->assign('site_title', \CODOF\Util::get_opt('site_title'));
        $smarty->assign('brand_img', \CODOF\Util::get_opt('brand_img'));
        $smarty->assign('username', $user->username);

        $date = date('Y-F-j-S', time());
        list($year, $month, $day, $ordinal) = explode("-", $date);

        $dayInfo = array(
            "year" => $year,
            "month" => $month,
            "day" => $day,
            "ordinal" => $ordinal
        );

        $smarty->assign('dayInfo', $dayInfo);

        $smarty->assign('statistics_img', 'http://i.imgur.com/7sBa4Ow.png'); //RAW
        $smarty->assign('create_new_img', 'http://i.imgur.com/E0MhBwI.png'); //RAW

        $notifier = new \CODOF\Forum\Notification\Notifier();
        $events = $notifier->get(TRUE, 0, 'asc'); //get all unread notifications

        $sortedEvents = $this->sort($events);

        $smarty->assign('events', $sortedEvents);

        $smarty->assign('new_posts', $this->newPosts . " ");
        $smarty->assign('new_topics', $this->newTopics . " ");

        if (empty($events)) {

            $smarty->assign('nothing_new', true);
        } else {

            $smarty->assign('nothing_new', false);
        }

        $frequency = $user->prefers('notification_frequency');

        $html = $smarty->fetch("$frequency.tpl");

        $text = $smarty->fetch("{$frequency}Text.tpl");

        $this->daily = $html;

        $this->dailyText = $text;

        $mailer = new \CODOF\Forum\Notification\Mail();

        $mailer->setHTML($mailer->replace_tokens($this->dailyText));
        $mailer->to = $user->mail;
        $mailer->subject = _t('Daily digest - ') . \CODOF\Util::get_opt('site_title');
        $mailer->message = $this->daily;

        $mailer->send_mail();
    }

    /**
     * 
     * @param type $events
     * @return array
     * 
     * array(
     * 
     *      //mentions of topics/categories, i am not following
     *      //[User] mentioned you in [title]
     *      "rawMentions" => array (
     * 
     *           array (
     * 
     *              "title" //topic title
     *              "tid" //topic id
     *              "pid" //post id
     *              "uid" //user id
     *              "avatar" //absolute url
     *              "username"
     *          )         
     *      )
     * 
     *      //replies, mentions of my topics
     *      "myTopics" = array (
     * 
     * 
     *          "$tid" => array (
     * 
     *             "meta" => array (
     *              
     *                  "new_topic_pid" => $pid //point to post id of new topic
     *                  //other info
     *             ),
     *              
     *             "$pid" => array(
     *              
     *                 "mention" => true
     *                  ...other info
     *          )
     *      )
     * 
     *      //replies, mentions of topics of topics/categories i follow
     *      "following" = array (
     * 
     *          //similar to [myTopics]
     *      )
     * 
     * 
     *  
     * )
     * 
     *   //if event is of type "new_reply", it means either i have created that
     *   //topic or i am following that topic
     *   //if event is of type "new_topic", it means either i have created that
     *   //topic or i am following that category
     *   //if event is of type "mention" AND there is no corresponding "new_reply"
     *   //or "new_topic", it means it is a rawMention
     *   //so to segregate rawMentions i have to store topic ids of "new_reply"
     *   //& "new_topic" and then isset() to check is all that will be left
     */
    protected function sort($events) {

        $_events = array(
            "rawMentions" => array(),
            "myTopics" => array(),
            "following" => array()
        );

        $tids = array(); //topic ids array
        $mentions = array(); //

        $user = \CODOF\User\User::get();

        foreach ($events as $event) {

            $data = json_decode($event['data'], true);

            if ($event['type'] == 'new_reply' || $event['type'] == 'new_topic') {

                $tids[$data['tid']] = 1; //to use isset instead of in_array

                $type = ($data['tuid'] == $user->id) ? 'myTopics' : 'following';

                //store topic meta once to avoid redundant data
                if (!isset($_events[$type][$data['tid']])) {

                    $_events[$type][$data['tid']] = array(
                        "meta" => $this->getMetaInfo($data)
                    );

                    $_events[$type][$data['tid']]["replies"] = array();
                }

                //tell this topic is new
                if ($event['type'] == 'new_topic') {

                    $_events[$type][$data['tid']]['meta']['new_topic_pid'] = $data['pid'];
                    $this->newTopics++;
                } else {

                    $this->newPosts++;
                }

                $date = date('M-d-h-i-A', $event['created']);
                list($month, $day, $hour, $minute, $meridiem) = explode("-", $date);
                $time = array(
                    "month" => $month,
                    "day" => $day,
                    "hour" => $hour,
                    "minute" => $minute,
                    "meridiem" => $meridiem
                );

                $_events[$type][$data['tid']]["replies"][$data['pid']] = array(
                    "actor" => $data['actor'],
                    "pid" => $data['pid'],
                    "time" => $time,
                    "message" => $data['message']
                );
            }

            if ($event['type'] == 'mention') {

                $mentions[] = $event;
            }
        }

        //now merge $mentions with $_events
        foreach ($mentions as $mention) {

            $data = json_decode($mention['data'], true);

            //if this mention exists in "new_reply" or "new_topic"
            if (isset($tids[$data['tid']])) {

                if ($data['tuid'] == $user->id) {

                    $_events['myTopics'][$data['tid']]['replies'][$data['pid']]['mention'] = true;
                } else {

                    $_events['following'][$data['tid']]['replies'][$data['pid']]['mention'] = true;
                }
            } else {

                $date = date('M-d-h-i-A', $mention['created']);
                list($month, $day, $hour, $minute, $meridiem) = explode("-", $date);
                $data['time'] = array(
                    "month" => $month,
                    "day" => $day,
                    "hour" => $hour,
                    "minute" => $minute,
                    "meridiem" => $meridiem
                );

                $_events['rawMentions'][] = $data;
            }
        }


        return $_events;
    }

    /**
     * 
     * @param array $data
     * @return array
     */
    protected function getMetaInfo($data) {

        return array(
            "title" => $data['title'],
            "cid" => $data['cid'],
            "tid" => $data['tid']
                //"actor" => $data['actor']
        );
    }

}
