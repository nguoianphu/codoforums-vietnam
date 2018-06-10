<?php

/*
 * @CODOLICENSE
 */

namespace CODOF;

defined('IN_CODOF') or die();

class Hook {

    private static $hooks = array();

    /*
     * Returns all available hooks
     */

    public static function get_hooks() {
        return self::$hooks;
    }

    /*
     * Adds $function to the given $hook with arguments $args
     */

    public static function add($hook, $function, $args = array(), $priority = 10) {

        $id = self::gen_id($function);
        self::$hooks[$hook][$priority][$id] = array('function' => $function, 'args' => (array) $args);
    }

    /*
     * Calls all actions for the given $hook with optional argument $args
     */

    public static function call($hook, $args = array()) {

        if (!isset(self::$hooks[$hook])) {

            //No actions hooked
            return $args;
        }

        /* $args = array();

          if (!is_array($arg) && !is_object($arg)) {

          $args = $arg;
          } else {

          $args = $arg;
          } */

        //unsorted actions for $hook
        $actions = self::$hooks[$hook];
        //sorted actions for $hook
        ksort($actions);

        $return_arr = array();
        
        do {
            foreach ((array) current($actions) as $action) {

                if (!is_null($action['function'])) {

                    $return_value = call_user_func_array($action['function'], array($args, $action['args']));

                    if ($return_value != null) {

                        $return_arr[] = $return_value;
                    }
                }
            }
        } while (next($actions) !== false);

        return $return_arr;
    }

    /*
     * Generates a unique id for reference for the function or object or class passed
     */

    public static function gen_id($function) {

        if (is_string($function)) {

            //simple function string passed
            return $function;
        }

        //looks like a closure
        if (is_object($function)) {

            return spl_object_hash($function);
        }

        //can be object method or static method
        if (is_array($function)) {

            if (is_object($function[0])) {

                //object method
                return spl_object_hash($function[0]) . $function[1];
            } else {

                //static method
                return $function[0] . $function[1]; //converts to string
            }
        }
    }

}
