<?php

/*
 * @CODOLICENSE
 */

namespace CODOF;

class Time {

    /**
     *
     * $time -> UNIX timestamp
     * Returns formatted time
     *
     * X seconds ago or -> Same minute
     * X minutes ago or -> Same hour
     * X hours ago or -> Within 12 hours
     * Today at HH:MM or -> Within 24 hours
     * D days ago or -> Within 7 days
     * M D at HH:MM -> Same year
     * M D 'Y at HH:MM -> Different year
     */
    public static function get_pretty_time($time) {

        if (!$time) {

            return $time;
        }

        $pretty_time = '';
        $at = _t("at");
        $time = intval($time);

        if (self::same_min($time)) {

            $tdiff = time() - $time;

            if ($tdiff == 0) {

                $pretty_time = _t("just now");
            } else {

                $pretty_time = _t("%s second ago", "%s seconds ago", $tdiff);
            }
        } else if (self::same_hour($time)) {

            $tdiff = round((time() - $time) / 60);
            $pretty_time = _t("%s minute ago", "%s minutes ago", $tdiff);
        } else if (self::within_hours($time, 12)) {

            $tdiff = round((time() - $time) / (60 * 60));
            $pretty_time = _t("%s hour ago", "%s hours ago", $tdiff);
        } else if (self::same_day($time)) {

            $pretty_time = _t('Today') . ' ' . $at . ' ' . date('g:i a', $time);
        } else if (self::within_days($time, 7)) {

            $tdiff = round((time() - $time) / (60 * 60 * 24));
            $ddiff = (!$tdiff) ? 1 : $tdiff;
            $pretty_time = _t("%s day ago", "%s days ago", $ddiff) . ' ' . $at . ' ' . date('g:i a', $time);
        } else if (self::same_year($time)) {

            $pretty_time = date('M j', $time) . ' ' . $at . ' ' . date('g:i a', $time);
        } else {

            //Different year
            $pretty_time = date('M j \'y', $time) . ' ' . $at . ' ' . date('g:i a', $time);
        }

        return $pretty_time;
    }

    private static function same_min($time) {

        return (time() - $time) < 60;
    }

    private static function same_hour($time) {

        return round((time() - $time) / 60) < 60;
    }

    private static function within_hours($time, $hrs) {

        return round((time() - $time) / (60 * 60)) < $hrs;
    }

    private static function same_day($time) {

        return date('Ymd') == date('Ymd', $time);
    }

    private static function within_days($time, $days) {

        return self::within_hours($time, $days * 24);
    }

    private static function same_year($time) {

        return date('Y') == date('Y', $time);
    }
    
    /**
     * 
     * Gets unix time X hours before now 
     * @param type $by
     * @return int
     */
    public function unix_get_time_hour($by = 1) {

        $seconds = 60 * 60 * $by; 
        
        return time() - $seconds;
    }
    
    /**
     *  
     * Gets unix time X days before now
     * @param type $by
     */
    public function unix_get_time_day($by = 1) {
        
        //24 hours = 1day
        return $this->unix_get_time_hour(24 * $by);
    }
    
}
