<?php

/*
 * @CODOLICENSE
 */


$tpl = 'plugins.tpl';

$smarty->assign('A_RURI', A_RURI);

class Backend_plugins {

    public $db = null;

    function __construct() {

        $this->db = \DB::getPDO();
    }

//---------------------------------------------------------
    function disable_plugin($plugin) {

        $query = "UPDATE " . PREFIX . "codo_plugins SET plg_status=0 WHERE plg_name=:plugin";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array(':plugin' => $plugin));
    }

//----------------------------------------------------------
    function enable_plugin($plugin) {

        $sql = "SELECT count(*) FROM " . PREFIX . "codo_plugins WHERE plg_name=:plugin";
        $result = $this->db->prepare($sql);
        $result->execute(array(':plugin' => $plugin));
        $number_of_rows = $result->fetchColumn();

        if ($number_of_rows > 0) { //if plugin already exists, update
            $query = "UPDATE " . PREFIX . "codo_plugins SET plg_status=1 WHERE plg_name=:plugin";
            $stmt = $this->db->prepare($query);
            $stmt->execute(array(':plugin' => $plugin));
        } else { //else insert
            $query = "INSERT INTO " . PREFIX . "codo_plugins (plg_name,plg_type,plg_status,plg_weight,plg_schema_ver) VALUES(:plugin,:plg_type,1,0,0)";

            $root = PLUGIN_DIR . $plugin . '/';
            require $root . $plugin . '.info.php';
            
            if(!isset($info['plugin_type'])){
                $info['plugin_type']='plugin';
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->execute(array(':plugin' => $plugin, ':plg_type' => $info['plugin_type']));
        }
    }

//------------------------------------------------------------

    public function update_plugin_version($plugin, $version) {

        $qry = "UPDATE " . PREFIX . "codo_plugins SET plg_schema_ver=:version WHERE plg_name=:plugin";
        $stmt = $this->db->prepare($qry);
        $stmt->execute(array(":plugin" => $plugin, ":version" => $version));
    }

//------------------------------------------------------------

    public function install_plugin($plugin) {

        $root = PLUGIN_DIR . $plugin . '/';

        require $root . $plugin . '.info.php';

        $plg_ver = $info['version'];

        $files = array();
        $names = array();
        foreach (glob($root . "install/*.php") as $filename) {

            $parts = explode("/", $filename);
            $name = array_pop($parts);

            $files[$name] = $filename;
            $names[] = $name;
        }

        natsort($names);

        foreach ($names as $name) {

            require $files[$name];
        }

        $this->enable_plugin($plugin);
        $this->update_plugin_version($plugin, $plg_ver);
    }

//------------------------------------------------------------

    public function upgrade_plugin($plugin) {

        $root = PLUGIN_DIR . $plugin . '/';

        require $root . $plugin . '.info.php';

        $plg_ver = $info['version'];

        $qry = 'SELECT plg_schema_ver FROM ' . PREFIX . 'codo_plugins WHERE plg_name=:plugin';
        $stmt = $this->db->prepare($qry);
        $stmt->execute(array(":plugin" => $plugin));
        $res = $stmt->fetch();

        $installed_plg_ver = $res['plg_schema_ver'];

        $files = array();
        $names = array();
        foreach (glob($root . "install/*.php") as $filename) {

            $parts = explode("/", $filename);
            $name = str_replace(".php", "", array_pop($parts));

            $files[$name] = $filename;
            $names[] = $name;
        }

        natsort($names);

        foreach ($names as $name) {

            if (version_compare($installed_plg_ver, $name) === -1) {

                //run if file is greater than installed plugin version
                require $files[$name];
            }
        }

        $this->enable_plugin($plugin);
        $this->update_plugin_version($plugin, $plg_ver);
    }

//------------------------------------------------------------

    function plugin_in_list($needle, $list) {


        foreach ($list as $elem) {


            if ($needle == $elem['plg_name']) {

                return $elem;
            }
        }

        return false;
    }

//------------------------------------------------------------------
    /**
     *
     * bad plugins r those plugins which exist in the DB but not actually on disk
     */
    function get_bad_plugins($dplugins, $fplugins) {


        foreach ($dplugins as $dplugin) {


            $elem = $this->plugin_in_list($dplugin['plg_name'], $fplugins);


            if ($elem == false) {

                $query = "DELETE FROM " . PREFIX . "codo_plugins WHERE plg_name='" . $dplugin['plg_name'] . "'";
                $this->db->query($query);
            }
        }
    }

//-------------------------------------------------------------------
    /**
     *
     * Merge data from get_plugins_db() & get_plugins_fs()
     */
    function merge_db_fs() {


        $dplugins = $this->get_plugins_db();
        $fplugins = $this->get_plugins_fs();

        $plugins = array();

        foreach ($fplugins as $fplugin) {


            $elem = $this->plugin_in_list($fplugin['plg_name'], $dplugins);

            $installed = true;
            $enabled = false;
            if ($elem === false) {

                $installed = false;
            } else {

                $enabled = $elem['plg_status'];
            }


            /* if ($elem !== false) {


              $fplugin['plg_status'] = $elem['plg_status'];
              } else {

              $fplugin['plg_status'] = 0;
              }

              if ($fplugin['plg_status'] == 0) {

              $fplugin['rowstyle'] = 'plgdisabled';
              } else {

              $fplugin['rowstyle'] = 'plgenabled';
              } */

            if ($elem['plg_status'] == 0) {
                $fplugin['rowstyle'] = 'plgdisabled';
            } else {
                $fplugin['rowstyle'] = 'plgenabled';
            }

            $upgradable = version_compare($fplugin['version'], $elem['plg_schema_ver']) === 1;
            $fplugin['plg_status'] = $this->get_plugin_status($enabled, $installed, $upgradable);

            $plugins[] = $fplugin;
        }


        $this->get_bad_plugins($dplugins, $fplugins);

        return $plugins;
    }

//-------------------------------------------------------------------------------

    /**
     * 1 -> install
     * 2 -> enable
     * 3 -> disable
     * 4 -> upgrade
     * 
     * @param bool $enabled
     * @param bool $installed
     */
    public function get_plugin_status($enabled, $installed, $upgradable) {

        if (!$enabled && !$installed) {

            return 1;
        } else if ($installed && !$enabled) {

            return 2;
        } else if ($installed && $upgradable) {

            return 4;
        } else {

            return 3;
        }
    }

//-------------------------------------------------------------------------------

    /**
     *
     * Get plugins from the DB
     *
     */
    function get_plugins_db() {


        $query = "SELECT * FROM " . PREFIX . "codo_plugins";
        $res = $this->db->query($query);
        $plugins = $res->fetchAll();

        return $plugins;
    }

//--------------------------------------------------------------------------------

    /**
     *
     * Get plugins from the file system (plugins directory)
     *
     */
    function get_plugins_fs() {

        $dirItr = new DirectoryIterator(DATA_PATH . "plugins/");

        $plugins = array();

        foreach ($dirItr as $dir) {

            if ($dir->isDot())
                continue;

            if ($dir->isFile())
                continue;

            $info_file_path = $dir->getPathname() . "/" . $dir->getFilename() . ".info.php";

            if (is_file($info_file_path)) {

                $info['plg_name'] = $dir->getFilename();
                $info['name'] = '';
                $info['description'] = '';
                $info['version'] = '';
                $info['author'] = "";
                $info['author_url'] = '';
                $info['license'] = '';
                $info['core'] = '';
                $info['admin'] = false;

                if (is_file($dir->getPathname() . "/" . ADMIN . $dir->getFilename() . ".admin.php")) {


                    $info['admin'] = true;
                }

                require $info_file_path;

                $plugins[] = $info;

                //var_dump($info);
            }
        }

        return $plugins;
    }

}

$plg = new Backend_plugins();
//$plg->get_plugins_fs();
//$plg->get_plugins_db();
if (isset($_POST['action']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {


    if ($_POST['action'] == 'install') {

        $plg->install_plugin($_POST['plugin']);
        //$plg->enable_plugin($_POST['plugin']);
    } else if ($_POST['action'] == 'upgrade') {

        $plg->upgrade_plugin($_POST['plugin']);
    } else if ($_POST['action'] == 'enable') {

        $plg->enable_plugin($_POST['plugin']);
    } else if ($_POST['action'] == 'disable') {

        $plg->disable_plugin($_POST['plugin']);
    } else {
        
    }

    header("Location: index.php?page=plugins/plugins");
}


$smarty->assign('plugins', $plg->merge_db_fs());
$content = $smarty->fetch($tpl);
