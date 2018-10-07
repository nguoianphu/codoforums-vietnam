<?php

/*
 * @CODOLICENSE
 */

class ConfigCreator
{

    /**
     * @var The SSO provider like Google, Facebook
     */
    private $provider;

    /**
     * Mapping of provider names used in front end to the keys saved in database
     */
    const MAPPING = [
        'GOOGLE' => 'GOOGLE',
        'FACEBOOK' => 'FB',
        'TWITTER' => 'TW'
    ];

    /**
     * @return array Hybridauth config array
     */
    public function get() {

        $key = strtoupper($this->provider);
        if(self::MAPPING[$key] != null)
            $key = self::MAPPING[$key];

        if ($key == 'FB')
            $id = number_format(\CODOF\Util::get_opt($key . '_ID'), 0, '', '');
        else
            $id = \CODOF\Util::get_opt($key . '_ID');

        $secret = \CODOF\Util::get_opt($key . '_SECRET');

        $config =  [

            'enabled' => true,

            /**
             * Required: Callback URL
             *
             * The callback url is the location where a provider (Google in this case) will redirect the use once they
             * authenticate and authorize your application. For this example we choose to come back to this same script.
             *
             * Note that Hybridauth provides an utility function `Hybridauth\HttpClient\Util::getCurrentUrl()` that can
             * generate the current page url for you and you can use it for the callback.
             */
            'callback' => RURI . "uni_login/authorize",

            /**
             * Required*: Application credentials
             *
             * A set of keys used by providers to identify your website (analogous to a login and password).
             * To acquire these credentials you'll have to register an application on provider's site. In the case of Google
             * for instance, you can refer to https://support.google.com/cloud/answer/6158849
             *
             * Application credentials are only required by providers using OAuth 1 and OAuth 2.
             */
            'keys' => [
                'id' => $id,
                'secret' => $secret
            ],


            /**
             * Optional: Debug Mode
             *
             * The debug mode is set to false by default, however you can rise its level to either 'info', 'debug' or 'error'.
             *
             * debug_mode: false|info|debug|error
             * debug_file: Path to file writeable by the web server. Required if only 'debug_mode' is not false.
             */
            'debug_mode' => 'debug',
            'debug_file' => __FILE__ . '.log',
        ];

        return [
            "providers" => [
                $this->provider => $config
            ]
        ];
    }


    public function __construct($provider)
    {
        $this->provider = $provider;
    }
}