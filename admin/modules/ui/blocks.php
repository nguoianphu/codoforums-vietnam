<?php

/*
 * @CODOLICENSE
 */
$smarty = \CODOF\Smarty\Single::get_instance();
$smarty->assign('msg', "");

if (!function_exists('glob_recursive')) {

// Does not support flag GLOB_BRACE        
    function glob_recursive($pattern, $flags = 0) {

        $matched_files = glob($pattern, $flags);

        $matched_dirs = glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT);
        $dirs = $matched_dirs ? $matched_dirs : array();
        $files = $matched_files ? $matched_files : array();
        foreach ($dirs as $dir) {
            $files = array_merge($files, glob_recursive($dir . '/' . basename($pattern), $flags));
        }
        return $files;
    }

}

class A_Block {

    public $db;
    public $theme = null;

    function __construct() {
        $this->db = \DB::getPDO();
    }

    /*
     * 
     * get content of all tpl files in a given theme dir.
     */

    function get_tpl_contents($theme = 'default') {

        require DATA_PATH . 'themes/' . $theme . '/info.php';

        $pfiles = array();
        if (isset($info['parent_theme'])) {

            $pfiles = glob_recursive(DATA_PATH . 'themes/' . $info['parent_theme'] . '/*.tpl');
        }

        $cfiles = glob_recursive(DATA_PATH . 'themes/' . $theme . '/*.tpl');
        $files = array_merge($pfiles, $cfiles);
        $tcontent = "";

        foreach ($files as $file) {
            $tcontent.= file_get_contents($file);
        }

        return $tcontent;
    }

    function get_current_theme() {

        if ($this->theme == null) {

            CODOF\Util::get_config($this->db);
            $theme = CODOF\Util::get_opt('theme');
            $this->theme = $theme;
        }

        return $this->theme;
    }

    function get_all_blocks($theme = null) {



        if ($theme == null) {

            $theme = $this->get_current_theme();
        }


        $tcontent = $this->get_tpl_contents($theme);
        $matches = array();
        preg_match_all('/{"([a-z_A-Z0-9]*)"\|load_block}/', $tcontent, $matches);
// $blocks=  array_merge($blocks,$matches); 
        $e = $matches[1];
        return (array_unique($e));
    }

    function list_blocks() {


        $query = "SELECT * FROM " . PREFIX . "codo_blocks WHERE theme='$this->theme'";
        $res = $this->db->query($query);
        $r = $res->fetchAll();
        return $r;
    }

    function save_blocks() {

        if (isset($_POST['test_post'])) {
            var_dump($_POST);

            foreach ($_POST as $key => $val) {

                $count = 0;
                $key = str_replace('bid_', '', $key, $count);
                $weight = (int) $_POST['bweight_' . $key];

                if ($count > 0) {

                    $key = (int) $key;
                    $query = "UPDATE " . PREFIX . "codo_blocks SET region=:region,weight=:weight WHERE id=$key AND theme=:theme";
                    $prep = $this->db->prepare($query);
                    $prep->execute(array(':region' => $val, ':theme' => $this->theme, ':weight' => $weight));
                }
            }

            header("Location: index.php?page=ui/blocks");
            exit();
        }
    }

    function get_block_type_plugins() {


        $query = "SELECT * FROM " . PREFIX . "codo_plugins WHERE plg_type='block'";
        $res = $this->db->query($query);
        $Tplugins = $res->fetchAll();

        $plugins = array();

        foreach ($Tplugins as $Tplugin) {



            $plugins[$Tplugin['plg_name']] = $Tplugin['plg_name'];
        }

        return $plugins;
    }

    function get_roles($bid = null) {


        if ($bid == null) {

            $query = "SELECT role.rname, role.rid
                FROM  " . PREFIX . "codo_roles AS role";
        } else {

            $bid = (int) $bid;
            $query = "SELECT role.rname, role.rid, block_role.bid "
                    . "FROM codo_roles AS role "
                    . "LEFT JOIN codo_block_roles AS block_role ON role.rid = block_role.rid "
                    . "AND block_role.bid =$bid";
        }

        $res = $this->db->query($query);

        return $res->fetchAll();
    }

    function add_block() {
        
    }

}

$B = new A_Block();

/*
 * action
 *      --add block
 *      -- delete block
 *      --  save blockS
 * 
 * 
 * add
 *   - new
 *   
 * 
 */

if (isset($_GET['action'])) {




    if ($_GET['action'] == 'add') {

        if (isset($_POST['mode']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {

            $blk_name = $_POST['blk_name']; //title
            $blk_region = $_POST['region'];
            $block_type = $_POST['block_type'];
            $plugin_name = isset($_POST['plugin_name']) ? $_POST['plugin_name'] : '';
            $block_html = $_POST['block_html'];
            $block_page_visi_type = $_POST['block_page_visi_type'];
            $pages = $_POST['pages'];


            if ($block_type == 'html') {

                $plugin_name = 'html';
            }


            $array = array(
                'module' => $plugin_name,
                'theme' => $B->get_current_theme(),
                'status' => 0,
                'weight' => 0,
                'region' => $blk_region,
                'content' => $block_html,
                'visibility' => (int) $block_page_visi_type,
                'pages' => $pages,
                'title' => $blk_name
            );


            if ($_POST['mode'] == 'add') {
                $id = DB::table(PREFIX . "codo_blocks")->insertGetId(
                        $array
                );
            } else if ($_POST['mode'] == 'edit') {

                $id = (int) $_POST['bid'];
                DB::table(PREFIX . "codo_blocks")
                        ->where('id', $id)
                        ->update($array);
            }
            DB::table(PREFIX . "codo_block_roles")->where('bid', '=', $id)->delete();


            $roles = array();
            $i = 0;

            if (isset($_POST['roles'])) {

                foreach ($_POST['roles'] as $role) {

                    $roles[$i]['bid'] = $id;
                    $roles[$i]['rid'] = $role;
                    $i++;
                }
            }

            //var_dump($roles);

            if (count($roles) > 0) {
                DB::table(PREFIX . "codo_block_roles")->insert($roles);
            }
            header("Location: index.php?page=ui/blocks&action=editblock&id=$id");
            exit();
        }
    } elseif ($_GET['action'] == 'delete' && CODOF\Access\CSRF::valid( ($_GET['CSRF_token']))) {

        $id = $_GET['id'];
        DB::table(PREFIX . "codo_block_roles")->where('bid', '=', $id)->delete();
        DB::table(PREFIX . "codo_blocks")->where('id', '=', $id)->delete();
        header("Location: index.php?page=ui/blocks");
    } else if ($_GET['action'] == 'editblock') {


        $id = (int) $_GET['id'];
        $current_block = DB::table(PREFIX . "codo_blocks")->where('id', $id)->first();

        //var_dump($current_block);

        $smarty->assign('current_block', $current_block);
        $smarty->assign('mode', 'edit');
        $smarty->assign('selected_region', $current_block['region']);

        if ($current_block['module'] == 'html') {
            $smarty->assign('h_selected', ' selected="selected" ');
        } else {
            $smarty->assign('p_selected', ' selected="selected" ');
        }

        if ($current_block['visibility'] == 0) {
            $smarty->assign('a_selected', ' selected="selected" ');
        } else {
            $smarty->assign('o_selected', ' selected="selected" ');
        }


        //default add new block page
        $av_blocks = $B->get_all_blocks(); //available block regions in a theme


        $smarty->assign('av_blocks', $av_blocks);
        $smarty->assign('bid', $id);
        $plugins = $B->get_block_type_plugins();
        $roles = $B->get_roles($id);
        $nroles = array();
        foreach ($roles as $role) {

            if ($role['bid'] != '' || $role['bid'] != null) {

                $role['checked'] = ' checked="checked" ';
            }

            $nroles[] = $role;
        }

        $smarty->assign('roles', $nroles);
        $smarty->assign('plugins', $plugins);
        $smarty->assign('selected_plugin', $current_block['module']);
        $content = $smarty->fetch('ui/block_edit.tpl');
    } else if ($_GET['action'] == 'addnewblock') {


        //default add new block page
        $av_blocks = $B->get_all_blocks(); //available block regions in a theme

        $smarty->assign('av_blocks', $av_blocks);
        $plugins = $B->get_block_type_plugins();
        $roles = $B->get_roles();
        $smarty->assign('roles', $roles);
        $smarty->assign('plugins', $plugins);
        $smarty->assign('selected_plugin', 'xxx');
        $smarty->assign('selected_region', 'xxx');
        $content = $smarty->fetch('ui/block_edit.tpl');
    }
} else {
    $av_blocks = $B->get_all_blocks(); //available block regions in a theme
    $B->save_blocks();
    $blocks = $B->list_blocks();
    $smarty->assign('av_blocks', $av_blocks);
    $smarty->assign('blocks', $blocks);
    $smarty->assign('theme', $B->theme);
    $content = $smarty->fetch('ui/blocks.tpl');
}

