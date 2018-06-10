<?php

namespace Controller;

class serve {

    public function attachment() {

        $name = $this->sanitize($_GET['path']);
        $dir = DATA_PATH . 'assets/img/attachments/';

        $this->set_headers($name, $dir);
    }

    public function smiley() {

        $name = $this->sanitize($_GET['path']);
        $dir = DATA_PATH . 'assets/img/smileys/';

        $this->set_headers($name, $dir);
    }

    private function set_headers($name, $dir) {

        $filename = $dir . $name;

        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $mime_type = \CODOF\File\Extension::get_mime_type($ext);




        //session_cache_limiter(false);
        //header('Cache-Control: private');
        // Checking if the client is validating his cache and if it is current.
        /*if (isset($_SERVER["HTTP_IF_MODIFIED_SINCE"]) && (strtotime($_SERVER["HTTP_IF_MODIFIED_SINCE"]) == filemtime($filename))) {
            // Client's cache IS current, so we just respond '304 Not Modified'.
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filename)) . ' GMT', true, 304);
        } else*/ {
            // Image not cached or cache outdated, we respond '200 OK' and output the image.
            //@readfile($filename);
            //exit($filename);
            header("Content-type: $mime_type");

            header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', strtotime("+6 months")), true);
            header("Pragma: public");
            header("Cache-Control: public");
            @readfile($filename);
        }
        exit;
    }

    private function sanitize($name) {

        $name = str_replace("..", "", $name);
        $name = str_replace("%2e%2e", "", $name);

        return $name;
    }

}
