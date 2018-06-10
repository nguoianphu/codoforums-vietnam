<?php

namespace Controller;

/**
 * Serves static files
 */
class Serve {

    /**
     * Serves forum attachments
     *
     * @return void
     */
    public function attachment() {

        $this->serve('assets/img/attachments/');
    }

    /**
     * Serves forum attachments preview
     *
     * @return void
     */
    public function previewAttachment() {
        
        $this->serve('assets/img/attachments/preview/');
    }
     
    /**
     * Serves the attachment
     *
     * @return void
     */
    private function serve($path) {

        $name = $this->sanitize($_GET['path']);
        $dir = DATA_PATH . $path;

        $path = $this->setBasicheaders($name, $dir);
        header('Content-Disposition: attachment; filename="' . $this->getRealFileName($name) . '"');
        @readfile($path);        
        exit;
    }

    /**
     * Serves smileys
     *
     * @return void
     */
    public function smiley() {

        $name = $this->sanitize($_GET['path']);
        $dir = DATA_PATH . 'assets/img/smileys/';

        $path = $this->setBasicheaders($name, $dir);
        @readfile($path);        
        exit;
    }

    /**
     * Gets the original name of file from table from hash
     * If not found a dummy name is returned
     *
     * @param [type] $hash
     * @return void
     */
    private function getRealFileName($hash) {

        $dummyName = "download";
        $name = \DB::table(PREFIX . 'codo_attachments')
            ->where('visible_hash', '=', $hash)
            ->pluck('original_name');

        return $name ? $name : $dummyName;
    }

    /**
     * Sets some basic headers for serving file
     *
     * @param [string] $name
     * @param [string] $dir
     * @return void
     */
    private function setBasicheaders($name, $dir) {

        $filename = $dir . $name;
        $mime_type = mime_content_type($filename);

        header("Content-type: $mime_type");
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', strtotime("+6 months")), true);
        header("Pragma: public");
        header("Cache-Control: public");

        return $filename;
    }

    /**
     * Some security checks/sanitization
     *
     * @param [string] $name
     * @return string
     */
    private function sanitize($name) {

        $name = str_replace("..", "", $name);
        $name = str_replace("%2e%2e", "", $name);

        return $name;
    }

}
