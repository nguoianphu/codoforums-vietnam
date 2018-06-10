<?php

namespace CODOF;

require SYSPATH . 'Ext/b8/b8.php';

class SpamFilter {

    private static $b8 = false;
    private $use;

    public function __construct() {

        $ml_enabled = Util::get_opt('ml_spam_filter');


        $config_b8 = array(
            'storage' => 'mysqli'
        );


        $conf = get_codo_db_conf();
        $config_storage = array(
            'database' => $conf['database'],
            'table_name' => 'b8_wordlist',
            'host' => $conf['host'],
            'user' => $conf['username'],
            'pass' => $conf['password']
        );

        if ($conf['driver'] == 'mysql') {

            $this->use = true;
        }

        $this->use = ($ml_enabled == 'yes');


# Tell b8 to use the new-style HTML extractor
        $config_lexer = array(
            'old_get_html' => FALSE,
            'get_html' => TRUE
        );

# Tell the degenerator to use multibyte operations
# (needs PHP's mbstring module! If you don't have it, set 'multibyte'to FALSE)
        $config_degenerator = array(
            'multibyte' => TRUE
        );

        if (!self::$b8) {
            try {
                self::$b8 = new \b8($config_b8, $config_storage, $config_lexer, $config_degenerator);
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }

    public function spam($text) {

        if (!$this->use) {

            return false;
        }

        self::$b8->learn($text, \b8::SPAM);
    }

    public function ham($text) {

        if (!$this->use) {

            return true;
        }
        self::$b8->learn($text, \b8::HAM);
    }

    public function isSpam($text) {

        if (!$this->use) {

            return false;
        }

        $rating = self::$b8->classify($text);
        return $rating > 0.9;
    }

}
