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


\CODOF\Access\Request::get('plugin/adv_role/user/:uid', function ($uid) {

    $uid = (int) $uid;

    $user = \CODOF\User\User::get($uid);

    return $user->getInfo();
});


class advrole
{

    const PLUGIN_NAME = "adv_role";
    const ASSET_FOLDER = 'assets';

    private $pluginAssetPath;

    public function __construct()
    {
        $this->pluginAssetPath = PLUGIN_PATH . self::PLUGIN_NAME . '/' . self::ASSET_FOLDER . '/';
    }


    public function includeJS()
    {
        $asset = new \CODOF\Asset\Stream();
        $asset->addJS($this->pluginAssetPath . 'js/adv_role.js', array('type' => 'defer'));
    }

}

$role = new advrole();
CODOF\Hook::add('tpl_before_forum_topics', array($role, "includeJS"));
