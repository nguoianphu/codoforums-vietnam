<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Smarty;

// load Smarty library
require ABSPATH . 'sys/Ext/Smarty/Smarty.class.php';

class Single {

    private static $smarty = null;
    private static $instance = null;

    private function __construct($path) {

        self::$smarty = new \Smarty();
        $this->parent = self::$smarty->parent;
        $this->load($path);
    }

    public static function get_instance($path = CURR_THEME_PATH, $force = false) {

        if (!self::$instance || $force) {
            self::$instance = new self($path);
        }
        return self::$instance;
    }

    public function load($path) {

        $dir = ABSPATH . 'cache/smarty/';

        
        self::$smarty->setTemplateDir(array(
            "current" => $path . 'templates',            
            "default" => THEME_DIR . 'default/templates'
        ));
        
        self::$smarty->setCompileDir($dir . 'templates_c/');
        self::$smarty->setConfigDir($path . 'configs/');
        self::$smarty->setCacheDir($dir . 'cache/');

        //self::$smarty->caching = 1;
        self::$smarty->addPluginsDir(SYSPATH . 'CODOF/Smarty/plugins');
        self::$smarty->debugging = FALSE;
        //$this->caching = \Smarty::CACHING_LIFETIME_CURRENT;
    }

    public function assign($var, $value = false) {

        if (!$value) {

            self::$smarty->assign($var);
        } else {

            self::$smarty->assign($var, $value);
        }
    }

    public function display($filename) {
        self::$smarty->display($filename);
    }

    public function fetch($filename) {
        return self::$smarty->fetch($filename);
    }

    public function getTemplateDir() {

        return self::$smarty->getTemplateDir();
    }

    public function addTemplateDir($path) {

        self::$smarty->addTemplateDir($path);
    }

    public function createTemplate($file, $smarty) {

        return self::$smarty->createTemplate($file, $smarty);
    }

}
