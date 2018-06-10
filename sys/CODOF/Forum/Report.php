<?php

namespace CODOF\Forum;

class Report {

    public static $open = 1;
    public static $closed = 0;

    /**
     * Get the types of reports that can be made
     * @return type
     */
    public function getReportTypes() {

        $types = \DB::table(PREFIX . 'codo_report_types')->get();
        return $types;
    }

    /**
     * Checks if this topic has already been reported by the current user
     * @param type $tid
     * @return type
     */
    private function topicHasBeenReportedBefore($tid) {

        $reports = \DB::table(PREFIX . 'codo_reports')
                ->where('topic_id', '=', $tid)
                ->where('uid', '=', \CODOF\User\CurrentUser\CurrentUser::id())
                ->count();

        return $reports > 0;
    }

    /**
     * Reports a topic
     * If a report already exits it updates the type and details of that report
     * @param type $tid
     * @param type $type
     * @param type $details
     */
    public function reportTopic($tid, $type, $details) {

        if (!$this->topicHasBeenReportedBefore($tid)) {
            \DB::table(PREFIX . 'codo_reports')
                    ->insert(array(
                        'topic_id' => $tid,
                        'post_id' => 0,
                        'report_type' => $type,
                        'details' => $details,
                        'status' => Report::$open,
                        'uid' => \CODOF\User\CurrentUser\CurrentUser::id(),
                        'time' => time()
            ));
        } else {

            \DB::table(PREFIX . 'codo_reports')
                    ->where('topic_id', '=', $tid)
                    ->where('uid', '=', \CODOF\User\CurrentUser\CurrentUser::id())
                    ->update(array(
                        'report_type' => $type,
                        'details' => $details
            ));
        }
    }

    /**
     * Gets open reports from the database
     * @return array
     */
    public function getActiveReports() {

        $reports = \DB::table(PREFIX . 'codo_reports AS r')
                ->select('r.id', 'r.report_type', 'r.details', 't.topic_id', 't.title', 't.uid', \DB::raw('COUNT(r.topic_id) AS num_reports'))
                ->join(PREFIX . 'codo_topics AS t', 'r.topic_id', '=', 't.topic_id')
                ->where('r.status', Report::$open)
                ->groupBy('t.topic_id')
                ->get();

        return $reports;
    }

    /**
     * Gets closed reports from the database
     * @return array
     */
    public function getClosedReports() {

        $reports = \DB::table(PREFIX . 'codo_reports AS r')
                ->select('r.id', 'r.details', 'r.report_type', 't.topic_id', 't.title', 't.uid', \DB::raw('COUNT(r.topic_id) AS num_reports'))
                ->join(PREFIX . 'codo_topics AS t', 'r.topic_id', '=', 't.topic_id')
                ->where('r.status', Report::$closed)
                ->groupBy('t.topic_id')
                ->get();

        return $reports;
    }

}
