<?php
$smarty = \CODOF\Smarty\Single::get_instance();
$parent_directory = "../sites/default/locale/";
$name = $_GET['name'];
$current_directory = $parent_directory . $name . '/';
$current_language_file_name = $name . '.php';
$flash = array('flash'=>false);
$smarty->assign('name', $name);


if (is_writable($parent_directory)) {
    if ( isset($_POST['CSRF_token']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {
        $CODOT = json_decode($_POST['language_json'],true);
        $smarty->assign('language_json', $_POST['language_json']);
        $current_directory = $parent_directory . $name . '/';
        $current_language_file_name = $name . '.php';
        $file = $current_directory . $current_language_file_name;
        $file_open = fopen($file, "w");
        $array = "<?php \n \$CODOT = " . var_export($CODOT, true) . ";";
        file_put_contents($file, $array);
        fclose($file_open);
        $flash = array('flash'=>true,'message'=>'Language saved successfully.');
        $smarty->assign('flash',$flash);
        $content = $smarty->fetch('system/language_settings/edit_language.tpl');
    } else {
        require($parent_directory . 'en_US/en_US.php');
        $english_values = $CODOT;
        $file = $current_directory . $current_language_file_name;
        require($file);
        $CODOT = array_merge($CODOT, $english_values);
        $language_json = json_encode($CODOT,JSON_PRETTY_PRINT);
        $smarty->assign('language_json',$language_json);
        $content = $smarty->fetch('system/language_settings/edit_language.tpl');
    }
} else {
    echo "The locale directory is not writable.";
}
