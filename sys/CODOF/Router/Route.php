<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Router;

class Route {

    protected static $params;
    protected $path;
    protected static $onned = false;

    public function __construct() {

        $path = trim($_GET['u'], '/');
        $this->path = $path;

        self::$params = explode('/', $path);
    }

    public function on($path, $callback) {

        $path = trim($_GET['u'], '/');
        
        if (!self::$onned) {

            if (($pos = strpos($this->path, $path)) !== FALSE) {

                $callback();
            }
            
            self::$onned = true;
        }
    }
    
    public static function params() {
        
        return self::$params;
    }

}
