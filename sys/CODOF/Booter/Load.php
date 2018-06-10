<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Booter;

require 'Container.php';

class Load extends Container {

    public function __construct() {


        spl_autoload_register(array($this, 'loader'));
        $aliaser = new AliasLoader();
        $aliaser->register();
    }

    /**
     * 
     * Codoforum autoloader
     * @param type $class
     * @return type
     */
    public function loader($class) {
            if (0 === strpos($class, 'Smarty')) {
        return;
    }

        $className = explode('\\', $class);

        $class = array_pop($className);
        $namespace = implode("/", $className);

        if($namespace == 'JBBCode') {
            
            $namespace = 'Ext/' . $namespace;
        }

        if($class == 'LightnCandy') {
            
            $namespace = 'Ext/LightnCandy';
        }
        
        $file = ABSPATH . "sys/" . $namespace . "/" . $class . '.php';

        if (is_file($file)) {

            require $file;
        } else {
            
            echo 'Unable to require ' . $file;
        }
    }


    public function loadServiceProvider() {
        
        $sp = new ServiceProvider();
        $sp->register($this);
    }
    
}
