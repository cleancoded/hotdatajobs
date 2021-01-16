<?php
/**
* Description of Country
*
* @author greg
* @package 
*/

class Wpjb_List_Country
{
    private static $_list = array();

    public static function getByCode($code)
    {
        return self::_getBy('code', $code);
    }

    public static function getByAlpha2($code)
    {
        return self::_getBy('iso2', $code);
    }

    public static function getByAlpha3($code)
    {
        return self::_getBy('iso3', $code);
    }

    private static function _getBy($index, $code)
    {
        foreach(self::getAll() as $country) {
            if($country[$index] == $code) {
                return $country;
            }
        }
    }

    public static function getAll()
    {
        if(!empty(self::$_list)) {
            return self::$_list;
        }

        $file = "country_list.ini";
        $default = Wpjb_List_Path::getPath("app_config")."/".$file;
        $user = Wpjb_List_Path::getPath("user_config")."/".$file;

        if(is_file($user)) {
            self::$_list = Daq_Config::parseIni($user, null, true);
        } else {
            self::$_list = Daq_Config::parseIni($default, null, true);
        }

        return self::$_list;
    }
}

?>