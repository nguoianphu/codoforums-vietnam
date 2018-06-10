<?php

/*
 * @CODOLICENSE
 */

defined('IN_CODOF') or die();


dispatch_post('sso/authorize', function() {

    //CSRF protection  
    if (\CODOF\Access\Request::valid($_POST['token'])) {

        //$id = $_POST['uid'];      

        $user = $_POST['sso'];
        $posted_token = $user['token'];
        
        $secret = CODOF\Util::get_opt('sso_secret');

        if(!empty($user)) {
            
            unset($user['token']);
            $sso_token = md5(urlencode(json_encode($user)) . $secret . $_POST['timestamp']);
        }

        $username = $user['name'];
        $mail = $user['mail'];

        if ($sso_token != $posted_token) {
            
            echo 'error';
            exit;
        }
        

        
        $db = DB::getPDO();

        if (!CODOF\User\User::mailExists($mail)) {

            //this user does not have an account in codoforum
            $reg = new \CODOF\User\Register($db);
            if(\CODOF\User\User::usernameExists($username)) {
                
                $username .= time();
            }
            $reg->username = $username;
            $reg->name = htmlentities($username, ENT_QUOTES, 'UTF-8');
            $reg->mail = $mail;
            $reg->user_status = 1;
            $ret = $reg->register_user();
            $reg->login();
            
            if(!empty($ret)) {
                
                echo "error";
            }
        } else {

            CODOF\User\User::loginByMail($mail);
        }
    }
});

function add_sso_js() {

    add_js(PLUGIN_PATH . 'sso/assets/js/sso.js', array('name' => 'sso.js', 'type' => 'defer'));
    add_css(PLUGIN_PATH . 'sso/assets/css/sso.css', array('name' => 'sso.css'));
}

//lets write the req info in divs
//so that they can be fetched later using javascript
function add_sso_defs($dom) {

    $container = $dom->getElementById('codo_js_php_defs');

    $sso_token = md5(time() . CODOF\Util::get_opt('sso_secret'));
    $sso_client_id = CODOF\Util::get_opt('sso_client_id');
    $sso_get_user_path = CODOF\Util::get_opt('sso_get_user_path');
    $sso_login_user_path = CODOF\Util::get_opt('sso_login_user_path');

    $auto_login = 'no';
    if (isset($_GET['sso']) && $_GET['sso'] == 'login') {

        $auto_login = 'yes';
    }

    $html = <<<EOD
        <div id="_codo_sso_token">$sso_token</div>      
        <div id="_codo_sso_auto_login">$auto_login</div>    
        <div id="_codo_sso_client_id">$sso_client_id</div>
        <div id="_codo_sso_get_user_path">$sso_get_user_path</div>
        <div id="_codo_sso_login_user_path">$sso_login_user_path</div>
            
EOD;

    //prepend our code
    $container->innertext = $html . $container->innertext;


    $container = $dom->getElementById('codo_navbar_content');

    $html = <<<EOD
        <div class="codo_login_loading"></div>       
EOD;

    //prepend our code
    $container->innertext = $html . $container->innertext;
        
}

function add_login_as($dom) {
    
        $container = $dom->getElementById('codo_login_container');

        $sso_name = CODOF\Util::get_opt('sso_name');
        
        $html = <<<EOD
        <div class="row codo_sso">
          <div class="codo_sso_login_btn codo_sso_login_btn" id="codo_login_with_sso">with <span>$sso_name</span></div>
        </div>   
EOD;

        //prepend our code
        $container->innertext = $html . $container->innertext;
    
}

CODOF\Hook::add('tpl_after_user_login', "add_login_as");

//Below hooks are called on all pages
CODOF\Hook::add('before_site_head', "add_sso_js");
CODOF\Hook::add('after_site_head', "add_sso_defs");

