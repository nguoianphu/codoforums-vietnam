<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Booter\IoC;


class IoC {
    
    /**
     * Container Class that includes class loaders
     * @var type 
     */
    protected static $container;


    protected static function getComponentName() {
        
        echo 'not implemented getComponentName in IoC';
    }
    
    
    protected static function getInstance($name) {

        return static::$container[$name];
    }

    
    public static function setIoCContainer($container) {
        
        static::$container = $container;
    }
    

    /**
     * 
     * @param type $method
     * @param type $args
     */
    public static function __callStatic($method, $args) {
        
        $instance = static::getInstance(static::getComponentName());
        
        return call_user_func_array(array($instance, $method), $args);
    }
}
