<?php
$smarty = \CODOF\Smarty\Single::get_instance();

if (isset($_POST['language']) && CODOF\Access\CSRF::valid($_POST['CSRF_token'])) {
    $language = $_POST['language'];

    if (!empty($language)) {
       \CODOF\Util::set_opt('default_language',$language);
    }
}

header("Location: index.php?page=system/language_settings");
exit(0);