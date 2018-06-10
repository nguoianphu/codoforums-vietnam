<?php

/*
 * @CODOLICENSE
 */

namespace CODOF;

class Plugin {

    private static $plugin;
    public static $data = array();
    private static $rids;

    public function __construct() {
        $this->db = \DB::getPDO();
    }

    public function init() {

        $me = User\User::get();

        if (!$me)
            session_destroy();

        $rids = $me->rids;
        self::$rids = $rids;

        $qry = 'SELECT plg_name,plg_type,plg_status,region,title,content,pages,visibility FROM ' . PREFIX . 'codo_plugins AS p'
                . ' LEFT JOIN ' . PREFIX . 'codo_blocks AS b ON p.plg_name=b.module '
                . ' LEFT JOIN ' . PREFIX . 'codo_block_roles AS r ON b.id=r.bid '
                . ' WHERE r.rid IN (' . implode(",", $rids) . ') OR r.rid IS NULL  ORDER BY b.weight';

        $result = $this->db->query($qry)->fetchAll();

        foreach ($result as $res) {

            $path = PLUGIN_DIR . $res['plg_name'] . '/' . $res['plg_name'] . '.php';

            if (file_exists($path) && $res['plg_status'] == 1) {

                $this->loadPlugin($res, $path);
            }

            self::$plugin[$res['plg_name']] = array("status" => $res['plg_status'], "block" => $res['region']);
        }

        $this->storeHtmlBlocks();
    }

    public function storeHtmlBlocks() {

        $qry = 'SELECT module,content,region,pages,visibility FROM ' . PREFIX . 'codo_blocks AS b '
                . ' LEFT JOIN ' . PREFIX . 'codo_block_roles AS r ON b.id=r.bid '
                . ' WHERE (r.rid IN (' . implode(",", self::$rids) . ') OR r.rid IS NULL) AND module="html"'
                . ' ORDER BY b.weight';

        $result = $this->db->query($qry)->fetchAll();

        foreach ($result as $res) {

            if ($res['content'] != '' && $this->canLoadIn($res['pages'], $res['visibility'])) {

                $this->storeContent($res['region'], $res['content']);
            }
        }
    }

    public function loadPlugin($plugin, $path) {

        if ($plugin['plg_type'] == 'plugin') {

            require $path;
        } else if ($this->canLoadIn($plugin['pages'], $plugin['visibility'])) {

            ob_start();
            require $path;
            $content = ob_get_clean();

            $this->storeContent($plugin['region'], $content);
        }
    }

    public static function storeContentByName($name, $content) {

        $region = self::$plugin[$name]['block'];

        if (!isset(self::$data[$region])) {

            self::$data[$region] = array();
        }

        self::$data[$region][] = $content;
    }

    public function storeContent($region, $content) {

        if (!isset(self::$data[$region])) {

            self::$data[$region] = array();
        }

        self::$data[$region][] = $content;
    }

    private function canLoadIn($pages_str, $visibility) {

        $visibleInCurrentPage = (int) $this->canLoadInPage($pages_str);

        return !($visibleInCurrentPage ^ (int) $visibility);
    }

    /**
     * 
     * checks if block can load in current page
     * @param type $pages_str
     */
    private function canLoadInPage($pages_str) {

        $pages = explode("\n", $pages_str);

        $curr_url = isset($_GET['u']) ? $_GET['u'] : '';

        $curr_page = trim($curr_url, "/");
        /*
         * user
         * user/edit
         * user/*
         */

        foreach ($pages as $page) {

            $tpage = trim($page, "/");

            if ($page == "/" && $curr_page == "") {

                return true;
            }
            if ($tpage == '')
                continue;

            $parts = explode("*", $tpage);
            $_page = $parts[0];

            return strpos($curr_page, $_page) !== FALSE;
        }

        return false;
    }

    /**
     * 1 -> active
     * Returns status of the plugin
     * @param type $plg_name
     * @return type int
     */
    public static function is_active($plg_name) {

        return self::$plugin[$plg_name]["status"];
    }

    public static function tpl($tpl) {

        \CODOF\Smarty\Layout::load('file:' . PLUGIN_DIR . $tpl);
    }

}
