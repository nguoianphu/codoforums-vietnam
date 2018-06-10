<?php

namespace CODOF\Upgrade;

/*
 * @CODOLICENSE
 */

/**
 * 
 * Codoforum Upgrader
 */
class Upgrade {

    /**
     * Checks if the required php version is compatible with installed php
     * @param float $required_php_version
     * @return bool
     */
    private function isCompatible($required_php_version) {

        return version_compare(PHP_VERSION, $required_php_version, '>=');
    }

    /**
     * Checks if passed version is lower than currently installed version
     * @param type $version
     * @return type
     */
    public function currentVersionGreaterThan($version) {

        \CODOF\Util::get_config(\DB::getPDO());
        $versionInstalled = \CODOF\Util::get_opt('version');
        return (version_compare($versionInstalled, $version) == 1);
    }

    /**
     * Upgrades codoforum from(excluding) version specified
     * @param type $from
     */
    public function upgradeDB($from) {

        $files = array();
        $names = array();
        foreach (glob(ABSPATH . "install/upgrade/*.php") as $filename) {


            $parts = explode("/", $filename);
            $name = str_replace(".php", "", array_pop($parts));

            $files[$name] = $filename;
            $names[] = $name;
        }

        natsort($names);
        //var_dump($files);
        foreach ($names as $name) {

            if (version_compare($from, $name) === -1) {

            	//run if file is greater than installed plugin version
                require $files[$name];
                //var_dump($files[$name]);
            }
        }
    }

    /**
     * Check if table exists
     * @param type $table
     * @return type
     */
    public static function tableExists($table) {

        $val = \DB::select('select 1 from ' . $table . ' LIMIT 1');

        return $val;
    }
    
    /**
     * Checks if column exists for table
     * @param type $table
     * @param type $col
     * @return type
     */
    public static function columnExists($table, $col) {

        $pdo = \DB::getPDO();
        $res = $pdo->query("SHOW COLUMNS FROM $table LIKE '$col'");

        return (bool) $res->fetch();
    }
    

}
