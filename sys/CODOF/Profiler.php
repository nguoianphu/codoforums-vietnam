<?php


namespace CODOF;

/**
 * 
 * A very very simple PHP profiler using microtime for quick optimizations
 * 
 */

class Profiler {

    /**
     * Execution time of profiled functions
     * @var array 
     */
    static $times = array();
    
    /**
     * Profile a function
     * @param function $function
     * @param string $name
     */
    static function profile($function, $name) {
        
        $start = microtime(true);
        $value = $function();
        $end = microtime(true);
        
        self::$times[$name] = $end - $start; 
        
        return $value;
    }
    
    /**
     * Display execution time of all profiled functions
     */
    static function display($die = false) {
        
        foreach(self::$times as $name => $time) {
            
            echo $name . ": " . $time . "<br/>";
        }
        
        if($die) exit;
    }
}
