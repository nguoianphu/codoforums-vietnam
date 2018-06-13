/*
@CODOLICENSE
*/

<?php 

header("Content-Type: text/css");

?>

/*
1. Grid
2. Navbar
*/
@import "bootstrap.less";

@import "mixins.less";

@import "general.less";



<?php
if (isset($_GET['css_files']) && $_GET['css_files'] != 'null') {

    $css_files = json_decode($_GET['css_files']);

    foreach ($css_files as $css_file) {
        ?>
          <?php require "$css_file.less" ?>;

        <?php
    }
}

