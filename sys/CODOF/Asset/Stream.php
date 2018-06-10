<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Asset;

/**
 * 
 * Asset manager 
 * -------------
 * 
 * Manages assets like js, css 
 * Minifies assets and caches them . 
 * 
 * 
 * Collection
 * ----------
 * 
 * Collection can contain 1 or more than one asset .
 * Collection is used to concatenate multiple files into one file .
 * 
 * Stream
 * ------
 * 
 * Everything in Stream will be part of html output
 */
class Stream {

    /**
     * Forcefully overwrites cache files
     * @var bool 
     */
    public $recache = false;

    /**
     * Path where assets are stored . This path will be used eveytime a 
     * relative url is passed while adding a new asset
     * @var string
     */
    public $asset_dir = ASSET_DIR;
    public $asset_url = ASSET_URL;

    /**
     * Directory relative to asset_dir where js files are stored
     * no trailing slash
     * @var string
     */
    public $js_dir = 'js';

    /**
     * Directory relative to asset_dir where css files are stored
     * no trailing slash
     * @var string
     */
    public $css_dir = 'css';

    /**
     * Directory where minified/concatenated files are cached
     * @var string 
     */
    protected $cache_path;
    protected $cache_url;

    /**
     * Stores css files of collection
     * @var array
     */
    public static $css = array();

    /**
     * Stores js files of collection
     * @var array
     */
    public static $js = array();
    private static $collections = array();
    private static $deferredJS = array();

    /**
     * Manager object to use methods that manage assets
     * @var object
     */
    private $manager;

//------------------------------------------------------------------------------

    public function __construct() {

        $this->cache_path = ABSPATH . 'cache/';
        $this->cache_url = str_replace("index.php?u=/", "", RURI) . 'cache/';
        $this->manager = new Manager;

        require_once SYSPATH . 'Ext/lessphp/lessc.inc.php';

        $this->lessc = new \lessc();
    }

    public function addCSS($asset, $options = false) {

        self::$css[] = $this->manager->add($asset, $options);

        return $this;
    }

    public function addJS($asset, $options = false) {

        self::$js[] = $this->manager->add($asset, $options);

        return $this;
    }

    public function dumpJS($pos) {

        $ujs = array_merge(self::$js, self::$collections);
        uasort($ujs, array($this->manager, 'order_cmp'));

        $html = '';
        foreach ($ujs as $_js) {

            $js = (array) $_js;

            if ($js['position'] != $pos) {
                continue;
            }

            if (is_object($_js)) {

                $html .= $this->pipeline($_js->js, 'js');
            } else if ($js['type'] == 'defer') {

                if ($this->is_remote($js['data'])) {

                    $url = $js['data'];
                } else if (!file_exists($js['data'])) {

                    $url = $this->build_path($js['data'], 'js');
                } else {

                    $url = $js['data'];
                }
                array_push(self::$deferredJS, $url);
            } else {

                $html .= $this->mk_script($js['data'], $js['type']);
            }
        }

        return $html;
    }

    public function dumpCSS() {

        $ucss = array_merge(self::$css, self::$collections);

        uasort($ucss, array($this->manager, 'order_cmp'));
        $html = '';
        foreach ($ucss as $_css) {

            if (is_object($_css)) {

                $this->prependURL = $_css->prependURL;
                $html .= $this->pipeline($_css->css, 'css');
            } else {

                $css = (array) $_css;
                $html .= $this->mk_link($css['data'], $css['type']);
            }
        }

        return $html;
    }

    /**
     * Returns array of urls of deferred js
     * @return array
     */
    public function deferred() {

        return self::$deferredJS;
    }

    private function pipeline($files, $type, $prepend = '') {

        $contents = '';
        $deferredContents = '';
        $urls = array();
        $remoteUrls = array();
        $html = '';

        
        foreach ($files as $file) {

            if (!$file)
                continue;
            if ($file['type'] == 'file' || $file['type'] == 'defer' || $file['type'] == 'remote') {

                if ($this->is_remote($file['data'])) {

                    if (!ini_get('allow_url_fopen') || $file['type'] == 'remote') {

                        //oops we can't get contents of this remote file
                        //add them to a new array of remote files
                        $remoteUrls[] = array("data" => $file['data'], "type" => $file['type']);
                        continue;
                    } else {
                        $contents .= file_get_contents($file['data']);
                    }
                } else {

                    if (!file_exists($file['data'])) {

                        $file['data'] = $this->asset_dir . $this->js_dir . '/' . $file['data'];
                    }
                }

                if ($file['type'] == 'file' && file_exists($file['data'])) {

                    $contents .= file_get_contents($file['data']);
                } else if ($file['type'] == 'defer') {

                    $deferredContents .= file_get_contents($file['data']);
                }
            } else {

                $urls[] = array("data" => $file['data'], "type" => $file['type']);
            }
        }

        $content_files = array("file" => $contents, "defer" => $deferredContents);

        foreach ($content_files as $assetType => $content) {
            if ($content != '') {

                $name = md5($content) . '.' . $type;
                if ($type == 'js') {
                    $url = $this->cache_path . 'js/' . $name;
                } else {

                    $url = $this->cache_path . 'css/' . $name;
                }

                if (!file_exists($url) || $this->recache) {

                    $compiledContent = $this->compile($content, $type);
                    file_put_contents($url, $compiledContent);
                }

                $urls[] = array("data" => $name, "type" => $assetType);
            }
        }

        //remote urls should be loaded after local urls
        //irrespective of order
        //TODO: Improve this
        $allUrls = array_merge($urls, $remoteUrls);

        foreach ($allUrls as $url) {

            if ($url['type'] == 'defer') {

                if (!$this->is_remote($url['data'])) {

                    $url['data'] = $this->build_path_cache($url['data'], 'js');
                }
                array_push(self::$deferredJS, $url['data']);
                continue;
            }
            if ($type == 'js') {
                $html .= $this->mk_script($url['data'], $url['type'], 'cache');
            } else {
                $html .= $this->mk_link($url['data'], $url['type'], 'cache');
            }
        }

        return $html;
    }

    /**
     * 
     * Minifies and compiles less to css
     * @param string $contents
     * @param string $type
     * @return string
     */
    private function compile($contents, $type, $prependURL = '') {

        if ($type == 'js') {

            //$contents = \Ext\JSMin::minify($contents);
        } else {

            $contents = \Ext\MinifyCssUriRewriter::prepend($contents, $this->prependURL . 'css/');
            $contents = $this->compile_less($contents);
            $contents = \Ext\CSSmin::process($contents);
        }

        return $contents;
    }

    /**
     * Determine whether the path is local or remote
     * 	 
     * @param  string $path
     * @return bool
     */
    private function is_remote($path) {
        return ('http://' == substr($path, 0, 7) || 'https://' == substr($path, 0, 8));
    }

    private function build_path($path, $type) {

        if ($type == 'js') {

            return $this->asset_url . $this->js_dir . '/' . $path;
        } else {

            return CURR_THEME . $this->css_dir . '/' . $path;
        }
    }

    private function build_path_cache($path, $type) {

        if ($type == 'js') {

            return $this->cache_url . 'js/' . $path;
        } else {

            return $this->cache_url . 'css/' . $path;
        }
    }

    private function mk_script($data, $type, $cache = false) {

        $html = '';
        if ($type == 'file' || $type == 'remote') {

            if (!$this->is_remote($data)) {

                if ($cache) {
                    $data = $this->build_path_cache($data, 'js');
                } else {
                    $data = $this->build_path($data, 'js');
                }
            }

            $html = "<script src='" . $data . "' type='text/javascript'></script>\n";
        } else if ($type == 'inline') {

            $html = "\n<script type='text/javascript'>" . $data . "</script>\n";
        }

        return $html;
    }

    /**
     * 
     * @return link href tag or style tag for css
     */
    private function mk_link($data, $type, $cache = false) {

        $html = '';

        if ($type == 'file') {

            if (!$this->is_remote($data)) {
                if ($cache) {
                    $data = $this->build_path_cache($data, 'css');
                } else {
                    $data = $this->build_path($data, 'css');
                }
            }
            $html = "<link href='" . $data . "' rel='stylesheet' type='text/css'>\n";
        } else if ($type == 'inline') {

            $html = "<style type='text/css'>" . $data . "</style>\n";
        }

        return $html;
    }

    /**
     * Converts less to css
     */
    private function compile_less($less) {

        $css = $this->lessc->compile($less);

        return $css;
    }

    /**
     * 
     * Adds a Collection object 
     * @param \CODOF\Asset\Collection $collection
     * @return \CODOF\Asset\Stream
     */
    public function addCollection(Collection $collection) {

        if (!isset(self::$collections[$collection->name])) {

            self::$collections[$collection->name] = $collection;
        } else {

            self::$collections[$collection->name]->css = array_merge(self::$collections[$collection->name]->css, $collection->css);
            self::$collections[$collection->name]->js = array_merge(self::$collections[$collection->name]->js, $collection->js);
        }

        return $this;
    }

}
