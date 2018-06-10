<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Asset;

class Collection{

    /**
     * Where will the collection of assets be dumped in head or body ?
     * @var string
     */
    public $position = 'head';

    /**
     * Name of this collection
     * @var string
     */
    public $name;
    
    /**
     * 
     */
    public $type = 'file';
    
    /**
     * Global Order of all collection
     * @var int
     */
    private static $sys_order;
    
    /**
     * Order of this collection
     * @var int
     */
    public $order;
    
    /**
     * Stores css files of collection
     * @var array
     */
    public $css = array();
    
    /**
     * Stores js files of collection
     * @var array
     */
    public $js = array();
    
    /**
     * Manager object to use methods that manage assets
     * @var object
     */
    private $manager;

    /**
     * css rel paths will be converted to absolute using $compiledPath
     * @var string 
     */
    public $prependURL = '../../sites/default/themes/default/';
    
//------------------------------------------------------------------------------
    
    public function __construct($name, $order = false) {
        
        $this->name = $name;
        
        if(!$order) {
            
            $order = self::$sys_order++;
        }
        
        $this->order = $order;
        $this->manager = new Manager;
    }
    
    public function addCSS($asset, $options = false) {
        
        $this->css[] = $this->manager->add($asset, $options);

        return $this;
    }
    
    public function addJS($asset, $options = false) {

        $this->js[] = $this->manager->add($asset, $options);
        
        return $this;                
    }
    
}