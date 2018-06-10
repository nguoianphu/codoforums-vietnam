<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\User;

class Login {

    //put your code here

    public $username;
    public $password;
    private $db;

    public function __construct($db = false) {
        $this->db = $db;
    }

    /**
     * 
     * Checks if username and password is not empty
     * Checks if user exists and password matches
     * Logs the user in
     * remember_me() is called
     * 
     * @return type
     */
    public function process_login() {

        //don't neeed much validation since we use prepared queries    
        $username = strip_tags(trim($this->username));

        $hasher = new \CODOF\Pass(8, false);
        $password = $this->password;

        $errors = array();

        if (strlen($username) == 0) {
            $errors[]["msg"] = _t("username field cannot be left empty");
        }

        if (strlen($password) == 0) {
            $errors[]["msg"] = _t("password field cannot be left empty");
        }

        if (strlen($password) < 72 && empty($errors)) {

            $user = User::getByUsername($username);

            $ip = $_SERVER['REMOTE_ADDR']; //cannot be trusted at all ;)
            $ban = new Ban($this->db);

            if ($user && $ban->is_banned(array($ip, $username, $user->mail))) {

                $ban_len = '';

                if ($ban->expires > 0) {

                    $ban_len = _t("until ") . date('d-m-Y h:m:s', $ban->expires);
                }

                return json_encode(array("msg" => _t("You have been banned ") . $ban_len));
            }

            if ($user && $hasher->CheckPassword($password, $user->pass)) {

                User::login($user->id);
                $user = User::get();
                $user->rememberMe();
                setcookie("user_id", $user->id);
                return json_encode(array("msg" => "success", "uid" => $user->id, "rid" => $user->rid, "role" => User::getRoleName($user->rid)));
            } else {

                \CODOF\Log::info('failed login attempt by ' . $username . 'wrong username/password');
                return json_encode(array("msg" => _t("Wrong username or password")));
            }
        } else {
            return json_encode($errors);
        }
    }
}
