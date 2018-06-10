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

    function list_pages() {


        $query = "SELECT * FROM " . PREFIX . "codo_pages";
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

    function get_roles($pid = null) {


        if ($pid == null) {

            $query = "SELECT role.rname, role.rid
                FROM  " . PREFIX . "codo_roles AS role";
        } else {

            $pid = (int) $pid;
            $query = "SELECT role.rname, role.rid, page_role.pid "
                    . "FROM codo_roles AS role "
                    . "LEFT JOIN codo_page_roles AS page_role ON role.rid = page_role.rid "
                    . "AND page_role.pid =$pid";
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

            $page_title = $_POST['page_title']; //title
            $page_url = $_POST['page_url'];
            $page_html = $_POST['page_html'];

            $array = array(
                'title' => $page_title,
                'url' => CODOF\Filter::URL_safe($page_url),
                'content' => $page_html
            );


            if ($_POST['mode'] == 'add') {
                $id = DB::table(PREFIX . "codo_pages")->insertGetId(
                        $array
                );
            } else if ($_POST['mode'] == 'edit') {

                $id = (int) $_POST['pid'];
                DB::table(PREFIX . "codo_pages")
                        ->where('id', $id)
                        ->update($array);
            }
            DB::table(PREFIX . "codo_page_roles")->where('pid', '=', $id)->delete();


            $roles = array();
            $i = 0;

            if (isset($_POST['roles'])) {

                foreach ($_POST['roles'] as $role) {

                    $roles[$i]['pid'] = $id;
                    $roles[$i]['rid'] = $role;
                    $i++;
                }
            }

            //var_dump($roles);

            if (count($roles) > 0) {
                DB::table(PREFIX . "codo_page_roles")->insert($roles);
            }
            header("Location: index.php?page=pages/pages&action=editpage&id=$id");
            exit();
        }
    } elseif ($_GET['action'] == 'delete' && CODOF\Access\CSRF::valid(($_GET['CSRF_token']))) {

        $id = $_GET['id'];
        DB::table(PREFIX . "codo_page_roles")->where('pid', '=', $id)->delete();
        DB::table(PREFIX . "codo_pages")->where('id', '=', $id)->delete();
        header("Location: index.php?page=pages/pages");
    } else if ($_GET['action'] == 'editpage') {


        $id = (int) $_GET['id'];
        $current_page = DB::table(PREFIX . "codo_pages")->where('id', $id)->first();

        //var_dump($current_block);

        $smarty->assign('current_page', $current_page);
        $smarty->assign('mode', 'edit');


        $smarty->assign('pid', $id);
        $roles = $B->get_roles($id);
        $nroles = array();
        foreach ($roles as $role) {

            if ($role['pid'] != '' || $role['pid'] != null) {

                $role['checked'] = ' checked="checked" ';
            }

            $nroles[] = $role;
        }

        $smarty->assign('roles', $nroles);

        $content = $smarty->fetch('pages/page_edit.tpl');
    } else if ($_GET['action'] == 'addnewpage') {


        $roles = $B->get_roles();
        $smarty->assign('roles', $roles);

        $id = \DB::table(PREFIX . 'codo_pages')->max('id');
        $smarty->assign('pid', $id + 1);
        $content = $smarty->fetch('pages/page_edit.tpl');
    }
} else {
    $av_blocks = $B->get_all_blocks(); //available block regions in a theme
    $B->save_blocks();
    $pages = $B->list_pages();
    $smarty->assign('av_blocks', $av_blocks);
    $smarty->assign('pages', $pages);
    $smarty->assign('theme', $B->theme);
    $content = $smarty->fetch('pages/pages.tpl');
}

