<?php

namespace CODOF\Forum\Notification;

class MailQueue {

    protected $db;
    
    public function __construct() {
        
        $this->db = \DB::getPDO();
    }


    /**
     * Send first 10 emails in codo_mails queue
     */
    public function dequeue() {

        $qry = 'SELECT * FROM ' . PREFIX . 'codo_mail_queue WHERE mail_status=0 LIMIT 10 OFFSET 0';
        $obj = $this->db->query($qry);

        $mails = $obj->fetchAll();
        if (!count($mails)) {
            return;
        }

        $ids = array();
        foreach ($mails as $mail) {

            $mailer = new \CODOF\Forum\Notification\Mail();

            $mailer->to = $mail['to_address'];
            $mailer->subject = $mail['mail_subject'];
            $mailer->message = $mail['body'];

            $mailer->send_mail();

            $ids[] = $mail['id'];
        }

        $_ids = implode(",", $ids);
        $qry = 'DELETE FROM ' . PREFIX . 'codo_mail_queue WHERE id IN (' . $_ids . ')';
        $this->db->query($qry);
    }

}
