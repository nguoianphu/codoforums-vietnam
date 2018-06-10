<?php

/*
 * @CODOLICENSE
 */

namespace CODOF;

/**
 * 
 * Class used to define different regions of the template which can
 * be later used by plugins
 */
class Block {

    /**
     * Instance of smarty
     * @var type 
     */
    private $smarty;

    /*
     * Array of all visible blocks in current template
     */
    public static $blocks;

    /**
     * Array of all visible blocks defined by plugins
     * @var type 
     */
    private static $plg_blocks;
    

    
    public static $plugin_contents;
    /*
     * Adds $function to the given $block with arguments $args
     */
    
    public static function add($block, $function, $args = array(), $priority = 10) {
       
        $id = Hook::gen_id($function);
        self::$hooks[$block][$priority][$id] = array('function' => $function, 'args' => (array) $args);
    }
    
    public function default_layout() {

        $default_blocks = array('head', 'body_start', 'footer', 'body_end',
            'profile_view_start', 'profile_view_before', 'profile_view_end', 'profile_view_after');

        self::$blocks = $this->set_def_block_values($default_blocks);
    }

    public static function assign_blocks() {

        $smarty = Smarty\Single::get_instance();

        $smarty->assign('block', self::$blocks);
    }

    private function set_def_block_values($blocks) {

        $b = array();

        foreach ($blocks as $block) {

            $b[$block] = ''; //no output by default 
        }

        return $b;
    }
    
    public static function render($plg_name, $content) {
        
        
        Plugin::storeContentByName($plg_name, $content);
    }

    public static function renderView($plg_name, $file, $data) {
        
        $content = \CODOF\HB\Render::tpl($file, $data, $plg_name);
        Plugin::storeContentByName($plg_name, $content);
    }
    
}
