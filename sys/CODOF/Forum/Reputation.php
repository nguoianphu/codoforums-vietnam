<?php

namespace CODOF\Forum;

class Reputation {
    /*
     * 
     *  Reputation cannot be given/taken to same user more than once
     *  until X hours have passed
     * 
     *  User must have X reputation points or Y posts to increment reputation
     *  User must have X reputation points or Y posts to decrement reputation
     * 
     *  Each user may give/take reputation only X times a day
     * 
     * 
     */

    public $rule1;
    public $rule2;
    public $rule3;
    public $rule4;
    public $rule5;
    private $max_rep_count_reached = false;

    public function up($post_id) {

        $pid = (int) $post_id;


        $user = \CODOF\User\User::get();

        $post_info = \DB::table(PREFIX . 'codo_posts')
                ->where('post_id', '=', $pid)
                ->select('uid', 'reputation')
                ->first();

        $errors = array();
        $puid = $post_info['uid'];

        if ($this->can_up($pid, $puid)) {

            $this->inc_rep_log($user->id);

            \DB::table(PREFIX . 'codo_reputation')
                    ->insert(array(
                        'from_id' => $user->id,
                        'to_id' => $puid,
                        'post_id' => $pid,
                        'points' => 1,
                        'rep_time' => time()
            ));

            \DB::table(PREFIX . 'codo_users')
                    ->where('id', '=', $puid)
                    ->increment('reputation');

            \DB::table(PREFIX . 'codo_posts')
                    ->where('post_id', '=', $pid)
                    ->increment('reputation');



            /* $rules = \DB::table(PREFIX . 'codo_promotion_rules')
              ->select(max('reputation+posts'))
              ->where(function($query) {

              $query->where('reputation', '>', $user->reputation)
              ->where('type', '=', 1);
              })
              ->orWhere(function($query) {

              $query->where('posts', '>', $user->no_posts)
              ->where('type', '=', 1);
              })
              ->orWhere(function($query) {

              $query->where('reputation', '>', $user->reputation)
              ->where('posts', '>', $user->no_posts)
              ->where('type', '=', 0);
              });

              foreach ($rules as $rule) {

              } */
            /**
             * 
             * 100  200   and    user
             * 
             * 300  0     or
             * 
             * 
             * 0    400   or     moderator
             * 
             * 
             * 210
             * 
             * 411
             */
            echo json_encode(array("done" => true, 'rep' => $post_info['reputation'] + 1));
        } else {


            if (!$user->can('rep up')) {

                $errors[] = _t("You do not have permission to give reputation");
            }

            if (!$this->rule1) {

                $errors[] = sprintf(_t("You cannot give more than %d reps per day"), \CODOF\Util::get_opt('max_rep_per_day'));
            }

            if (!$this->rule2) {

                $errors[] = _t("You do not have enough rep points or posts to give reputation");
            }

            if (!$this->rule3) {

                $errors[] = _t("You have already given the maximum number of reps to this user, please wait for sometime");
            }

            if (!$this->rule4) {

                $errors[] = _t("You cannot give reputation to the same post again");
            }

            if (!$this->rule5) {

                $errors[] = _t("You cannot give reputation to your own post");
            }

            echo json_encode(array("done" => false, 'errors' => $errors));
        }
        \CODOF\Util::set_promoted_or_demoted_rid();
    }

    public function down($post_id) {

        $pid = (int) $post_id;


        $user = \CODOF\User\User::get();

        $post_info = \DB::table(PREFIX . 'codo_posts')
                ->where('post_id', '=', $pid)
                ->select('uid', 'reputation')
                ->first();

        $errors = array();
        $puid = $post_info['uid'];

        if ($this->can_down($pid, $puid)) {

            $this->inc_rep_log($user->id);

            \DB::table(PREFIX . 'codo_reputation')
                    ->insert(array(
                        'from_id' => $user->id,
                        'to_id' => $puid,
                        'post_id' => $pid,
                        'points' => -1,
                        'rep_time' => time()
            ));

            \DB::table(PREFIX . 'codo_users')
                    ->where('id', '=', $puid)
                    ->decrement('reputation');

            \DB::table(PREFIX . 'codo_posts')
                    ->where('post_id', '=', $pid)
                    ->decrement('reputation');

            echo json_encode(array("done" => true, 'rep' => $post_info['reputation'] - 1));
        } else {


            if (!$user->can('rep down')) {

                $errors[] = _t("You do not have permission to give reputation");
            }

            if (!$this->rule1) {

                $errors[] = sprintf(_t("You cannot give more than %d reps per day"), \CODOF\Util::get_opt('max_rep_per_day'));
            }

            if (!$this->rule2) {

                $errors[] = _t("You do not have enough rep points or posts to give reputation");
            }

            if (!$this->rule3) {

                $errors[] = _t("You have already given the maximum number of reps to this user, please wait for sometime");
            }

            if (!$this->rule4) {

                $errors[] = _t("You cannot give reputation to the same post again");
            }

            if (!$this->rule5) {

                $errors[] = _t("You cannot give reputation to your own post");
            }


            echo json_encode(array("done" => false, 'errors' => $errors));
        }

        \CODOF\Util::set_promoted_or_demoted_rid();
    }

    public function inc_rep_log($uid) {


        if ($this->max_rep_count_reached) {

            \DB::table(PREFIX . 'codo_daily_rep_log')
                    ->where('uid', $uid)
                    ->increment('rep_count', 1, array('start_rep_time' => time()));
        } else {

            \DB::table(PREFIX . 'codo_daily_rep_log')
                    ->where('uid', $uid)
                    ->increment('rep_count');
        }
    }

    public function can_up($pid, $to_id) {

        $user = \CODOF\User\User::get();
        $res = \DB::table(PREFIX . 'codo_daily_rep_log')
                ->select('rep_count', 'start_rep_time')
                ->where('uid', '=', $user->id)
                ->first();

        if (!$res) {

            $res['rep_count'] = 0;
            $res['start_rep_time'] = time();
            \DB::table(PREFIX . 'codo_daily_rep_log')
                    ->insert(array(
                        "uid" => $user->id,
                        "rep_count" => 0,
                        "start_rep_time" => time()
            ));
        }

        $max_rep_allowed = \CODOF\Util::get_opt('max_rep_per_day');


        //RULE 1: User can give max X rep per day        
        $one_day = 24 * 60 * 60;
        $within_one_day = (time() - $res['start_rep_time']) < $one_day;
        $this->max_rep_count_reached = $res['rep_count'] == $max_rep_allowed;

        $this->rule1 = !($this->max_rep_count_reached && $within_one_day);


        //RULE 2: User must have X reputation points or Y posts to increment reputation
        $rep_to_inc = \CODOF\Util::get_opt('rep_req_to_inc');
        $posts_to_inc = \CODOF\Util::get_opt('posts_req_to_inc');

        $this->rule2 = $user->reputation >= $rep_to_inc && $user->no_posts >= $posts_to_inc;

        //RULE 3: Reputation cannot be given/taken to same user more N times
        //        until X hours have passed

        $rep_times_same_user = \CODOF\Util::get_opt('rep_times_same_user');
        $rep_hours_same_user = \CODOF\Util::get_opt('rep_hours_same_user');

        $rep_seconds_same_user = $rep_hours_same_user * 60;

        $rows = \DB::table(PREFIX . 'codo_reputation')
                        ->where('from_id', '=', $user->id)
                        ->where('to_id', '=', $to_id)
                        ->where('post_id', '=', $pid)
                        ->where('rep_time', '>', time() - $rep_seconds_same_user)
                        ->select('points')->get();

        $numbers_of_reps = count($rows);

        $this->rule3 = $numbers_of_reps < $rep_times_same_user;

        //RULE 4: User cannot give reputation to the same post more than once

        $has_rep = \DB::table(PREFIX . 'codo_reputation')
                ->where('from_id', '=', $user->id)
                ->where('post_id', '=', $pid)
                ->where('points', '=', 1)
                ->get();

        $this->rule4 = !$has_rep;

        $this->rule5 = $user->id != $to_id;
        
        return $this->rule1 && $this->rule2 && $this->rule3 && $this->rule4 && $this->rule5 && $user->can('rep up'); 
    }

    public function can_down($pid, $to_id) {

        $user = \CODOF\User\User::get();
        $res = \DB::table(PREFIX . 'codo_daily_rep_log')
                ->select('rep_count', 'start_rep_time')
                ->where('uid', '=', $user->id)
                ->first();

        if (!$res) {

            $res['rep_count'] = 0;
            $res['start_rep_time'] = time();
            \DB::table(PREFIX . 'codo_daily_rep_log')
                    ->insert(array(
                        "uid" => $user->id,
                        "rep_count" => 0,
                        "start_rep_time" => time()
            ));
        }

        $max_rep_allowed = \CODOF\Util::get_opt('max_rep_per_day');


        //RULE 1: User can give max X rep per day        
        $one_day = 24 * 60 * 60;
        $within_one_day = (time() - $res['start_rep_time']) < $one_day;
        $this->max_rep_count_reached = $res['rep_count'] == $max_rep_allowed;

        $this->rule1 = !($this->max_rep_count_reached && $within_one_day);


        //RULE 2: User must have X reputation points or Y posts to increment reputation
        $rep_to_inc = \CODOF\Util::get_opt('rep_req_to_dec');
        $posts_to_inc = \CODOF\Util::get_opt('posts_req_to_dec');

        $this->rule2 = $user->reputation >= $rep_to_inc && $user->no_posts >= $posts_to_inc;

        //RULE 3: Reputation cannot be given/taken to same user more N times
        //        until X hours have passed

        $rep_times_same_user = \CODOF\Util::get_opt('rep_times_same_user');
        $rep_hours_same_user = \CODOF\Util::get_opt('rep_hours_same_user');

        $rep_seconds_same_user = $rep_hours_same_user * 60;

        $rows = \DB::table(PREFIX . 'codo_reputation')
                        ->where('from_id', '=', $user->id)
                        ->where('to_id', '=', $to_id)
                        ->where('post_id', '=', $pid)
                        ->where('rep_time', '>', time() - $rep_seconds_same_user)
                        ->select('points')->get();

        $numbers_of_reps = count($rows);

        $this->rule3 = $numbers_of_reps < $rep_times_same_user;

        //RULE 4: User cannot give reputation to the same post more than once

        $has_rep = \DB::table(PREFIX . 'codo_reputation')
                ->where('from_id', '=', $user->id)
                ->where('post_id', '=', $pid)
                ->where('points', '=', 1)
                ->get();

        $this->rule4 = !$has_rep;

        $this->rule5 = $user->id != $to_id;
        
        return $this->rule1 && $this->rule2 && $this->rule3 && $this->rule4 && $this->rule5 && $user->can('rep up');
    }

}
