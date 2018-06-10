<?php

/* Page dependent stylesheets */

$asset = new \CODOF\Asset\Stream();

$col = new \CODOF\Asset\Collection('head_col');
$col->addJS('jquery-1.10.2.min.js', array('type' => 'defer'))
        ->addJS('fastclick.js', array('type' => 'defer'))
        ->addJS('bootstrap.min.js', array('type' => 'defer'))
        ->addJS('notify.js', array('type' => 'defer'))
        ->addJS('app.js', array('type' => 'defer'))
        ->addJS('jquery.mmenu.min.js', array('type' => 'defer'))
        ->addJS('jquery.mmenu.dragopen.min.js', array('type' => 'defer'))
        ->addJS('hammer.min.js', array('type' => 'defer'));


$global_less = array('mixins', 'bootstrap', 'general', 'search', 'jquery.mmenu','jquery.mmenu.dragopen');

if ($css_files == null) {

    $css_files = array();
}

//$css_files are page dependent less files
$files = array_merge($global_less, $css_files);
$path = DEF_THEME_DIR . 'less';


//Add global & page-dependent less files defined by controllers
foreach ($files as $file) {

    $col->addCSS("$path/$file.less");
}

$curr_theme_less = array('colors');
$path = CURR_THEME_PATH . 'less';


//Add custom less files
foreach ($curr_theme_less as $file) {

    $col->addCSS("$path/$file.less");
}

$asset->addCollection($col);

$mycol = new \CODOF\Asset\Collection('head_mycol');
$mycol->prependURL = CURR_THEME;
$mycol->addCSS("$path/colors.less")
        ->addCSS("$path/custom.less");

$asset->addCollection($mycol);


$colb = new \CODOF\Asset\Collection('head_col');
$colb->position = 'body';
$colb->addJS('handlebars-v1.1.2.js', array('type' => 'defer'));


// Add page-dependent js files defined by controllers 
foreach ($js_files as $js_file) {

    if (is_array($js_file)) {

        $colb->addJS($js_file[0], $js_file[1]);
    } else {
        $colb->addJS($js_file);
    }
}

$asset->addCollection($colb);

