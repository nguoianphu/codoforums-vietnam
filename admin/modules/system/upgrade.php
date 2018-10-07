<?php

/*
 * @CODOLICENSE
 */

$smarty = \CODOF\Smarty\Single::get_instance();

$db = \DB::getPDO();

\CODOF\Util::get_config($db);
define('CODOF_VERSION', str_replace(".php", "", \CODOF\Util::get_opt('version')));

class upgrader {

    public static $latest_version = "1.0";
    public static $base_url = "https://codoforum.com/";

    static function is_required() {


        if (version_compare(CODOF_VERSION, upgrader::$latest_version) == -1) {

            return true;
        }
        return false;
    }

    static function check_writable() {

        $paths = array("index.php", ADMIN . "index.php", "admin", "sys", "sys/CODOF/Util.php", "sites");


        foreach ($paths as $path) {


            if (!is_writable(ABSPATH . $path)) {

                echo "#> Error-Path not writable: " . ABSPATH . $path . "<br>\n";
                return false;
            }

            echo "3.1> Files seem writable :) <br>";

            return true;
        }
    }

    static function chmod_array($ftp, $array, $prepend, $mode) {

        foreach ($array as $thing) {

            $ftp->chmod($prepend . $thing, $mode);
        }
    }

    static function ftp_step() {

        $result = upgrader::get_all_files();



        require "ftpabstract.php";

        $mstring = "cache/" . time() . ".php";


        file_put_contents(ABSPATH . $mstring, "adi");
        chmod(ABSPATH . $mstring, 0777);

        $ftp = new ftp();
        $ftp->Verbose = TRUE;
        $ftp->LocalEcho = TRUE;
        if (!$ftp->SetServer($_REQUEST['fserver'])) {
            $ftp->quit();
            die("Setting server failed :(\n<br>");
        }

        if (!$ftp->connect()) {
            die("Cannot connect: Refresh and try again\n<br>");
        }
        if (!$ftp->login($_REQUEST['fusername'], $_REQUEST['fpassword'])) {
            $ftp->quit();
            die("Login failed: Refresh and try again\n<br>");
        }

        require 'path.php';

        $finder = new finder();
        $finder->mstring = $mstring;
        $finder->connect($ftp);
        $res = $finder->searcher();


        $dirs = $result['dirs'];
        $phpfiles = $result['phpfiles'];
        $only_files = $result['only_files'];
        $everything = $phpfiles; //$result["everything"];
        //  file_put_contents("out.txt", print_r($everything, true));

        $xdirs = \CODOF\Util::get_777s();

        @$ftp->chmod($res, 0777);

        foreach ($everything as $thing) {


            @$ftp->chmod($res . $thing, 0777);
            // echo $res.$thing."<br>"; 
        }

        upgrader::chmod_array($ftp, $everything, $res, 0777);



        upgrader::direct_upgrade();


        $result = upgrader::get_all_files(); //get all files after unpacking
        $dirs = $result['dirs'];
        $phpfiles = $result['phpfiles'];
        $only_files = $result['only_files'];
        $everything = $phpfiles; //$result["everything"];

        upgrader::chmod_array($ftp, $phpfiles, $res, 0644); //PHP FILES
        upgrader::chmod_array($ftp, $dirs, $res, 0755); //ALL DIRS
        upgrader::chmod_array($ftp, $xdirs, $res, 0777); //CACHE & SITE DIRS
    }

    static function direct_upgrade() {

        echo ">Unpacking contents <br>";

        upgrader::unpack();


        echo ">Unpacking contents Completed.<br>";

        echo ">Ungrading schema <br>";
        upgrader::upgrade_schema();

        echo "> <br>Upgrading Schema Completed. <br>";
    }

    static function unpack() {

        require 'class-pclzip.php';

        $zip = new PclZip(ABSPATH . 'cache/' . $_SESSION['codo_file_to_download']);

        $x = $zip->extract(PCLZIP_OPT_PATH, ABSPATH);
    }

    static function upgrade_schema() {
        
        $upgrader = new \CODOF\Upgrade\Upgrade();
        $upgrader->upgradeDB(CODOF_VERSION);
    }

    static function file_upgrade() {

        upgrader::check_writable();
    }

    static function check_latest() {

        echo "####: Initiating cURL request<br>";
        $curl = new Curl\Curl();
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
		//$curl_setopt(CURLOPT_SSLVERSION , 3);
        $curl->get(upgrader::$base_url . 'news/latest.php');

        if ($curl->error) {
            echo '&nbsp;&nbsp;Error: ' . $curl->error_code . ': ' . $curl->error_message;
        } else {

            $arr = explode("|", $curl->response);
            echo "1.3> Response: <br>"
            . "Raw: " . $curl->response . '<br>'
            . "Latest version is " . $arr[0] . "<br>"
            . "File to be downloaded to cache dir: " . $arr[1] . "<br>";

            // unset($_SESSION['codo_file_to_download']);
            // var_dump($_SESSION);
            $_SESSION['codo_file_to_download'] = trim($arr[1]);
            $_SESSION['codo_file_to_download_hash'] = trim($arr[2]);
            // $_SESSION['542d2e3195cfeA_loggedin_avatar']=22;
            upgrader::$latest_version = $arr[0];

            if (upgrader::is_required()) {

                echo "1.4> Initiating Download phase 2.0...<hr>";
            } else {

                echo "&nbsp;&nbsp;&nbsp;&nbsp; You are using the latest version :)";
            }
        }
    }

    static function get_all_files() {


        global $dirs;
        $dirs = array();

        if (!function_exists('dir_scan')) {

            function dir_scan($folder) {
                $files = glob($folder);
                global $dirs;
                foreach ($files as $f) {
                    if (is_dir($f)) {

                        $dirs[] = $f;
                        $files = array_merge($files, dir_scan($f . '/*')); // scan subfolder
                    }
                }
                return $files;
            }

        }

        $abspath = rtrim(ABSPATH, '/');
        $files = dir_scan($abspath);
//print_r($files);

        $result = array();


        foreach ($files as $k => $v) {

            $files[$k] = str_replace(ABSPATH, "", $v);
        }

        $result["everything"] = $files;



        //print_r($dirs);


        foreach ($dirs as $k => $v) {

            $dirs[$k] = str_replace(ABSPATH, "", $v);
        }
        $result['dirs'] = $dirs;

        $only_files = array_diff($files, $dirs);

        $result["only_files"] = $only_files;

        $phpfiles = array();

        foreach ($only_files as $ofile) {

            $ext = pathinfo($ofile, PATHINFO_EXTENSION);

            if ($ext == 'php') {
                $phpfiles[] = $ofile;
            }
        }

        $result["phpfiles"] = $phpfiles;

        return $result;
    }

    static function download() {

        //var_dump($_SESSION);
        $file = $_SESSION['codo_file_to_download'];
        $hash = $_SESSION['codo_file_to_download_hash'];

        //var_dump(md5_file(ABSPATH.'cache/'.$file));

        if (is_file(ABSPATH . 'cache/' . $file) && md5_file(ABSPATH . 'cache/' . $file) == $hash) {

            echo "2.1> File already downloaded :)<hr>";
        } else {

            upgrader::__download();
        }
    }

    static function __download() {

        $file = $_SESSION['codo_file_to_download'];
        $hash = $_SESSION['codo_file_to_download_hash'];

        $url = upgrader::$base_url . 'upgrades/' . $file;
        $path = ABSPATH . 'cache/' . $file;

        $fp = fopen($path, 'w');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);

        curl_close($ch);
        fclose($fp);

        //var_dump($_SESSION);
        if (is_file(ABSPATH . 'cache/' . $file) && md5_file(ABSPATH . 'cache/' . $file) == $hash) {

            echo "2.1> File downloaded sucessfully :)<hr><br>";
        } else {

            echo "2.1> An error occurred while downloading the file.<br>"
            . "Download the file manually: <a href='" . $url . "' target='_blank'>Download now</a><br>"
            . "and place it in the folder: " . ABSPATH . 'cache/';
        }
    }

    static function run() {


        if (isset($_GET['upgrade']) && CODOF\Access\CSRF::valid($_GET['CSRF_token'])) {

            require ABSPATH . ADMIN . 'modules/system/Curl.php';

            if (isset($_GET['checklatest'])) {
                upgrader::check_latest();
            } else if (isset($_GET['download'])) {

                upgrader::download();
            } else if (isset($_GET['file_upgrade'])) {

                upgrader::file_upgrade();
            } else if (isset($_GET['direct_upgrade'])) {

                echo 'started DU';
                upgrader::direct_upgrade();
            } else if (isset($_GET['ftp_step'])) {

                upgrader::ftp_step();
            }

            session_write_close();
            if (ob_get_contents()) ob_end_flush();
            exit();
        }
    }

}

//var_dump(upgrader::is_required());

upgrader::run();
CODOF\Util::get_config($db);

//$smarty->assign('files', $files);
$content = $smarty->fetch('system/upgrade.tpl');
