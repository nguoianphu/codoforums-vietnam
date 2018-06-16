<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Booter;

use CODOF\Hook;
use CODOF\Util;
use Illuminate\Database\Capsule\Manager as Capsule;

define('ABSPATH', (((dirname(dirname(__FILE__))))) . '/');
define('CODO_SITE', 'default');

define('ADMIN', 'admin/');

if (@$_SERVER["HTTPS"] == "on") {
    $protocol = "https://";
} else {
    $protocol = "https://";
}

$path = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
$path = str_replace(ADMIN, "", $path);
require ABSPATH . 'sys/CODOF/Booter/Load.php';

require ABSPATH . 'sites/' . CODO_SITE . '/constants.php';

\Constants::pre_config($path);

if (file_exists(DATA_PATH . 'config.php')) {

    //contains valuable db information
    require DATA_PATH . 'config.php';

    $container = new Load();

    //IoC::setIoCContainer($container);
    //$container->loadServiceProvider();

    if (!$installed) {
        $r_path = str_replace("index.php", "", $path);
        header('Location: ' . $r_path . 'install');
    }


    \Constants::post_config($CONF);
    //contains routing system
    //require ABSPATH . 'sys/Ext/limonade/limonade.php';

    require ABSPATH . 'sys/vendor/autoload.php';

    $capsule = new Capsule();

    $config = get_codo_db_conf();

    $capsule->addConnection($config);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    \CODOF\Database\Schema::storeSchemaConnection($capsule);
    // $x = $container->make('db')->query('SELECT * FROM codo_config')->fetchAll();

    Hook::call('after_config_loaded');
    //  Util::$use_normal_sessions=true;
    Util::start_session();

    require SYSPATH . 'globals/global.php';

    //initiate all plugins
    //Now the plugins can work on the data available
    //$plg = new \CODOF\Plugin();
    //$plg->init();
} else {

    die('codoforum is not installed!');
}
