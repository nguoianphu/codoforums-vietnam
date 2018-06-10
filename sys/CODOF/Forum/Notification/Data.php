<?php

namespace CODOF\Forum\Notification;

class Data {

    /**
     * Category id
     * @var int 
     */
    public $cid;

    /**
     * Topic id
     * @var int
     */
    public $tid = 0;

    /**
     * User ids
     * @var array 
     */
    public $mentions = array();

    public static function ofCategory($cid, $uids) {

        $data = new Data();
        $data->cid = $cid;
        $data->mentions = $uids;
        
        return $data;
    }

    public static function ofTopic($cid, $tid, $uids) {

        $data = new Data();
        $data->cid = $cid;
        $data->tid = $tid;
        $data->mentions = $uids;
        
        return $data;
    }

}
