<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\User;

class RememberMe extends \CODOF\Cookie{


    public function __construct($db) {
        $this->db = $db;
    }
    
    public function save_cookie($username) {
        
        $str = uniqid() . $username;
        $token = sha1($str);
        
        $cookie = $token . "|" . $username;
        
        \CODOF\Cookie::Set("codo_remember", $cookie);
        
        $qry = "UPDATE codo_users SET token = :token WHERE username = :username";
        $obj = $this->db->prepare($qry);
        $obj->execute(array("token" => $token, "username" => $username));
    }
    
    /**
     * 
     * checks if the user cookie token matches the token stored in db
     * @return user id if has cookie else false 
     */
    
    public function has_cookie() {
        
        $cookie = \CODOF\Cookie::Get('codo_remember', false);
        
        if($cookie) {
            
            list($token, $username) = explode("|", $cookie);
            
            $qry = "SELECT id, token FROM codo_users WHERE username = :username";
            $obj = $this->db->prepare($qry);
            $obj->execute(array("username" => $username));
            
            $result = $obj->fetchObject();
            
            if($result) {
                
                $db_token = $result->token;
                return ($db_token == $token) ? $result->id : false;
            }
        }
        
        return false;
    }
    
    public function destroy_cookie() {
        
        \CODOF\Cookie::Delete('codo_remember');
        
    }
}
