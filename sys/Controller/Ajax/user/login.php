<?php

/*
 * @CODOLICENSE
 */

namespace Controller\Ajax\user;

class login {

    public function __construct() {
        $this->db = \DB::getPDO();
    }

    public function dologin() {

        if (isset($_GET['username']) && isset($_GET['password'])) {

            $login = new \CODOF\User\Login($this->db);

            $login->username = $_GET['username'];
            $login->password = $_GET['password'];
            echo $login->process_login();
        }
    }


    public function reset_pass() {

        $token = $_POST['reset_token'];

        $pass = $_POST['pass'];

        $constraints = new \CODOF\Constraints\User;
        $constraints->password($pass);
        $errors = $constraints->get_errors();

        if (empty($errors)) {

            $username = \DB::table(PREFIX . 'codo_users')
                    ->where('token', $token)
                    ->pluck('username');


            if ($username != null) {

                $parts = explode("&", $token);
                $expiry = $parts[1];

                if ($expiry > time()) {

                    $user = \CODOF\User\User::getByUsername($username);
                    if ($user) {

                        $user->updatePassword($pass);

                        \DB::table(PREFIX . 'codo_users')
                                ->where('token', $token)
                                ->update(array('token' => null));
                    }
                } else {

                    $errors[] = _t("Password reset token has expired");
                }
            } else {

                $errors[] = _t("Incorrect token");
            }
        }

        if (!empty($errors)) {
            $resp = array("status" => "fail", "msg" => $errors);
        } else {

            $resp = array("status" => "success", "msg" => _t("Password changed successfully...Redirecting to login page"));
        }
        echo json_encode($resp);
    }

    public function req_pass() {


        $errors = array();

        $token = uniqid() . '&' . (time() + 3600);
        $mail = new \CODOF\Forum\Notification\Mail();

        //update the user's password with the generated password
        $user = \CODOF\User\User::getByMailOrUsername($_GET['ident'], $_GET['ident']);

        $gen = false;
        if (!$user) {

            $errors[] = _t("User does not exist with the given username/mail");
        } else {


            $old_token = $user->token;

            if ($old_token != null && strpos($old_token, "&") === TRUE) {

                $parts = explode("&", $old_token);
                $expiry = (int) $parts[1];

                if ($expiry > time()) {

                    $gen = true;
                }
            } else {

                $gen = true;
            }
        }

        if (empty($errors) && $gen) {

            \DB::table(PREFIX . 'codo_users')
                    ->where('id', $user->id)
                    ->update(array('token' => $token));

            $body = \CODOF\Util::get_opt('password_reset_message');
            $sub = \CODOF\Util::get_opt('password_reset_subject');


            $mail->user = array(
                "token" => $token,
                "link" => RURI . 'user/reset'
            );

            $message = $mail->replace_tokens($body);
            $subject = $mail->replace_tokens($sub);


            $mail->to = $user->mail;
            $mail->subject = $subject;
            $mail->message = $message;

            $mail->send_mail();

            if (!$mail->sent) {

                $errors[] = $mail->error;
            }
        }
        $resp = array("status" => "success", "msg" => _t("E-mail sent successfully"));
        if (!empty($errors)) {

            $resp = array("status" => "fail", "msg" => $errors);
        }

        echo json_encode($resp);
    }

}
