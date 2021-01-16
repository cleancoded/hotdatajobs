<?php
/**
 * Description of Path
 *
 * @author greg
 * @package 
 */

class Wpjb_List_Path
{
    private static $_instance = null;

    private $_path = array();

    private function __construct()
    {
        $baseDir = Wpjb_Project::getInstance()->getProjectBaseDir();
        $this->_path = Daq_Config::parseIni($baseDir."/application/config/paths.ini");
    }

    public static function getInstance()
    {
        if(self::$_instance === null) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public static function getPath($key)
    {
        $instance = self::getInstance();
        $baseDir = Wpjb_Project::getInstance()->getProjectBaseDir();
        return $baseDir.$instance->_path[$key];
    }

    public static function getRawPath($key)
    {
        $instance = self::getInstance();
        return $instance->_path[$key];
    }
}

?>