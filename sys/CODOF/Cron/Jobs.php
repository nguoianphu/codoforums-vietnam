<?php

/*
 * @CODOLICENSE
 */

/**
 * 
 * This contains all the core jobs that are responsible for 
 * maintenance, updates, indexing, etc
 * 
 * 
 */

namespace CODOF\Cron;

class Jobs {

    public function run_jobs() {

        $this->unban_users();
        $this->close_topics();
    }

    public function add_core_hooks() {

        \CODOF\Hook::add('on_cron_notify', array(new \CODOF\Forum\Notification\Notifier, 'dequeueNotify'));
        \CODOF\Hook::add('on_cron_daily_digest', array(new \CODOF\Forum\Notification\Digest\Digest, 'sendDailyDigest'));
        \CODOF\Hook::add('on_cron_weekly_digest', array(new \CODOF\Forum\Notification\Digest\Digest, 'sendWeeklyDigest'));
        \CODOF\Hook::add('on_cron_mail_notify_send', array(new \CODOF\Forum\Notification\MailQueue(), 'dequeue'));        
        \CODOF\Hook::add('on_cron_mail_notify_send', array(new \CODOF\Forum\Notification\MailQueue(), 'dequeue'));        
        \CODOF\Hook::add('on_cron_forum_update', array(new \CODOF\Forum\Forum(), 'update'));
        
    }

    //Unbans all usernames/emails/ips that have passed the time limit
    //for ban period 
    private function unban_users() {

        $qry = 'DELETE FROM ' . PREFIX . 'codo_bans WHERE ban_expires<' . time() . ' AND ban_expires<>0';
        $this->db->query($qry);
    }
    
    /**
     * Closes any topics which based on their auto close time
     */
    private function close_topics() {
        
        
    }

}
