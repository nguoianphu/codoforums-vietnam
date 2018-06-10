<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Router;

class Router {

    private static $instance = null;
    public static $testing = false;
    
    private function __construct() {}
    
    public static function get_instance() {
        
        if(self::$instance == null || self::$testing) {
            
            self::$instance = new \Klein\Klein();
        }
        
        return self::$instance;
    }
}
