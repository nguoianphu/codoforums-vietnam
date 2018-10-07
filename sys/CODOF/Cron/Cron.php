<?php

/*
 * @CODOLICENSE
 */

/**
 * 
 * PHP implementation of CRON 
 * 
 * You can add a function/job to execute in a cron run 
 * by calling the function/job to the cron job queue in the hook 'before_cron_run' 
 * 
 *   
 * cron_status
 *  1 => running
 *  0 => not running
 */

namespace CODOF\Cron;

class Cron extends Jobs {

    /**
     * php time limit. time period for which cron is allowed to run
     * @var int
     */
    protected $time_limit = 300;

    /**
     * Database connection
     * @var object
     */
    protected $db;

    /**
     * 
     * Makes sure no cron is started when another cron is still running
     * @var type 
     */
    private $serial = true;

    /**
     * All crons in codo_crons table
     * @var type 
     */
    private $crons = null;

    /**
     * This contains ids of crons that are running
     * @var type 
     */
    public $runners = array();

    /**
     * Conatins all crons that are scheduled to be run once
     * @var type 
     */
    public $one_timers = array();

    /**
     * Contains output of cron run which is stored in codo_crons table
     * @var type 
     */
    public $log = '';

    public function __construct() {

        $this->db = \DB::getPDO();
    }

    /**
     * This function is called on every page load by the user . 
     * 
     * It checks for any cron that is scheduled to run 
     * 
     * @return boolean
     */
    public function run($cron = null) {

        $crons = $this->acquire_lock($cron);

        if (!$crons) {

            $this->cleanUp();
            //could not acquire lock because another cron is already running
            //or the cron last completed is not older than cron_interval
            return false;
        }

        //script must continue even if user aborts
        @ignore_user_abort(true);

        //parallel crons may cause write conflicts
        if (!$this->serial) {
            //write and end session
            session_write_close();
        }

        //amount of time for which cron is allowed to run
        set_time_limit($this->time_limit);

        ob_start();

        $this->add_core_hooks();

        foreach ($crons as $cron) {

            if ($cron['cron_name'] == 'core') {

                //run all core jobs of cron
                $this->run_jobs();
            }


            //there is no guarantee that user defined plugins wont produce
            //errors . 
            try {

                \CODOF\Hook::call('on_cron_' . $cron['cron_name']);
            } catch (Exception $ex) {
                
            }
        }

        $this->log = ob_get_clean();
        //cron jobs done, set status as not running
        $this->release_lock();

        //below hook should not be used to run cron jobs
        \CODOF\Hook::call('after_cron_run');

        if ($this->log != '') {
            
            \CODOF\Log::info('Cron:' . $this->log);
        }
        return true;
    }

    /**
     * 
     * Schedules a hook to be run at specified interval 
     * 
     * $interval can be
     *  - hourly  [cron runs every hour ]
     *  - daily   [cron runs every day  ]
     *  - weekly  [cron runs every week ]
     *  - monthly [cron runs every month]
     *  
     * if $start is not passed, cron will start after the speicified interval
     * if $start is given as 'now', it will start immdiately
     * $start can also be passed as a UNIX timestamp, so that cron first starts
     * after that time
     *
     * @param type $name
     * @param type $timestamp
     * @return
     */
    public function set($name, $interval, $start = 0) {

        //if schedule for this cron does not exists
        if (!$this->cron_exists($name)) {

            $first = $start;

            if ($start == 0) {

                $first = time();
            }

            $interval = $this->get_time($interval);

            //=== because 'now' will get cast to int otherwise
            if ($start === 'now') {

                $first = time() - $interval;
            }

            $vals = array("name" => $name, "type" => "recurrence", "interval" => $interval, "last" => $first, "status" => 0);

            $this->ins_cron($vals);
            return true;
        }

        return false;
    }

    /**
     * 
     * Schedules a cron to be executed only once after the specified interval
     * i.e $timestamp
     * 
     * @param type $name name of the cron
     * @param type $timestamp interval after which cron will run
     */
    public function setOnce($name, $timestamp) {

        //if schedule for this cron does not exists
        if (!$this->cron_exists($name)) {

            $interval = $timestamp;
            $first = time();

            $vals = array("name" => $name, "type" => "once", "interval" => $interval, "last" => $first, "status" => 0);

            $this->ins_cron($vals);
        }
    }

    /**
     * 
     * Resets a previously set cron.
     * 
     * @param string $name cron name
     * @param string $type cron type 'once' or 'recurrence'
     * @param int $interval cron interval
     */
    public function reset($name, $type, $interval) {

        $qry = 'UPDATE ' . PREFIX . 'codo_crons SET cron_type=:type, cron_interval=:interval '
                . ' WHERE cron_name = :name';

        $obj = $this->db->prepare($qry);

        $obj->execute(array("name" => $name, "type" => $type, "interval" => $interval));
    }

    /**
     * Removes previously set cron . 
     * Returns true if cron was removed successfully
     * @param type $name
     * @return type
     */
    public function remove($name) {

        $qry = "DELETE FROM " . PREFIX . "codo_crons WHERE cron_name = :name";
        $obj = $this->db->prepare($qry);

        return $obj->execute($name);
    }

    /**
     * Checks whether a cron with given name exists in the codo_crons table
     * @param type $name
     */
    private function cron_exists($name) {

        $qry = "SELECT id FROM " . PREFIX . "codo_crons WHERE cron_name=:name";
        $obj = $this->db->prepare($qry);
        $obj->execute(array("name" => $name));

        $res = $obj->fetch();
        return !empty($res);
    }

    /**
     * Inserts cron into codo_crons table
     * @param type $vals
     */
    private function ins_cron($vals) {

        $qry = 'INSERT INTO ' . PREFIX . 'codo_crons (cron_name, cron_type, cron_interval, cron_started, cron_last_run, cron_status) '
                . 'VALUES(:name,:type,:interval,:start,:last,:status)';

        $vals["start"] = $vals["last"];
        $obj = $this->db->prepare($qry);
        $obj->execute($vals);
    }

    /**
     * 
     * Returns any crons that have to run now
     * 
     * If there is no other active cron and the last cron run is older
     * than cron_interval, cron is returned with the status set as running
     * in codo_crons table
     * 
     * crons in self::$crons are ordered by oldest to newest
     */
    private function acquire_lock($name = null) {

        $crons = $this->get_crons($name);

        $crons_to_run = array();
        $this->one_timers = array();

        foreach ($crons as $cron) {

            if ($this->serial && $cron['cron_status'] == 1) {
                return false;
            }

            //get any cron to be run now and make sure that the cron is
            //not in running status
            //if $name is passed , it means this is a force run
            if ((time() - (int) $cron['cron_interval'] > (int) $cron['cron_last_run'] || $name != null) && $cron['cron_status'] == 0) {

                $crons_to_run[] = $cron;
                $this->runners[] = $cron['id']; //this cron will be run

                if ($cron['cron_type'] == 'once') {

                    $this->one_timers[] = $cron['id'];
                }
            }
        }

        if (!empty($this->runners)) {

            //set cron status as running
            $qry = 'UPDATE ' . PREFIX . 'codo_crons SET cron_status = 1, '
                    . ' cron_started=' . time() . ' WHERE id IN (' .
                    implode(',', $this->runners) . ')';

            $this->db->query($qry);
        }

        return $crons_to_run;
    }

    /**
     * 
     * Sets cron status as completed and cron_last_run as time()
     * It also sets all running crons as completed that are older than 
     * 1 hours . Since there is no cron that can run for 1 hour
     */
    private function release_lock() {

        $hour_old = time() - 3600; //one hour old UNIX time

        $qry = 'UPDATE ' . PREFIX . 'codo_crons SET cron_status = 0, '
                . ' cron_last_run = ' . time() . ' WHERE id IN (' .
                implode(',', $this->runners) . ') OR '
                . ' ( cron_last_run < ' . $hour_old . ' AND '
                . ' cron_status = 1 )';

        $this->db->query($qry);

        if (!empty($this->one_timers)) {

            $qry = 'DELETE FROM ' . PREFIX . 'codo_crons WHERE id IN'
                    . ' (' . implode(',', $this->one_timers) . ')';
            $this->db->query($qry);
        }
    }

    /**

      Stop any zombie crons
     */
    private function cleanUp() {

        //do it just once per session
        if (!isset($_SESSION['codo_zombie_crons_cleanup'])) {

            $_SESSION['codo_zombie_crons_cleanup'] = true;
            $hour_old = time() - 3600;

            $qry = 'UPDATE ' . PREFIX . 'codo_crons SET cron_status = 0 '
                    . ' WHERE '
                    . ' cron_last_run < ' . $hour_old . ' AND '
                    . ' cron_status = 1';

            $this->db->query($qry);
        }
    }

    /**
     * Get all cron data on page load the crons are ordered by last run time
     */
    private function get_crons($name = null) {

        $cond = '';
        $vals = array();

        if ($name != null) {

            $cond = ' WHERE cron_name = :name';
            $vals = array("name" => $name);
        }

        $qry = 'SELECT * FROM ' . PREFIX . 'codo_crons ' . $cond . ' ORDER BY cron_last_run';
        $obj = $this->db->prepare($qry);
        $obj->execute($vals);

        return $obj->fetchAll();
    }

    /**
     * 
     * Gets UNIX time
     * @param type $time
     * @return int
     */
    private function get_time($time) {

        //=== because if int is given , all strings will be cast to int
        if ($time === 'hourly') {

            return 3600;
        }

        if ($time === 'daily') {

            return 3600 * 24;
        }

        if ($time === 'weekly') {

            return 3600 * 24 * 7;
        }

        if ($time === 'monthly') {

            return 3600 * 24 * 30;
        }

        return $time;
    }

}
