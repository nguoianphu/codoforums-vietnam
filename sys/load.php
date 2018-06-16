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


if (@$_SERVER["HTTPS"] == "on") {
    $protocol = "https://";
} else {
    $protocol = "http://";
}

$path = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];

require 'CODOF/Booter/Load.php';

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
    require ABSPATH . 'sys/Ext/limonade/limonade.php';

    require ABSPATH . 'sys/vendor/autoload.php';

    $capsule = new Capsule();

    $config = get_codo_db_conf();

    $capsule->addConnection($config);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    // $x = $container->make('db')->query('SELECT * FROM codo_config')->fetchAll();

    Hook::call('after_config_loaded');

    Util::start_session();

    //$u = \User::get();
    //var_dump($u->id);
    //exit('hello');

    require SYSPATH . 'globals/global.php';

    //initiate all plugins
    //Now the plugins can work on the data available
    $plg = new \CODOF\Plugin();
    $plg->init();

    $subscriber = new \CODOF\Forum\Notification\Subscriber();
    $subscriber->registerCoreTypes();

} else {

    die('codo forums not installed!');
}
