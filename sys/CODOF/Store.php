<?php

/*
 * @CODOLICENSE
 */

namespace CODOF;

/*
 * 
 * This class is used to set constants that will be used in the page 
 * These constants are replacements for define() and global variables 
 * These are not fixed constants like the one's in sites/default/constants.php
 * These are static variables so they can be accessed and modified from anywhere
 *   
 */

class Store {

    /**
     * This will hold all constants in an indexed based array
     * @var type array
     */
    protected static $const;

    /**
     * This will be saved in an array which can be fetched later by self::get
     * @param string $index
     * @param mixed $value
     */
    public static function set($index, $value) {

        self::$const[$index] = $value;
    }

    /**
     * Used to fetch stored constants
     * @param string $index
     * @return string
     */
    public static function get($index, $default = false) {

        return (isset(self::$const[$index])) ? self::$const[$index] : $default;
    }

    /**
     * Checks if item exists
     * @param string $index
     * @return boolean
     */
    public static function has($index) {

        return (isset(self::$const[$index]));
    }

}
