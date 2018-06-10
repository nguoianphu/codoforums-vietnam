<?php

class ftp {

    public $server = "127.0.0.1";
    public $con;

    function SetServer($server = "127.0.0.1") {

        $this->server = $server;
        return true;
    }

    function connect() {

        $this->con = ftp_connect($this->server);
        
        if($this->con===false){
            return false;
        }
        return true;
        
    }

    function login($username = "", $password = "") {

        if (@ftp_login($this->con, $username, $password)) {
            return true;
        } else {
            return false;
        }
    }

    function quit() {

        ftp_close($this->con);
    }

    function is_exists($file) {

        $res = ftp_size($this->con, $file);

        if ($res != -1) {
            return true;
        } else {
            return false;
        }
    }

    function chmod($file, $mode) {

        ftp_chmod($this->con, $mode, $file);
    }

}
