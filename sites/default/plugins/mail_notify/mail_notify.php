<?php

/*
 * @CODOLICENSE
 */

class Notify {

    /**
     *
     * @var \PDO 
     */
    private $db;

    public function __construct() {

        $this->db = \DB::getPDO();
    }

    /**
     * 
     *  cid tid   uid type
     *  10  null  1   2
     *  10  2     1   3
     * 
     * @param type $cid
     * @param type $tid
     * @param type $pid
     * @param type $offset
     * @return type
     */
    public function getData($cid, $tid, $pid, $offset) {

        /**
         *  Alternative with JOIN 
         *  select `u`.`id`, `u`.`username`, `u`.`mail`, `t`.`title`, `p`.`imessage`, `p`.`omessage`, `s`.`type`
          from `codo_users` as `u`
          inner join `codo_notify_subscribers` as `s` on `s`.`uid` = `u`.`id`
          join(SELECT id, MAX(tid) AS tid FROM codo_notify_subscribers GROUP BY uid) s2
          ON s2.id=s.id AND s.tid=s2.tid
          left join `codo_posts` as `p` on `p`.`post_id` = 54
          left join `codo_topics` as `t` on `t`.`topic_id` = 18
          where `s`.`type` = 3
          and `s`.`cid` = 3
          and `p`.`topic_id` = 18
          and `s`.`uid` <> 1
          limit 400 offset 0
         * 
         */
        $data = \DB::table(PREFIX . 'codo_notify_subscribers AS s')
                        ->select('u.id', 'u.username', 'u.mail', 't.title', 'p.imessage', 'p.omessage', 's.type', 'c.cat_name')
                        ->join(PREFIX . 'codo_users AS u', 's.uid', '=', 'u.id')
                        ->leftJoin(PREFIX . 'codo_posts AS p', 'p.post_id', '=', \DB::raw($pid))
                        ->leftJoin(PREFIX . 'codo_topics AS t', 't.topic_id', '=', \DB::raw($tid))
                        ->leftJoin(PREFIX . 'codo_categories AS c', 'c.cat_id', '=', \DB::raw($cid))
                        ->where('s.type', '=', CODOF\Forum\Notification\Subscriber::$NOTIFIED)
                        ->where('s.cid', '=', $cid)
                        ->where(function($query) use($tid) {

                            $query->where('s.tid', '=', 0)
                            ->orWhere('s.tid', '=', \DB::raw($tid));
                        })
                        ->where('p.topic_id', '=', $tid)
                        ->where('s.uid', '<>', \CODOF\User\CurrentUser\CurrentUser::id())
                        ->skip($offset)->take(400)->get();

        return $data;
    }

    public function queue_mails($args) {


        $cid = (int) $args['cid'];
        $tid = (int) $args['tid'];
        $pid = (int) $args['pid'];
        $type = $args['type'];

        if ($type == 'new_topic') {

            $subject = \CODOF\Util::get_opt('topic_notify_subject');
            $message = \CODOF\Util::get_opt('topic_notify_message');
        } else {

            $subject = \CODOF\Util::get_opt('post_notify_subject');
            $message = \CODOF\Util::get_opt('post_notify_message');
        }


        $mail = new \CODOF\Forum\Notification\Mail();

        $me = CODOF\User\User::get();
       
        $mails = array();
        $offset = 0;
        while ($data = $this->getData($cid, $tid, $pid, $offset)) {

            foreach ($data as $info) {

                //do not send email to the user making the post
                if ($me->id == $info['id'] || $info['mail'] == null) {
                    continue;
                }

                $user = array(
                    "id" => $me->id,
                    "username" => $me->username
                );

                $post = array(
                    "omessage" => $info['omessage'],
                    "imessage" => $info['imessage'],
                    "url" => \CODOF\Forum\Forum::getPostURL($tid, $info['title'], $pid),
                    "id" => $info['id'],
                    "username" => $info['username'],
                    "title" => $info['title'],
                    "category" => $info['cat_name']
                );

                $mail->user = $user;
                $mail->post = $post;

                $mails[] = array(
                    "to_address" => $info['mail'],
                    "mail_subject" => html_entity_decode($mail->replace_tokens($subject), ENT_NOQUOTES, "UTF-8"),
                    "body" => html_entity_decode($mail->replace_tokens($message), ENT_QUOTES, "UTF-8")
                );
            }

            \DB::table(PREFIX . 'codo_mail_queue')->insert($mails);

            $offset += 400;
        }
    }

}

$pn = new Notify();
\CODOF\Hook::add('after_notify_insert', array($pn, 'queue_mails'));
