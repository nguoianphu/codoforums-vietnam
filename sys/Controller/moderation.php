<?php

/*
 * @CODOLICENSE
 */

/**
 * 
 * status -> 3 = Spam Filter 
 * status -> 4 = Normal moderation
 * 
 * 
 * If 3, on approve, we need to ask whether it was a spam.
 * 
 * If 4, on delete, we need to ask whether it was a spam.
 */

namespace Controller;

class moderation {

    public $css_files;
    public $js_files = array();
    protected $smarty;

    public function __construct() {

        $this->smarty = \CODOF\Smarty\Single::get_instance();
    }

    public function showTopicsQueue() {


        $mod = new \CODOF\Forum\Moderation(\DB::getPDO());
        $topics = $mod->getTopics();


        $this->smarty->assign('mod_queue', \CODOF\HB\Render::tpl('moderation/queue', array("topics" => $topics)
        ));

        $num_topics = $mod->getNumTopics();
        $num_replies = $mod->getNumReplies();
        
        if($num_topics === 0) {
            
            $this->smarty->assign('present', false);
        }else {
            
            $this->smarty->assign('present', true);            
        }
        
        $this->smarty->assign('num_topics', $num_topics ? $num_topics : '0 ');
        $this->smarty->assign('num_replies', $num_replies ? $num_replies : '0 ');

        $this->css_files = array('moderation');

        $this->smarty->assign('tab_option', 'topics');
        $this->smarty->assign('topic_head', 'codo_active_head_item');
        $this->smarty->assign('reply_head', '');

        $this->view = 'moderation/moderation';
        \CODOF\Store::set('sub_title', _t('Moderation queue'));
    }

    public function showRepliesQueue() {


        $mod = new \CODOF\Forum\Moderation(\DB::getPDO());
        $topics = $mod->getReplies();


        $this->smarty->assign('mod_queue', \CODOF\HB\Render::tpl('moderation/queue', array("topics" => $topics)
        ));

        $num_topics = $mod->getNumTopics();
        $num_replies = $mod->getNumReplies();

        if($num_replies === 0) {
            
            $this->smarty->assign('present', false);
        }else {
            
            $this->smarty->assign('present', true);            
        }
        
        $this->smarty->assign('num_topics', $num_topics ? $num_topics : '0 ');
        $this->smarty->assign('num_replies', $num_replies ? $num_replies : '0 ');

        $this->css_files = array('moderation');

        $this->smarty->assign('tab_option', 'replies');

        $this->smarty->assign('reply_head', 'codo_active_head_item');
        $this->smarty->assign('topic_head', '');
        
        $this->view = 'moderation/moderation';
        \CODOF\Store::set('sub_title', _t('Moderation queue'));
    }

}
