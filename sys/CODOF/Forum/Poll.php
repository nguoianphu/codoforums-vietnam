<?php

namespace CODOF\Forum;

class Poll {

    const OPTION_ACTIVE = 1;
    const OPTION_DELETED = 0;

    private static $publicResult = 1;
    private static $numVotable = 1;

    /**
     * Adds a poll to topic
     * Assumes data is clean/xss handled
     * @param type $tid
     * @param type $pollTitle
     * @param type $pollData
     */
    public static function add($tid, $pollTitle, $pollData) {

        $numVotable = self::$numVotable; //$pollData['numVotable'];
        $canRecast = $pollData['canRecast'];
        $publicResult = self::$publicResult; //$pollData['publicResult'];
        $viewResultWithoutVote = $pollData['viewResultWithoutVote'];
        $endTime = $pollData['endTime'];

        $pollId = \DB::table(PREFIX . 'codo_poll_questions')->insertGetId(array(
            "topic_id" => $tid,
            "question" => $pollTitle,
            "num_votable" => $numVotable,
            "can_recast" => $canRecast,
            "public_vote_result" => $publicResult,
            "view_result_without_vote" => $viewResultWithoutVote,
            "end_time" => $endTime,
            "is_active" => 1
        ));


        $pollOptions = $pollData['options'];

        self::addOptions($pollId, $pollOptions);
    }

    /**
     * Disables existing poll
     * @param type $tid
     */
    public static function disablePoll($tid) {

        \DB::table(PREFIX . 'codo_poll_questions')
                ->where("topic_id", '=', $tid)
                ->update(array("is_active" => 0));
    }

    /**
     * Delete all options from a poll
     * @param type $pollId
     */
    public static function deleteOptions($pollId, $deletedOptions) {

        \DB::table(PREFIX . 'codo_poll_options')
                ->where('poll_id', '=', $pollId)
                ->whereIn('option_name', $deletedOptions)
                ->delete();
    }

    /**
     * Updates the existing data of a poll
     * @param type $pollId
     * @param type $pollData
     */
    public static function updatePoll($pollId, $pollTitle, $pollData) {

        \DB::table(PREFIX . 'codo_poll_questions')
                ->where('id', '=', $pollId)
                ->update(array(
                    "question" => $pollTitle,
                    "can_recast" => $pollData['canRecast'] == 'yes' ? 1 : 0,
                    "view_result_without_vote" => $pollData['viewResultWithoutVote'] == 'yes' ? 1 : 0,
                    "end_time" => $pollData['endTime'],
                    "is_active" => 1
        ));
    }

    /**
     * Adds options to an existing poll
     * @param type $pollId
     * @param type $options
     */
    public static function addOptions($pollId, $options) {

        $optionData = array();

        foreach ($options as $option) {

            $optionData[] = array(
                "poll_id" => $pollId,
                "option_name" => $option,
                "num_votes" => 0,
                "option_status" => self::OPTION_ACTIVE,
                "option_created" => time()
            );
        }

        \DB::table(PREFIX . 'codo_poll_options')->insert($optionData);
    }

    /**
     * Gets poll data for current topic
     * If there isn't a poll, returns false
     * @param type $tid
     */
    public static function get($tid) {

        $pollData = array();

        $questionData = \DB::table(PREFIX . 'codo_poll_questions as q')
                ->leftJoin(PREFIX . 'codo_poll_log as l', function($join) {

                    $join->on('l.poll_id', '=', 'q.id');
                    $join->on('l.uid', '=', \DB::raw(\CODOF\User\CurrentUser\CurrentUser::id()));
                })
                ->select("q.*", "l.uid")
                ->where('topic_id', '=', $tid)
                ->first();


        if ($questionData == null) {
            return false;
        }

        $pollData['id'] = $questionData['id'];
        $pollData['isActive'] = $questionData['is_active'];
        $pollData['hasVoted'] = $questionData['uid'] != null;
        $pollData['title'] = $questionData['question'];
        $pollData['viewWithoutVote'] = $questionData['view_result_without_vote'];
        $pollData['canRecast'] = $questionData['can_recast'];
        $pollData['endTime'] = $questionData['end_time'];

        $totalVotes = $questionData['total_votes'];

        $optionsDB = \DB::table(PREFIX . 'codo_poll_options')
                ->where('poll_id', '=', $questionData['id'])
                ->where('option_status', '=', self::OPTION_ACTIVE)
                ->get();

        $i = 0;
        foreach ($optionsDB as $option) {

            $pollData['options'][$i] = $option;
            $pollData['options'][$i]['percent'] = $totalVotes > 0 ? round(($option['num_votes'] / $totalVotes) * 100, 2) : 0;
            $i++;
        }

        return $pollData;
    }

    /**
     * Adds a new vote to log and increments number of votes for poll and option
     * If recast, soft delete old vote log, add new vote log and update votes
     * @param type $pollId
     * @param type $optionId
     * @return type
     */
    public static function vote($pollId, $optionId) {

        $voted = \DB::table(PREFIX . 'codo_poll_log')
                ->where('poll_id', '=', $pollId)
                ->where('uid', \CODOF\User\CurrentUser\CurrentUser::id())
                ->where('active', '=', self::OPTION_ACTIVE)
                ->first();

        $isRecast = !is_null($voted);

        if ($isRecast) {

            $canRecast = \DB::table(PREFIX . 'codo_poll_questions')
                    ->where('id', '=', $pollId)
                    ->pluck('can_recast');


            if ($canRecast === "0") {
                return;
            }

            //soft delete last vote from log
            \DB::table(PREFIX . 'codo_poll_log')
                    ->where('id', '=', $voted['id'])
                    ->update(array('active' => self::OPTION_DELETED));

            //decrement num_votes for old option
            \DB::table(PREFIX . 'codo_poll_options')
                    ->where('id', '=', $voted['option_id'])
                    ->decrement('num_votes');
        } else {

            \DB::table(PREFIX . 'codo_poll_questions')
                    ->where('id', '=', $pollId)
                    ->increment('total_votes');
        }

        //log new vote
        \DB::table(PREFIX . 'codo_poll_log')
                ->insert(array(
                    "poll_id" => $pollId,
                    "option_id" => $optionId,
                    "uid" => \CODOF\User\CurrentUser\CurrentUser::id(),
                    "active" => self::OPTION_ACTIVE,
                    "voted_on" => time()
        ));

        //increment num_votes for new option
        \DB::table(PREFIX . 'codo_poll_options')
                ->where('id', '=', $optionId)
                ->increment('num_votes');
    }

}
