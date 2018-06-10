<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Access;

class Request {

    public static function valid($token) {

        if (!CSRF::valid($token)) {

            $css_files = array();
            $view = "access_denied";

            \CODOF\Smarty\Layout::load($view, $css_files);
            return false;
        }

        return true;
    }

    /**
     * Define a GET route for AJAX GET with token validation
     * @param string $route
     * @param \Closure $closure
     */
    public static function get($route, \Closure $closure, $getNewStuff = true) {

        if (!\CODOF\User\CurrentUser\CurrentUser::loggedIn()) {

            $getNewStuff = false; //not available for guests
        }

        dispatch_get($route, function() use ($closure, $getNewStuff) {

            Request::processReq($closure, $getNewStuff, func_get_args());
        });
    }

    /**
     * Define a POST route for AJAX POST with token validation
     * @param string $route
     * @param \Closure $closure
     */
    public static function post($route, \Closure $closure, $getNewStuff = true) {


        if (!\CODOF\User\CurrentUser\CurrentUser::loggedIn()) {

            $getNewStuff = false; //not available for guests
        }

        dispatch_post($route, function() use ($closure, $getNewStuff) {

            Request::processReq($closure, $getNewStuff, func_get_args());
        });
    }

    public static function processReq($closure, $getNewStuff, $args) {

        if (Request::valid($_REQUEST['_token'])) {

            $data = call_user_func_array($closure, $args);
            $newStuff = array();

            if ($getNewStuff) {

                //$newStuff = self::whatsNew();
            }

            if (!is_array($data)) {

                $data = array();
            }
            echo json_encode(array_merge($data, $newStuff));
        }
    }

    /**
     * Starts Limonade framework routing 
     */
    public static function start() {

        
        function server_error($errno, $errstr, $errfile = null, $errline = null) {

            $args = compact('errno', 'errstr', 'errfile', 'errline');

            var_dump(error_layout());
            var_dump($args);
        }

        run();
    }

}
