<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Lang;

/*
 * 
 * Parses MO files
 */

class Parser {

    private $bigEndian = false;
    private $file = false;
    private $data = array();

    private function read_MO_data($bytes) {
        if ($this->bigEndian === false) {
            return unpack('V' . $bytes, fread($this->file, 4 * $bytes));
        } else {
            return unpack('N' . $bytes, fread($this->file, 4 * $bytes));
        }
    }

    public function get_translations($filename, $locale) {

        $this->data = array();
        $this->bigEndian = false;
        $this->file = @fopen($filename, 'rb');
        if (!$this->file) {
            throw new \Exception('Error opening translation file \'' . $filename . '\'.');
        }
        if (filesize($filename) < 10) {
            throw new \Exception('\'' . $filename . '\' is not a gettext file');
        }


        // get Endian
        $input = $this->read_MO_data(1);
        if (strtolower(substr(dechex($input[1]), -8)) == "950412de") {
            $this->bigEndian = false;
        } else if (strtolower(substr(dechex($input[1]), -8)) == "de120495") {
            $this->bigEndian = true;
        } else {
            throw new \Exception('\'' . $filename . '\' is not a gettext file');
        }
        // read revision - not supported for now
        $input = $this->read_MO_data(1);

        // number of bytes
        $input = $this->read_MO_data(1);
        $total = $input[1];

        // number of original strings
        $input = $this->read_MO_data(1);
        $OOffset = $input[1];

        // number of translation strings
        $input = $this->read_MO_data(1);
        $TOffset = $input[1];

        // fill the original table
        fseek($this->file, $OOffset);
        $origtemp = $this->read_MO_data(2 * $total);
        fseek($this->file, $TOffset);
        $transtemp = $this->read_MO_data(2 * $total);

        for ($count = 0; $count < $total; ++$count) {
            if ($origtemp[$count * 2 + 1] != 0) {
                fseek($this->file, $origtemp[$count * 2 + 2]);
                $original = @fread($this->file, $origtemp[$count * 2 + 1]);
                $original = explode("\0", $original);
            } else {
                $original[0] = '';
            }

            if ($transtemp[$count * 2 + 1] != 0) {
                fseek($this->file, $transtemp[$count * 2 + 2]);
                $translate = fread($this->file, $transtemp[$count * 2 + 1]);
                $translate = explode("\0", $translate);
                if ((count($original) > 1) && (count($translate) > 1)) {

                    $this->data[$locale][$original[0]] = $translate;
                    array_shift($original);
                    foreach ($original as $orig) {
                        $this->data[$locale][$orig] = '';
                    }
                } else {
                    $this->data[$locale][$original[0]] = $translate[0];
                }
            }
        }

        $this->data[$locale][''] = trim($this->data[$locale]['']);

        unset($this->data[$locale]['']);
        
        return $this->data;
    }

}
