<?php
/**
 * Description of Seal
 *
 * @author greg
 * @package 
 */

class Wpjb_Utility_Seal
{
    const NOT_EXISTS = -1;

    const INVALID = -2;

    const INVALID_URL = -3;

    private static $_seal = null;

    private static function _exists()
    {
        if(self::$_seal) {
            return true;
        }

        $file = Wpjb_List_Path::getPath("seal_file");
        if(file_exists($file)) {
            $contents = file_get_contents($file);
            self::$_seal = unserialize(base64_decode($contents));
        }

        if(is_array(self::$_seal) && count(self::$_seal) >= 4) {
            return true;
        }

        return false;
    }

    private static function _checksum()
    {
        $seal = self::$_seal;
        if(md5($seal[0].$seal[1].(int)$seal[2].$seal[3].$seal[4]) != $seal[5]) {
            return false;
        } else {
            return true;
        }
    }

    private static function _valid()
    {
        if(stripos(site_url(), self::$_seal[1]) !== false) {
            return true;
        } elseif(stripos(site_url(), "localhost") !== false) {
            return true;
        } else {
            return false;
        }
    }

    public static function check()
    {
        if(!self::_exists()) {
            return self::NOT_EXISTS;
        }
        if(!self::_checksum()) {
            return self::INVALID;
        }
        if(!self::_valid()) {
            return self::INVALID_URL;
        }

        return true;
    }

    public static function id()
    {
        return self::$_seal[4];
    }

    public static function owner()
    {
        return self::$_seal[0];
    }

    public static function domain()
    {
        return self::$_seal[1];
    }

    public static function checksum()
    {
        return self::$_seal[5];
    }

}

?>