<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\HB;

class Render {

    /**
     * Gets Handlebars template for required page
     * @param string $tpl
     * @return string
     */
    public static function get_template_contents($tpl, $isPlugin = false) {
        if($isPlugin != false) {

            $path = PLUGIN_DIR . $isPlugin . '/' . $tpl;
        }else {

            $path = CURR_THEME_PATH . 'templates/' . $tpl . '.html';

        if (!file_exists($path)) {

            $path = DEF_THEME_DIR . 'templates/' . $tpl . '.html';
        }
    }

        return file_get_contents($path);
    }

    /**
     * Get data for building DOM from Handlebars template
     * @param string $tpl
     * @return array
     */
    public static function get_template_data($tpl) {

        $i18ns = array("find topics tagged", "new", "new replies", "replies", "views", "posted", "read more", "recent by", "Edit", "Delete", "Mark as spam");

        switch ($tpl) {

            case 'forum/topics' :
                $i18ns[] = "new topic";
                break;
            case 'forum/category' :
                break;
            case 'forum/topic' :
                $i18ns[] = 'reply';

            case 'moderation/queue':
                $i18ns[] = 'Approve';
                $i18ns[] = 'Delete';
        }

        $trans = array();
        foreach ($i18ns as $i18n) {

            $trans[$i18n] = _t($i18n);
        }


        return array(
            "const" => self::get_required_constants(),
            "i18n" => $trans
        );
    }

    /**
     * Get required constants for javascript
     * @return array
     */
    public static function get_required_constants() {

        return array(
            "RURI" => RURI,
            "DURI" => DURI,
            "CAT_IMGS" => CAT_IMGS,
            "CAT_ICON_IMGS" => CAT_ICON_IMGS,
            //"DEF_AVATAR" => DEF_AVATAR,
            "CURR_THEME" => CURR_THEME,
            "DEF_THEME_PATH" => DEF_THEME_PATH
        );
    }


    /**
     * Generate HTML from Handlebars template
     * @param string $tpl
     * @param array $data
     */
    public static function tpl($tpl, $data, $isPlugin = false) {

        $raw = self::get_template_contents($tpl, $isPlugin);
        $hash = md5($raw);

        $cachedPath = ABSPATH . 'cache/HB/compiled/' . $hash . '.php';

        if (!file_exists($cachedPath)) {

            $contents = \LightnCandy::compile($raw, array(
                        'flags' => \LightnCandy::FLAG_ERROR_LOG | \LightnCandy::FLAG_STANDALONE | \LightnCandy::FLAG_HANDLEBARS,
                        "helpers" => array(
                            "const" => function($args) {
                                //single argument call
                                return constant($args[0]);
                            },
                            "i18n" => function($args) {

                                return _t($args[0]);
                            },
                            "hide" => function($args) {
                                return "";
                            }
                        )
            ));

            file_put_contents($cachedPath, $contents);
        }

        $renderer = include $cachedPath;
        return $renderer($data);
    }

}
