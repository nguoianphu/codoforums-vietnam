<?php

$smarty = \CODOF\Smarty\Single::get_instance();

if (isset($_GET['action']) && $_GET['action'] == 'close' && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {


    $report_id = $_POST['report_id'];

    DB::table(PREFIX . 'codo_reports')
            ->where('topic_id', '=', $report_id)
            ->update(array('status' => \DB::raw(\CODOF\Forum\Report::$closed)));

    header('Location: index.php?page=moderation/reports');
    exit;
}


$processor = new ReportProcessor();


$smarty->assign('reports', $processor->getActiveReports());
$smarty->assign('closed', $processor->getClosedReports());
$content = $smarty->fetch('moderation/reports.tpl');

class ReportProcessor {

    private $report;
    private $types;

    public function __construct() {

        $this->report = new \CODOF\Forum\Report();
        $this->types = $this->processTypes($this->report->getReportTypes());
    }

    /**
     * Gets all open reports processed to be directly used in UI
     * @return type
     */
    public function getActiveReports() {

        return $this->processReports($this->report->getActiveReports());
    }

    /**
     * Gets all open reports processed to be directly used in UI
     * @return type
     */
    public function getClosedReports() {

        return $this->processReports($this->report->getClosedReports());
    }

    /**
     * Generates UI friendly array for use in smarty
     * @param type $reports
     * @return type
     */
    private function processReports($reports) {

        $_reps = array();
        foreach ($reports as $report) {

            $_reps[] = array(
                "id" => $report['id'],
                "topic_id" => $report['topic_id'],
                "reason" => $this->getReportReason($report['details'], $report['report_type']),
                "href" => \CODOF\Forum\Forum::getPostURL($report['topic_id'], $report['title']),
                "title" => $report['title'],
                "num_reports" => $report['num_reports']
            );
        }

        return $_reps;
    }

    /**
     * Returns an associative array of types as id => name
     * @param type $types
     * @return type
     */
    private function processTypes($types) {

        $_types = array();
        foreach ($types as $type) {

            $_types[$type['id']] = $type['name'];
        }

        return $_types;
    }

    /**
     * Returns custom reason if custom report else report name from types
     * @param type $details
     * @param type $type
     * @return type
     */
    private function getReportReason($details, $type) {

        if ($type == 3) {
            return $details;
        }

        return $this->types[$type];
    }

}
