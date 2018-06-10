<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\User;

class Ban {

    private $db;

    /**
     * Why was this user banned ?
     * @var string 
     */
    public $reason;

    /**
     * When will the ban be lifted
     * @var int
     */
    public $expires;

    /**
     * Whether banned by username/email/ip address
     * @var type 
     */
    public $type;

    /**
     * Associative array of a ban record used while inserting or
     * updating a ban record
     * @var type 
     */
    public $values;

    public function __construct($db) {

        $this->db = $db;
    }

    /**
     * 
     * Returns if any of the set of $ids provided exists in the table codo_bans
     * If it exists in that table, it means the user is banned
     * @param type $ids
     * @return type
     */
    public function is_banned($ids) {

        $len = count($ids);
        $binds = array();

        while ($len--) {

            $binds[] = '?';
        }

        $qry = 'SELECT ban_type,ban_reason,ban_expires FROM ' . PREFIX . 'codo_bans WHERE uid IN (' . implode(",", $binds) . ')';
        $obj = $this->db->prepare($qry);

        $obj->execute($ids);
        //assuming one result is obtained
        $res = $obj->fetch();

        if (!empty($res)) {


            $this->type = $res['ban_type'];
            $this->reason = $res['ban_reason'];
            $this->expires = $res['ban_expires'];

            return true;
        }

        return false;
    }

    /**
     * Adds a ban record
     */
    public function add_ban() {

        $keys = implode(",", array_keys($this->values));
        $values = ":" . implode(",:", array_keys($this->values));

        $qry = "INSERT INTO " . PREFIX . "codo_bans ($keys) VALUES($values)";
        $obj = $this->db->prepare($qry);

        $obj->execute($this->values);
    }

    /**
     * Updates ban record
     * @param type $id
     */
    public function update_ban($id) {

        $id = (int) $id;

        $qry = "UPDATE " . PREFIX . "codo_bans SET uid=:uid, ban_type=:ban_type,"
                . "ban_by=:ban_by,ban_on=:ban_on,ban_reason=:ban_reason,"
                . "ban_expires=:ban_expires WHERE id=$id";
        $obj = $this->db->prepare($qry);

        $obj->execute($this->values);
    }

    /**
     * Deletes ban record
     * @param type $id
     */
    public function remove_ban($id) {

        $id = (int) $id;
        $qry = "DELETE FROM " . PREFIX . "codo_bans WHERE id=$id";
        $this->db->query($qry);
    }

}
