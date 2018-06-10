<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\User;

class Register {

    //put your code here


    public $username;
    public $name;
    public $password;
    public $mail;
    public $avatar = '';
    public $oauth_id = 0;
    public $user_status = 0; //pending , 1=approved
    public $rid = ROLE_USER; //by default a approved user
    public $no_posts = 0;
    public $userid;
    private $db;

    public function __construct($storage) {

        //set default password as a random value
        $this->password = time() . uniqid() . rand(1000, 5000);
        $this->db = $storage;
    }

    public function register_user() {

        $username = $this->username;
        $name = $this->name == null ? $this->username : $this->name;
        $password = $this->password;
        $mail = $this->mail;
        $errors = array();

        $hasher = new \CODOF\Pass(8, false);
        $hash = $hasher->HashPassword($password);

        if (strlen($hash) >= 20) {

            $fields = array("username" => $username, "name" => $name, "pass" => $hash,
                "mail" => $mail, "created" => time(), "last_access" => time(),
                "user_status" => $this->user_status, "avatar" => $this->avatar, "no_posts" => $this->no_posts, "oauth_id" => $this->oauth_id);

            $qry = 'INSERT INTO codo_users (username, name, pass, mail, created, last_access, user_status, avatar, no_posts, oauth_id) '
                    . 'VALUES(:username, :name, :pass, :mail, :created, :last_access, :user_status, :avatar, :no_posts, :oauth_id)';

            $obj = $this->db->prepare($qry);
            if (!$obj->execute($fields)) {

                \CODOF\Log::error("Could not register user! \nError:\n " . print_r($obj->errorInfo(), true) . "  \nData:\n" . print_r($fields, true));
                $errors[] = "Could not register user";
            } else {

                $this->userid = $this->db->lastInsertId('id');
                \DB::table(PREFIX . 'codo_user_roles')
                        ->insert(array(
                            'uid' => $this->userid,
                            'rid' => $this->rid,
                            'is_primary' => 1
                ));
                if ($this->user_status == 0) {

                    $this->add_signup_attempt($fields);
                    $this->send_mail($fields, $errors);
                }

                //TODO: CurrentUser -> store user
                //dont know the security implications when $fields is passed with hook
                \CODOF\Hook::call('on_user_registered');
            }
        }

        return $errors;
    }

    /**
     * Logins the newly registered user
     */
    public function login() {

        $_SESSION[UID . 'USER']['id'] = $this->userid;
    }

    /**
     * adds an record in codo_signups with a unique token and username
     * 
     * @param type $user array of user info from User->get()
     */
    public function add_signup_attempt($user) {

        $this->token = md5($user['mail'] . time());

        $qry = "INSERT INTO " . PREFIX . "codo_signups (username, token) VALUES(:name,:token)";
        $stmt = $this->db->prepare($qry);

        $stmt->execute(array(":name" => $user['username'], ":token" => $this->token));
    }

    /**
     * 
     * Sends an email for confirming the user . 
     * You must call add_signup_attempt() before calling this method
     * 
     * @param type $fields array of user info from User->get()
     * @param type $errors
     */
    public function send_mail($fields, &$errors) {

        $mail = new \CODOF\Forum\Notification\Mail();

        $body = \CODOF\Util::get_opt('await_approval_message');
        $sub = \CODOF\Util::get_opt('await_approval_subject');

        $confirm_url = RURI . "user/confirm" . "&user=" . $fields['username'] . "&token=" . $this->token;
        $confirm_page = RURI . "user/confirm";

        $mail->curr = array(
            "token" => $this->token,
            "confirm_url" => $confirm_url,
            "confirm_page" => $confirm_page
        );

        $mail->user = $fields;

        $message = $mail->replace_tokens($body);
        $subject = $mail->replace_tokens($sub);

        $to = $fields['mail'];

        $mail->to = $to;
        $mail->subject = $subject;
        $mail->message = $message;

        $mail->send_mail();

        if (!$mail->sent) {

            $errors[] = $mail->error;
        }
    }

    /**
     * 
     * Get different possible errors before registering an user
     * @return Array errors
     */
    public function get_errors() {

        $constraints = new \CODOF\Constraints\User;
        $constraints->username($this->username);
        $constraints->password($this->password);
        $constraints->mail($this->mail);

        $errors = $constraints->get_errors();

        if (\CODOF\Util::get_opt('captcha') == "enabled") {

            require_once ABSPATH . 'sys/Ext/recaptcha/recaptchalib.php';
            $privatekey = \CODOF\Util::get_opt("captcha_private_key");

// your secret key
            $secret = $privatekey;

// empty response
            $response = null;

// check secret key
            $reCaptcha = new \ReCaptcha($secret);


            if ($_POST["g-recaptcha-response"]) {
                $response = $reCaptcha->verifyResponse(
                        $_SERVER["REMOTE_ADDR"], $_POST["g-recaptcha-response"]
                );
            }

            if (!($response != null && $response->success)) {
                $errors[] = _t("capcha entered was wrong");
            }
        }

        return $errors;
    }

}
