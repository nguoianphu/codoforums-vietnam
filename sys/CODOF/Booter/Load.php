<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Booter;

require 'Container.php';

class Load extends Container
{

    public function __construct()
    {


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
    public function loader($class)
    {

        if (0 === strpos($class, 'Smarty') ||  0 === strpos($class, 'LS')) {
            return;
        }

        $className = explode('\\', $class);

        $class = array_pop($className);
        $namespace = implode("/", $className);

        if ($namespace == 'JBBCode') {

            $namespace = 'Ext/' . $namespace;
        }

        /*if ($namespace == 'Facebook') {

            $namespace = 'Ext/hybridauth/Hybrid/Providers/';
        }*/


        if(strpos($namespace, 'Hybridauth') !== FALSE) {

            $folderName = str_replace("Hybridauth/", "", $namespace);
            $folderName = str_replace("Hybridauth", "", $folderName);

            $namespace = 'Ext/hybridauth/src/' . $folderName;
        }


        if ($class == 'LightnCandy') {

            $namespace = 'Ext/LightnCandy';
            $class = 'loader';
        }

        $file = ABSPATH . "sys/" . $namespace . "/" . $class . '.php';

        if (is_file($file)) {
            require_once $file;
        } else {
            echo 'Unable to require ' . $file;
        }
    }


    public function loadServiceProvider()
    {

        $sp = new ServiceProvider();
        $sp->register($this);
    }

}
