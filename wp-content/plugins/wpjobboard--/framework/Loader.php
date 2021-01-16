<?php
/**
 * Description of Loader
 *
 * @author greg
 * @package 
 */

class Daq_Loader
{
    private static $_dir = array();

    private static $_registeredAutoloader = false;

    private function __construct()
    {
        
    }

    public static function registerFramework($dir)
    {
        // check if registered Daq is newer
        // register if it is not
        self::$_dir["Daq"] = $dir;
    }

    public static function registerAutoloader()
    {
        if(self::$_registeredAutoloader) {
            return;
        }

        spl_autoload_register(array(__CLASS__, "autoload"));

        if(function_exists("__autoload")) {
            spl_autoload_register(array(__CLASS__, "autoloadProxy"));
        }

        self::$_registeredAutoloader = true;
    }

    public static function registerLibrary($prefix, $dir)
    {
        self::$_dir[$prefix] = $dir;
    }

    public static function autoload($class)
    {
        list($prefix) = explode("_", $class);
        if(isset(self::$_dir[$prefix])) {
            $class = str_replace($prefix."_", "", $class);
            $class = str_replace("_", "/", $class);
            $incFile = self::$_dir[$prefix]."/".$class.".php";
            if(file_exists($incFile)) {
                include_once $incFile;
            }
        }
    }
    
    public static function autoloadProxy($class)
    {
        __autoload($class);
    }
}

?>