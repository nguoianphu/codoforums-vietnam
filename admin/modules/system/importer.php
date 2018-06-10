<?php

/*
 * @CODOLICENSE
 */

$smarty = \CODOF\Smarty\Single::get_instance();

$db = \DB::getPDO();

if (isset($_GET['import']) && CODOF\Access\CSRF::valid($_GET['CSRF_token'])) {

    $_DB = array(
        'driver' => 'mysql',
        'host' => $_GET['db_host'],
        'database' => $_GET['db_name'],
        'username' => $_GET['db_user'],
        'password' => $_GET['db_pass'],
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => $_GET['tbl_prefix'],
    );


    $work = new \CODOF\Importer\ImportWorker($_DB, $_GET['import_from']);

    $work->max_rows = (int) $_GET['max_rows'];
    $work->import_admin_mail = $_GET['admin_mail'];
    $work->connect_db();

    $is_admin = $work->isset_admin_account();

    $step = (int) $_GET['import_step'];
    $counter = new CODOF\Importer\Concise($db, $_GET['import_from']);

    if ($work->connected && $is_admin) {


        switch ($step) {

            case 1:
                $_SESSION['total_import_time'] = 0;
                $time = microtime(true);
                $work->empty_tables('categories');
                $work->import_cats();
                echo "<br/>Categories imported in : ";
                $diff = microtime(true) - $time;
                echo $diff;
                $_SESSION['total_import_time'] += $diff;
                $time = microtime(true);
                $work->empty_tables('users');
                $work->import_users();
                echo "<br/>users imported in : ";
                $diff = microtime(true) - $time;
                echo $diff;
                $_SESSION['total_import_time'] += $diff;
                echo "<br/><br/>Importing topics...";
                break;

            case 2:
                $time = microtime(true);
                $work->empty_tables('topics');
                $work->import_topics();
                echo "<br/>topics imported in : ";
                $diff = microtime(true) - $time;
                echo $diff;
                $_SESSION['total_import_time'] += $diff;
                echo "<br/><br/>Importing posts...";
                break;

            case 3:
                $time = microtime(true);
                $work->empty_tables('posts');
                $work->import_posts();
                echo "<br/>posts imported in : ";
                $diff = microtime(true) - $time;
                echo $diff;
                $_SESSION['total_import_time'] += $diff;
                echo "<br/><br/>Checking counts of users...";
                break;

            case 4:

                if ($work->has_codopm_tables()) {

                    $time = microtime(true);
                    echo "<br/><br/>Found codopm data : ";
                    $work->import_table('codopm_messages', false);
                    echo "<br/>codopm data imported in : ";
                    $diff = microtime(true) - $time;
                    echo $diff . " s";
                    $_SESSION['total_import_time'] += $diff;
                }

                $counter->update_count_users();
                $time = microtime(true);
                echo "<br/>user post counts updated in : ";
                $diff = microtime(true) - $time;
                echo $diff . " s";
                $_SESSION['total_import_time'] += $diff;
                echo "<br/><br/>Checking counts of topics...";
                break;

            case 5:

                $counter->update_count_topics();
                $time = microtime(true);
                echo "<br/>topic counts updated in : ";
                $diff = microtime(true) - $time;
                echo $diff . " s";
                $_SESSION['total_import_time'] += $diff;
                break;

            case 6:

                $counter->update_count_categories();
                $time = microtime(true);
                echo "<br/>category counts updated in : ";
                $diff = microtime(true) - $time;
                echo $diff . " s";
                $_SESSION['total_import_time'] += $diff;
                echo "<br/><br/>Importing permissions, bans, blocks, block_roles, edits, fields, fields_roles, fields_values";
                break;



            case 7:
                if ($_GET['import_from'] == 'Codoforum') {


                    $tables = array('permissions', 'bans', 'blocks', 'block_roles', 'edits', 'fields', 'fields_roles', 'fields_values');

                    foreach ($tables as $table) {
                        $time = microtime(true);
                        $work->import_table('codo_' . $table);
                        echo "<br/>$table imported in : ";
                        $diff = microtime(true) - $time;
                        echo $diff;
                        $_SESSION['total_import_time'] += $diff;
                    }

                    echo "<br/><br/>Importing notify_subscribers, notify, notify_text, pages, page_roles, reports, reputation, roles, smileys";
                }
                break;

            case 8:
                if ($_GET['import_from'] == 'Codoforum') {


                    $tables = array('notify_subscribers', 'notify', 'notify_text', 'pages', 'page_roles', 'reports', 'reputation', 'roles', 'smileys');

                    foreach ($tables as $table) {
                        $time = microtime(true);
                        $work->import_table('codo_' . $table);
                        echo "<br/>$table imported in : ";
                        $diff = microtime(true) - $time;
                        echo $diff;
                        $_SESSION['total_import_time'] += $diff;
                    }
                    echo "<br/><br/>Importing tags, unread_categories, unread_topics, user_preferences, views";
                }
                break;

            case 9:

                if ($_GET['import_from'] == 'Codoforum') {

                    //lets import everything
                    $tables = array('tags', 'unread_categories', 'unread_topics', 'user_preferences', 'views');

                    foreach ($tables as $table) {
                        $time = microtime(true);
                        $work->import_table('codo_' . $table);
                        echo "<br/>$table imported in : ";
                        $diff = microtime(true) - $time;
                        echo $diff;
                        $_SESSION['total_import_time'] += $diff;
                    }
                }
                break;

            case 10:
                if ($_GET['import_from'] == 'Codoforum') {

                    $version = $work->get_imported_cf_ver();
                    echo "<br/><br/>Found imported codoforum data to be from V." . $version;

                    $upgrader = new \CODOF\Upgrade\Upgrade();
                    if ($upgrader->currentVersionGreaterThan($version)) {

                        $time = microtime(true);
                        echo "<br/>Upgrading codoforum schema to latest version.";
                        $upgrader->upgradeDB($version);
                        echo "<br/>Schema upgraded to latest ";
                        $diff = microtime(true) - $time;
                        echo $diff;
                        $_SESSION['total_import_time'] += $diff;
                    }
                }

                echo "<br/><br/><b>Import successfull in total time " . $_SESSION['total_import_time'] . " s</b>";

                break;
        }
    } else if (!$is_admin) {

        echo "admin e-mail address given does not exists!";
    } else {

        echo "Unable to connect to database";
    }

    exit;
}

$files = array();
if ($handle = opendir(ABSPATH . 'sys/CODOF/Importer/Drivers/')) {

    $invalid_entries = array(".", "..", "index.html", "Driver.php", "IDriver.php");

    while (false !== ($entry = readdir($handle))) {

        if (!in_array($entry, $invalid_entries)) {

            $entry = str_replace(".php", "", $entry);
            $files[] = $entry;
        }
    }

    closedir($handle);
}


CODOF\Util::get_config($db);

$smarty->assign('files', $files);
$content = $smarty->fetch('system/importer.tpl');
