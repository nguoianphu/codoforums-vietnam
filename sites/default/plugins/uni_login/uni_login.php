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



$uni = new \UnifiedLogin();

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
dispatch_get('uni_login/login/:name', function ($name) use ($uni) {

    $uni->authenticate($name);
});



dispatch('uni_login/authorize', function () use ($uni) {

    $adapter = $uni->authenticate($_SESSION['provider']);
    header('Location: ' . CODOF\User\User::getProfileUrl());
});

class UnifiedLogin
{

    /**
     * this is called before smarty process html
     * you can add/remove resources like js/css here
     * you can also modify html using regex - but disabled
     *
     */
    public function head()
    {

        $asset = new \CODOF\Asset\Stream();
        $col = new \CODOF\Asset\Collection('head_col');
        $col->position = 'body';
        $col->addJS(PLUGIN_DIR . 'uni_login/assets/js/uni_login.js', array('name' => 'uni_login.js', 'type' => 'defer'));
        $col->addJS(PLUGIN_DIR . 'uni_login/assets/js/uni_login.js', array('name' => 'uni_login.js', 'type' => 'defer'));
        $col->addCSS(PLUGIN_DIR . 'uni_login/assets/css/uni_login.css', array('name' => 'uni_login.css'));

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
    public function body($dom)
    {

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


    /**
     * @param $provider
     * @return mixed
     */
    public function authenticate($provider) {

        /**
         * Get hybridauth configuration
         */
        require_once 'ConfigCreator.php';
        $configCreator = new ConfigCreator($provider);
        $config = $configCreator->get();

        /**
         * Initialize the required provider
         */

        $_SESSION['provider'] = $provider; //Is Session a good idea ?
        $adapter = new \Hybridauth\Hybridauth($config);

        try {
            /**
             * Sign in a user with Google
             *
             * The method `authenticate()` will attempt to connect to the Google API then redirect the user to Google's website for
             * authentication. If for whatever reason the process fails, Hybridauth will then raise an exception, however if the user
             * successfully authenticated, then any subsequent call to this method will be ignored.
             */
            $adapter = $adapter->authenticate($provider);

            $userProfile = $adapter->getUserProfile();
            $oauthId = md5($provider . $userProfile->identifier);
            $db = \DB::getPDO();

            $qry = 'SELECT id, username, avatar FROM ' . PREFIX . 'codo_users WHERE oauth_id=:oauth_id';
            $stmt = $db->prepare($qry);
            $stmt->execute(array(":oauth_id" => $oauthId));

            $username = CODOF\Filter::clean_username($userProfile->displayName);
            $profile = $stmt->fetch();

            if (!empty($profile)) {
                if ($username != $profile['username'] || $userProfile->photoURL != $profile['avatar']) {

                    //profile has been updated remotely
                    $qry = 'UPDATE ' . PREFIX . 'codo_users SET username=:name,avatar=:avatar WHERE oauth_id=:id';
                    $stmt = $db->prepare($qry);
                    $stmt->execute(array(":name" => $username, ":avatar" => $userProfile->photoURL, ":id" => $oauthId));
                }
                CODOF\User\User::login($profile['id']);
            } else {
                //no local copy of this profile yet
                $mail = $userProfile->email;
                $create_account = true;

                if ($mail == null) {

                    $mail = '';
                } else {

                    //we got an email, lets check if it exists
                    $qry = "SELECT id FROM " . PREFIX . "codo_users WHERE mail=:mail";
                    $stmt = $db->prepare($qry);
                    $stmt->execute(array(":mail" => $mail));
                    $res = $stmt->fetch();

                    if (!empty($res)) {

                        //looks like this user has already registered
                        $create_account = false;
                        CODOF\User\User::login($res['id']);
                    }
                }

                if ($create_account) {

                    $reg = new CODOF\User\Register($db);
                    $reg->mail = $mail;
                    $reg->name = $userProfile->firstName . ' ' . $userProfile->lastName;
                    $reg->oauth_id = $oauthId;
                    $reg->username = $username;
                    $reg->avatar = $userProfile->photoURL;
                    $reg->user_status = 1; //approved user
                    $reg->register_user();
                    $reg->login();
                }
            }

        } /**
         * Catch API Requests Errors
         *
         * This usually happen when requesting a:
         *     - Wrong URI or a mal-formatted http request.
         *     - Protected resource without providing a valid access token.
         */
        catch (\Hybridauth\Exception\HttpRequestFailedException $e) {
            echo 'Raw API Response: Failed. Please make sure callback uri is set. Error Message: '. $e->getMessage();
        } /**
         * This fellow will catch everything else
         */
        catch (\Exception $e) {
            echo 'Oops! We ran into an unknown issue: ' . $e->getMessage();
        }

        return $adapter;
    }
}


CODOF\Hook::add('tpl_before_user_login', array($uni, "head"));
CODOF\Hook::add('tpl_after_user_login', array($uni, "body"));
CODOF\Hook::add('tpl_before_user_register', array($uni, "head"));
CODOF\Hook::add('tpl_after_user_register', array($uni, "body"));


\CODOF\Hook::add('before_logout', function () {

    /**
     * Get hybridauth configuration
     */
    if(isset($_SESSION['provider'])) {
        require_once PLUGIN_DIR . 'uni_login/ConfigCreator.php';
        $configCreator = new \ConfigCreator($_SESSION['provider']);
        $config = $configCreator->get();
        $adapter = new \Hybridauth\Hybridauth($config);
        $adapter->disconnectAllAdapters();
    }
});