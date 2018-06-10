<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Database;

class Schema {
    
    static $schemaConnection;
    
    public static function storeSchemaConnection($capsule) {
        
        self::$schemaConnection = $capsule->schema();
    }
    
    public static function __callStatic($name, $arguments) {
        
        call_user_func_array(array(self::$schemaConnection, $name), $arguments);
    }
}
