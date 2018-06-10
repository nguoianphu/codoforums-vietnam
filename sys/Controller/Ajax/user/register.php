<?php

/*
 * @CODOLICENSE
 */

namespace Controller\Ajax\user;

class register {

    public function __construct() {

        $this->db = \DB::getPDO();
    }

    public function usernameExists() {

        if (isset($_GET['username'])) {

            $response = array("exists" => false);

            $username = $_GET['username'];
            if ($_GET['username'] != '' && \CODOF\User\User::usernameExists($username)) {
                $response['exists'] = true;
            }

            echo json_encode($response);
        }
    }

    public function mailExists() {

        if (isset($_GET['mail'])) {

            $response = array("exists" => false);

            $mail = $_GET['mail'];
            if ($_GET['mail'] != '' && \CODOF\User\User::mailExists($mail)) {
                $response['exists'] = true;
            }

            echo json_encode($response);
        }
    }

    public function resend_mail() {

        $user = \CODOF\User\User::get();
        
        if ($user->loggedIn()) {
                        
            $details = $user->getInfo();
            $errors = array();
            
            $reg = new \CODOF\User\Register($this->db);
            
            $reg->add_signup_attempt($details);
            $reg->send_mail($details, $errors);
            
            if(empty($errors)) {
                
                echo 'success';
            }else{
                
                echo $errors[0];
            }
        } 
        
    }

}
