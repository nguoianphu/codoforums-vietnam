<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Forum\Notification;

class Mail {

    public $sent;
    public $error;
    public $to;
    public $subject;
    public $message;
    
    public $curr;
    public $user;
    public $post;
    public $topic;
    
    /**
     *
     * @var \Ext\phpmailer\PHPMailer 
     */
    private $mailer;
    
    public function __construct($testing = false) {
        
        if(!$testing)
        $this->mailer = new \Ext\phpmailer\PHPMailer;
    }
    
    
    public function setHTML($textMessage) {
        
        $mail = $this->mailer;
        $mail->IsHTML(true);
        $mail->CharSet = "text/html; charset=UTF-8;";
        $mail->WordWrap = 60;
        $mail->AltBody  =  $textMessage;        
    }
    
    
    public function send_mail() {
       
        $mail = $this->mailer;

        $mail->AddAddress($this->to);
        $mail->Subject = $this->subject;
        $mail->Body = $this->message;
        $mail->CharSet = 'UTF-8';

        if (\CODOF\Util::get_opt('mail_type') == 'smtp') {
            $mail->IsSMTP(); // enable SMTP
        } else {
            $mail->IsMail();
        }

        $mail->SMTPDebug = 0;  // debugging: 1 = errors and messages, 2 = messages only

        if (\CODOF\Util::get_opt('smtp_protocol') != 'none') {
            $mail->SMTPAuth = true;  // authentication enabled
        }

        $mail->SMTPSecure = \CODOF\Util::get_opt('smtp_protocol'); // SSL TLS secure transfer enabled REQUIRED for Gmail
        $mail->Host = \CODOF\Util::get_opt('smtp_server');
        $mail->Port = \CODOF\Util::get_opt('smtp_port');
        $mail->Username = \CODOF\Util::get_opt('smtp_username');
        $mail->Password = \CODOF\Util::get_opt('smtp_password');
        $mail->SetFrom(\CODOF\Util::get_opt('admin_email'), \CODOF\Util::get_opt('site_title'));
        
        // nguoianphu - BCC to admin for tracking
        $mail->addBCC(\CODOF\Util::get_opt('admin_email'));

        if (!$mail->Send()) {

            $this->sent = false;
            $this->error = $mail->ErrorInfo;
            \CODOF\Util::log('Mail error: ' . $this->error);
        } else {

            $this->sent = true;
        }
    }

    public function replace_tokens($text) {

        preg_match_all("/\[(.*?)\]/", $text, $tkns);
        $tokens = $tkns[1];
        
        //we use str_replace which anyway replaces all occurences
        $ids = array_unique($tokens);

        foreach ($ids as $id) {

            $fields = explode(":", $id);

            switch ($fields[0]) {

                //user related
                case 'user':
                    $value = $this->user[$fields[1]];
                    break;

                //any config from codo_config table
                case 'option':
                    $value = \CODOF\Util::get_opt($fields[1]);
                    break;

                case 'this':
                    $value = $this->curr[$fields[1]];
                    break;

                case 'post':
                    $value = $this->post[$fields[1]];
                    break;
                
                default : $value = '';
            }
            $text = str_replace("[$id]", $value, $text);            
        }
        
        return $text;
    }
        
}
