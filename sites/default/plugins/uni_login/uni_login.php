<?php

/*
 * @CODOLICENSE
 */

/**
 * 
 * There is no restriction whether to use OOP or procedural 
 * 
 * preferred pattern
 * assets/ your static resources
 *         js/  your javascript
 *         css/ your css files
 *         img/ your images
 *         tpl/ your .tpl files
 * 
 * you are free to follow your own style.
 */
/**
 * All files should include below defined or die line
 * 
 */
defined('IN_CODOF') or die();


dispatch('uni_login/authorize', function() {

    require_once SYSPATH . "Ext/hybridauth/Hybrid/Auth.php";
    require_once SYSPATH . "Ext/hybridauth/Hybrid/Endpoint.php";

    Hybrid_Endpoint::process();
});


//dispatch('abc', function() {echo "hi";});
/**
 * you can define your own routes using dispatch_t(get/post)
 * wildcards can be used 
 * files must end with .tpl and no php is allowed inside template files
 * not even using the smarty php tags by default 
 * to use any variables use the smarty assign function
 * 
 * All .tpl files in a plugin must follow the below layout
 * 
 * {* Smarty *}
 * {extends file='layout.tpl'}
 *
 * {block name=body}
 *
 *  YOUR PLUGIN HTML 
 * {/block}
 * 
 * if you want to remove the header and footer comment the {extends... } line
 * 
 * 
 * How to load your template file ?
 * 
 * You can load your smarty tpl file for eg. my_blog.tpl using
 * \CODOF\Plugin::tpl('my_blog')
 * do not include .tpl at the end
 * 
 */
dispatch_get('uni_login/login/:name', function($name) {

    // config and includes    
    $config = SYSPATH . 'Ext/hybridauth/config.php';
    require_once SYSPATH . "Ext/hybridauth/Hybrid/Auth.php";

    try {
        // hybridauth EP
        $hybridauth = new Hybrid_Auth($config);

        // automatically try to login with Twitter
        $adapter = $hybridauth->authenticate($name);

        // get the user profile 
        $user_profile = $adapter->getUserProfile();
//        var_dump($user_profile);
        //oauth identifier
        $oauth_id = md5($name . $user_profile->identifier);
        $db = \DB::getPDO();

        $qry = 'SELECT id, username, avatar FROM ' . PREFIX . 'codo_users WHERE oauth_id=:oauth_id';

        $stmt = $db->prepare($qry);
        $stmt->execute(array(":oauth_id" => $oauth_id));

        $username = CODOF\Filter::clean_username($user_profile->displayName);
        $profile = $stmt->fetch();

        if (!empty($profile)) {


            if ($username != $profile['username'] || $user_profile->photoURL != $profile['avatar']) {

                //profile has been updated remotely
                $qry = 'UPDATE ' . PREFIX . 'codo_users SET username=:name,avatar=:avatar WHERE oauth_id=:id';
                $stmt = $db->prepare($qry);
                $stmt->execute(array(":name" => $username, ":avatar" => $user_profile->photoURL, ":id" => $oauth_id));
            }

            CODOF\User\User::login($profile['id']);
        } else {


            //no local copy of this profile yet

            $mail = $user_profile->email;
            $create_account = true;

            if ($mail == null) {

                $mail = '';
            } else {

                //we got an email, lets check if it exists

                $qry = "SELECT id FROM " . PREFIX . "codo_users WHERE mail=:mail";
                $stmt = $this->db->prepare($qry);
                $stmt->execute(array(":mail" => $mail));
                $res = $stmt->fetch();

                if (!empty($res)) {

                    //looks like this user has already registered
                    $create_account = false;


                    CODOF\User\User::login($res['id']);

                    //now this will work if you change authentication from
                    //fb to gmail etc
                }
            }

            if ($create_account) {

                $reg = new CODOF\User\Register($db);
                $reg->mail = $mail;
                $reg->name = $user_profile->firstName . ' ' . $user_profile->lastName;
                $reg->oauth_id = $oauth_id;
                $reg->username = $username;
                $reg->avatar = $user_profile->photoURL;
                $reg->user_status = 1; //approved user
                $reg->register_user();
                $reg->login();
            }
        }

        header('Location: ' . CODOF\User\User::getProfileUrl());
        //$adapter->logout();
    } catch (Exception $e) {
        // In case we have errors 6 or 7, then we have to use Hybrid_Provider_Adapter::logout() to 
        // let hybridauth forget all about the user so we can try to authenticate again.
        // Display the recived error, 
        // to know more please refer to Exceptions handling section on the userguide
        switch ($e->getCode()) {
            case 0 : echo "Unspecified error.";
                break;
            case 1 : echo "Hybridauth configuration error.";
                break;
            case 2 : echo "Provider not properly configured.";
                break;
            case 3 : echo "Unknown or disabled provider.";
                break;
            case 4 : echo "Missing provider application credentials.";
                break;
            case 5 : echo "Authentication failed. "
                . "The user has canceled the authentication or the provider refused the connection.";
                break;
            case 6 : echo "User profile request failed. Most likely the user is not connected "
                . "to the provider and he should to authenticate again.";
                $adapter->logout();
                break;
            case 7 : echo "User not connected to the provider.";
                $adapter->logout();
                break;
            case 8 : echo "Provider does not support this feature.";
                break;
        }
    }
});

class uni_login {

    /**
     * this is called before smarty process html 
     * you can add/remove resources like js/css here
     * you can also modify html using regex - but disabled 
     * 
     */
    public function head() {

        $asset = new \CODOF\Asset\Stream();
        $col = new \CODOF\Asset\Collection('head_col');
        $col->addJS(PLUGIN_PATH . 'uni_login/assets/js/uni_login.js', array('name' => 'uni_login.js', 'type' => 'defer'));
        $col->addCSS(PLUGIN_PATH . 'uni_login/assets/css/uni_login.css', array('name' => 'uni_login.css'));

        $asset->addCollection($col);

    }

    /**
     * 
     * @param DomObject $dom
     * 
     * simplehtmldom object 
     * used to modify/add html
     * can also be used to add js/css by dom manipulation
     * but it is preferred to add resources using add_* functions
     * from tpl_before_* hook 
     */
    public function body($dom) {

        $container = $dom->getElementById('codo_login_container');
        $container2 = $dom->getElementById('codo_register_form');


        $html = <<<EOD
        <div class="row codo_uni_login" id="codo_uni_login">
          <div class="codo_login_btn codo_twitter_login_btn" id="codo_login_with_twitter"><span>Twitter</span></div>
          <div class="codo_login_btn codo_fb_login_btn" id="codo_login_with_facebook"><span>Facebook</span></div>
          <div class="codo_login_btn codo_google_login_btn" id="codo_login_with_google"><span>Google</span></div>
        </div>   
EOD;

        //prepend our code
        if ($container != null) {

            $container->innertext = $html . $container->innertext;
        }

        if ($container2 != null) {

            $container2->innertext = $html . $container2->innertext;
        }
    }

}

$uni = new uni_login();

CODOF\Hook::add('tpl_before_user_login', array($uni, "head"));
CODOF\Hook::add('tpl_after_user_login', array($uni, "body"));
CODOF\Hook::add('tpl_before_user_register', array($uni, "head"));
CODOF\Hook::add('tpl_after_user_register', array($uni, "body"));
