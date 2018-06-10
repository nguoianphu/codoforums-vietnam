<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Constraints;

/**
 * 
 * Constraints defined for an user 
 * 
 * What is a constraint ?
 * A constraint is anything which restricts free change of data normally defined
 * by the user
 * 
 * Why a separate class ?
 * Reusability , etc etc 
 */
class User {

    /**
     * All errors are pushed into this array
     * @var type 
     */
    private $errors = array();

    /**
     * Return all trapped errors
     * @return type
     */
    public function get_errors() {

        return $this->errors;
    }

    /**
     * Constraints defined for password of a user
     * @param string $pass
     */
    public function password($pass) {

        $errors = array();
        $pass_len = strlen($pass);
        $min_len = \CODOF\Util::get_opt('register_pass_min');

        ///this is useful during hashing 
        if ($pass_len > 72) {
            $errors[] = _t("password cannot be greater than 72 characters!");
        }

        //i know this leads to hardcoded translation problem; but i am lazy ;)
        if ($pass_len < $min_len) {
            $errors[] = _t("password cannot be less than $min_len characters!");
        }
        
        $this->errors = array_merge($errors, $this->errors);
        
        if(empty($errors)) {
            
            \CODOF\Hook::call('on_password_ok');
            return TRUE; //passed
        }

        \CODOF\Hook::call('on_password_fail');        
        return FALSE; //Fail
    }

    /**
     * Constraints defined for username
     * @param type $username
     */
    public function username($username) {

        $username_len = strlen($username);
        $min_username_len = \CODOF\Util::get_opt('register_username_min');

        $errors = array();
        if ($username_len < $min_username_len) {
            $errors[] = _t("username cannot be less than $min_username_len characters!");
        }

       /* if (preg_match('/^[A-Za-z0-9_-]+$/', $username) === 0) {

            $errors[] = _t("username can have only letters digits and underscores");
        }*/

        if (\CODOF\User\User::usernameExists($username)) {
            $errors[] = _t("user already exists");
        }

        $this->errors = array_merge($errors, $this->errors);

        if(empty($errors)) {
            
            \CODOF\Hook::call('on_username_ok');
            return TRUE; //passed
        }

        \CODOF\Hook::call('on_username_fail');        
        return FALSE; //Fail
        
    }

    public function mail($mail) {

        $errors = array();        
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = _t("email address not formatted correctly");
        }
        
        if (\CODOF\User\User::mailExists($mail)) {
            $errors[] = _t("email address is already registered");
        }

        $this->errors = array_merge($errors, $this->errors);
        
        if(empty($errors)) {
            
            \CODOF\Hook::call('on_mail_ok');
            return TRUE; //passed
        }

        \CODOF\Hook::call('on_mail_fail');        
        return FALSE; //Fail
        
    }

}
