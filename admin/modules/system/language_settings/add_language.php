<?php
$smarty = \CODOF\Smarty\Single::get_instance();
if (isset($_POST['language']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {
    $language = $_POST['language'];

    if (!empty($language)) {
        $parent_directory = "../sites/default/locale/";
        $default_language_file = $parent_directory . 'en_US/en_US.php';
        $directory = $language . '_' . $language;

        mkdir($parent_directory . $directory);
        $current_directory = $parent_directory . $directory . '/';
        $current_language_file_name = $directory . '.php';
        $absolute_current_file = $current_directory . $current_language_file_name;

        if (is_dir($current_directory)) {
            $file = fopen($current_directory . $current_language_file_name, "w");
            copy($default_language_file, $absolute_current_file);
        }

        header("Location: index.php?page=system/language_settings");
        exit(0);
    }
}
$content = $smarty->fetch('system/language_settings/add_language.tpl');