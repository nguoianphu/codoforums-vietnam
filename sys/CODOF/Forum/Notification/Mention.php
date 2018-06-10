<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Forum\Notification;

class Mention {

    /**
     * Get valid mentions from the users table
     * @param array $_mentions
     * @return array
     */
    public function getValid($_mentions) {

        //prevent DOS
        $mentions = array_slice($_mentions, 0, 30);

        $validMentions = \DB::table(PREFIX . 'codo_users')->select('username')
                        ->whereIn('username', $mentions)->get();

        return $validMentions;
    }

    /**
     * Assumnption: Only gets called when a new topic is created
     * Filters out mentionable usernames from mentions i.e returns non-mentionable
     * usernames
     * @param int $cid
     */
    public function getNotMentionable($cid) {


        $mentions = $_GET['mentions'];
        $cid = (int) $cid;

        foreach ($mentions as $mention) {

            $usernames[] = str_replace("@", "", $mention);
        }

        $res = \DB::table(PREFIX . 'codo_users AS u')->select(\DB::raw('u.username,u.id, MAX(p.granted) AS allowed'))
                        ->leftJoin(PREFIX . 'codo_user_roles AS r', 'r.uid', '=', 'u.id')
                        ->leftJoin(PREFIX . 'codo_permissions AS p', function($join) use($cid) {

                            $join->on('p.permission', '=', \DB::raw('\'view all topics\''))
                            ->on('p.rid', '=', 'r.rid')
                            ->on('p.cid', '=', \DB::raw($cid))
                            ->on('p.tid', '=', \DB::raw(0));
                        })
                        ->whereIn('u.username', $usernames)
                        ->groupBy('u.id')->get();


        $mutedIds = array();

        if ($res && count($res)) {
            $uids = array_column($res, 'id');
            $subscriber = new Subscriber();            
            $mutedIds = $subscriber->mutedOf('new_topic', Data::ofCategory($cid, $uids));
        }

        $nonMentionables = array();

        foreach ($res as $user) {

            if ($user['allowed'] === 0 || in_array($user['id'], $mutedIds)) {

                $nonMentionables[] = $user['username'];
            }
        }

        return $nonMentionables;
    }

    /**
     * Get matched users with limit 10
     * @param string $qry
     * @return array
     */
    public function find($qry, $catid, $tid) {

        $cid = (int) $catid;
        $tid = (int) $tid;

        $selector = '';
        if ($cid) {

            $selector = ', MAX(p.granted) AS allowed';
        }

        $users = \DB::table(PREFIX . 'codo_users AS u');

        $users->select(\DB::raw('u.id, u.username, u.name, u.avatar' . $selector))
                ->where(function($q) use ($qry) {

                    $q->where('u.username', 'LIKE', "$qry%")
                    ->orWhere('u.name', 'LIKE', "$qry%");
                })
                ->where('u.mail', '<>', 'anonymous@localhost');


        if ($cid) {

            $users->leftJoin(PREFIX . 'codo_user_roles AS r', 'r.uid', '=', 'u.id')
                    ->leftJoin(PREFIX . 'codo_permissions AS p', function($join) use($cid) {

                        $join->on('p.permission', '=', \DB::raw('\'view all topics\''))
                        ->on('p.rid', '=', 'r.rid')
                        ->on('p.cid', '=', \DB::raw($cid))
                        ->on('p.tid', '=', \DB::raw(0));
                    })
                    ->groupBy('u.id');
        }

        $users = $users->take(10)->get();

        $type = '';

        if ($cid) {

            $type = 'new_topic';
        }
        if ($tid) {

            $type = 'new_reply';
        }

        $mutedIds = array();
        if ($type != '' && count($users)) {

            $uids = array_column($users, 'id');
            $subscriber = new Subscriber();
            $mutedIds = $subscriber->mutedOf($type, Data::ofTopic($cid, $tid, $uids));
        }

        $_users = array();
        $i = 0;

        foreach ($users as $user) {

            //if ($user['name'] == null) {

            $_users[$i]["username"] = $user['username'];
            /* } else {

              $_users[$i]["username"] = str_replace(" ", "&nbsp;", $user['name']);
              } */



            $_users[$i]["avatar"] = \CODOF\Util::get_avatar_path($user['avatar'], $user['id'], false);
            if ($cid) {

                //if $cid is not provided can't say whether user is mentionable or not
                $notMentionable = in_array($user['id'], $mutedIds) || $user['allowed'] === 0;
                $_users[$i]["mentionable"] = (!$notMentionable) ? 'yes' : 'no'; //better for js -> y/n
            }
            $i++;
        }

        return $_users;
    }

}
