<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Booter;

/*
 *  
 * This class contains contains an array of class aliases that will be registered
 * when Codoforum is loaded. All aliases are lazy loaded with help of php 
 * autoloader for performance 
*/


class AliasLoader {
    

    //These are the aliases used by Codoforum
    protected $aliases = array(
        
        "Util" => "CODOF\Util",
        "Hook" => "CODOF\Hook",
        "Store" => "\CODOF\Store",
        "Block" => "\CODOF\Block",        
        "DB" => "Illuminate\Database\Capsule\Manager",
        "Schema" => "CODOF\Database\Schema",
        "User" => "\CODOF\User\User"

    );
    
    /**
     * Should the autoloader function be prepended instead of appended
     */
    const PREPEND = TRUE;
    
    /**
     * Should the autoloader throw error
     */
    const THROW_ERR = TRUE;
    
    
    public function register() {

        /**
         * Register the alias loader function to the PHP autloader
         */
        spl_autoload_register(array($this, 'alias_loader'), self::THROW_ERR, self::PREPEND);
    }
    
    /**
     * Adds an alias at runtime 
     * @param type $alias
     */
    public function alias_loader($alias) {

        if(isset($this->aliases[$alias])) {
            
            class_alias($this->aliases[$alias], $alias);
        }
    }
    
    /**
     * Adds an alias to a namespace
     *  
     * @param string $namespace
     * @param string $alias
     */
    public function add($namespace, $alias) {
        
        $this->aliases[$alias] = $namespace;
    }
    
    /**
     * Removes an alias to a namespace
     * 
     * @param type $alias
     */
    public function remove($alias) {
        
        unset($this->aliases[$alias]);
    }
}