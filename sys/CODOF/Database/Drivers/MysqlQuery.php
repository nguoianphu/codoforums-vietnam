<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Database\Drivers;

class MysqlQuery extends Query{

   
    
    public function limit($limit, $offset = 0) {
        
        $this->query = $this->query . " LIMIT $limit OFFSET $offset";
        
        return $this;
    }
    
}
