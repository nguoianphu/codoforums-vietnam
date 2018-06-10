<?php

/*
 * @CODOLICENSE
 */

namespace CODOF;

class Log {
    /*
     * 
     * Severity
     *  0 => Emergency [Any situation that renders codoforum unusable]
     *  1 => Alert [Requires immediate action] 
     *  2 => Critical 
     *  3 => Error
     *  4 => Warning 
     *  5 => Notice [normal but significant condition]
     *  6 => Info [information messages]
     */

    public static function emergency($message, $options) {

        $options['log_type'] = 'Emergency';
        $options = self::set_options($options, $message);

        self::insert($options);
    }

    public static function alert($message, $options) {

        $options['log_type'] = 'Alert';
        $options = self::set_options($options, $message);

        self::insert($options);
    }

    public static function critical($message, $options = array()) {

        $options['log_type'] = 'Critical';
        $options = self::set_options($options, $message);

        self::insert($options);
    }

    public static function error($message, $options = array()) {

        $options['log_type'] = 'Error';
        $options = self::set_options($options, $message);

        self::insert($options);
    }

    public static function warning($message, $options = array()) {

        $options['log_type'] = 'Warning';
        $options = self::set_options($options, $message);

        self::insert($options);
    }

    public static function notice($message, $options = array()) {

        $options['log_type'] = 'Notice';
        $options = self::set_options($options, $message);

        self::insert($options);
    }

    public static function info($message, $options = array()) {

        $options['log_type'] = 'Info';
        $options = self::set_options($options, $message);

        self::insert($options);
    }

    public static function insert($options) {

        if (CODO_DEBUG) {

            //file_put_contents('logs/file.log', $options['message'], FILE_APPEND | LOCK_EX);
            $db = \DB::getPDO();

            $qry = 'INSERT INTO codo_logs (uid,log_type,message,severity,trace,log_time) '
                    . 'VALUES(:uid,:log_type,:message,:severity,:trace,:log_time)';
            $stmt = $db->prepare($qry);

            $stmt->execute($options);
        }
    }

    public static function set_options($options, $message) {

        if (!isset($options['trace'])) {

            $options['trace'] = self::get_trace();
        }

        if (!isset($options['severity'])) {

            $options['severity'] = 6;
        }

        if (!isset($options['uid'])) {

            if (isset($_SESSION[UID . 'USER']['id'])) {
                $options['uid'] = $_SESSION[UID . 'USER']['id'];
            } else {
                $options['uid'] = 0;
            }
        }

        $options['log_time'] = time();
        $options['message'] = $message;

        return $options;
    }

    public static function get_trace() {

        $trace = print_r(debug_backtrace(), TRUE);
        //$caller = end($trace);
        return "Trace: " . $trace;
    }

}
