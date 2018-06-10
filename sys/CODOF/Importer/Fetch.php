<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Importer;

class Fetch {

    public $cat;
    private $db;

    public function __construct($db) {
        
        $this->db = $db;
    }
    
    public function get_cat() {

        /*$cat = $this->cat;

        $qry = "SELECT ";

        $fields = "";

        foreach ($cat["get"] as $key => $value) {

            $fields .= "$value AS $key, ";
        }


        $qry .= rtrim($fields, ",");

        $qry .= " FROM ";

        $tables = "";

        foreach ($cat["from"] as $from) {

            $tables .= $from . ", ";
        }

        $qry .= rtrim($tables, ",");

        $joins = "";
        if (isset($cat['joins']) && !empty($cat['joins'])) {

            foreach ($cat['joins'] as $join_name => $vals) {

                $joins .= " " . $vals['type'] . " JOIN $join_name ON " . $vals['on'] . " ";
            }
        }
        
        $qry .= $joins;
        
        $qry .= " WHERE ";*/


        $qry = $this->cat;

        $res = $this->db->query($qry);
        return $res->fetchAll();
    }
    
    public function get_topics() {
        
        $qry = $this->topics_qry;

        $res = $this->db->query($qry);
        return $res->fetchAll();
        
    }

}
