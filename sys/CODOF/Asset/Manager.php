<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Asset;

class Manager {

    /**
     * Order in which assets are loaded
     * @var int 
     */
    private static $order = 0;

//------------------------------------------------------------------------------

    /**
     * 
     * $name, $data, $type = 'file', $order = false, $position = 'head'
     * @param string $asset     * 
     * @param array $options
     * @return array
     */
    public function add($asset, $options = false) {

        $assetArr = array();
        
        if(!$options || !isset($options['type'])) {
            
            $options['type'] = 'file';
        }
        
        if($options['type'] == 'inline' && isset($options['data'])) {
            
            $assetArr['name'] = $asset;
            $assetArr['data'] = $options['data'];
        }
        else if ($options['type'] == 'file' || $options['type'] == 'defer' || $options['type'] == 'remote') {

            $assetArr['name'] = isset($options['name']) ? $options['name'] : $asset;
            $assetArr['data'] = $asset;
            
        }else return false;
        
        $def_asset = array(
            "name" => null, //required
            "data" => null, //required
            "type" => $options['type'], //file or inline or defer
            "order" => false,
            "position" => 'head' //head or body
        );

        $_asset = array_merge($def_asset, $assetArr);

        if (!$_asset['order']) {

            self::$order++;
            $_asset['order'] = self::$order;
        }
        
        return $_asset;
    }

    public function order_cmp($a, $b) {

        $order = array();
        if (is_object($a)) {

            $order['a'] = $a->order;
        } else {
            $order['a'] = $a['order'];
        }

        if (is_object($b)) {

            $order['b'] = $b->order;
        } else {
            $order['b'] = $b['order'];
        }


        if ($order['a'] == $order['b']) {
            return 0;
        }
        return ($order['a'] < $order['b']) ? -1 : 1;
    }

}
