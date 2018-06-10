<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Importer\Drivers;


abstract class Driver {

    /**
     * No. of rows to process set by the user
     * @var int 
     */
    public $max_rows;
    
    /**
     *
     * @var PDO 
     */
    protected $db;
    
    public function __construct(\PDO $db) {

        $this->db = $db;
    }

    /**
     * Table prefix 
     * @var string 
     */    
    public function set_prefix($prefix) {
        
        define('DBPRE', $prefix);
    }
    

    /**
     * Check if a table exists.
     *
     * @param string $table Table to search for.
     * @return bool TRUE if table exists, FALSE if no table found.
     */
    public function tableExists($table) {

        // Try a select statement against the table
        // Run it in try/catch in case PDO is in ERRMODE_EXCEPTION.
        try {
            $result = $this->db->query("SELECT 1 FROM $table LIMIT 1");
        } catch (\Exception $e) {
            // We got an exception == table not found
            return FALSE;
        }

        // Result is either boolean FALSE (no table found) or PDOStatement Object (table found)
        return $result !== FALSE;
    }
   
}