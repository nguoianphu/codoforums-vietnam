<?php

/*
 * @CODOLICENSE
 */

/**
 * Description of CSRF
 *
 * @author silva
 */

namespace CODOF\Access;

class CSRF {

    public static function get_token() {

        if(isset($_SESSION[SECRET . '_csrf'])) {
            
            return $_SESSION[SECRET . '_csrf'];
        }
        $token = md5(uniqid(rand(), true));
        //base64_encode(openssl_random_pseudo_bytes(32)); 
        //TODO: check if openssl is present
        
        $_SESSION[SECRET . '_csrf'] = $token;

        return $token;
    }

    public static function valid($token) {

        if (!isset($_SESSION[SECRET . '_csrf'])) {

            exit('CSRF token not yet generated');
        }

        $sess_token = $_SESSION[SECRET . '_csrf'];

        return $sess_token == $token;
    }

}
