<?php

/*
 * @CODOLICENSE
 */


namespace CODOF\User;

class Logout {

    /**
     *
     * @var type PDO resource
     */
    protected $db;


    public function __construct($db) {
        
        $this->db = $db;
    }
    
    
}
