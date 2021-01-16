<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Log
 *
 * @author Grzegorz
 * 
 * define("WPJB_LOG", "10000000"); // debug only
 * define("WPJB_LOG", "01000000"); // error only
 * define("WPJB_LOG", "00100000"); // mail only
 * define("WPJB_LOG", "00010000"); // cron only
 * 
 * define("WPJB_LOG", "11000000"); // debug+error
 * define("WPJB_LOG", "10100000"); // debug+mail
 * define("WPJB_LOG", "10010000"); // debug+cron
 * 
 * define("WPJB_LOG", "11100000"); // debug+error+mail
 * define("WPJB_LOG", "11010000"); // debug+error+cron
 * 
 * define("WPJB_LOG", "11110000"); // all errors
 * 
 */
class Wpjb_Utility_Log 
{
    const DEBUG = 1;
    const ERROR = 2;
    const MAIL  = 3;
    const CRON  = 4;
    
    public static function log($level, $message)
    {
        if(!self::_enabled($level)) {
            return false;
        }
        
        switch($level) {
            case self::DEBUG: $file = "debug-*.log"; break;
            case self::ERROR: $file = "error-*.log"; break;
            case self::MAIL : $file = "mail-*.log"; break;
            case self::CRON : $file = "cron-*.log"; break;
            default: 
                throw new Exception("Invalid Log Level ($level).");
        }
        
        $dir = wp_upload_dir();
        $dir = rtrim($dir["basedir"], "/")."/wpjobboard-log/";
        
        if(!is_dir($dir)) {
            mkdir($dir);
        }
        
        $filepath = $dir.$file;
        $filelist = glob($filepath);
        
        if(!isset($filelist[0])) {
            $filename = str_replace("*", md5(time().uniqid()), $filepath);
        } else {
            $filename = $filelist[0];
            
            if(filesize($filename) > 10485760) {
                $count = count((array)glob($filepath."-*"))+1;
                rename($filename, $filename."-".$count);
            }
        }
        
        $r = file_put_contents($filename, "[".date("Y-m-d H:i:s")."]".print_r($message, true)."\r\n", FILE_APPEND);
        
        if($r > 0) {
            return true;
        } else {
            return false;
        }
        
    }
    
    protected static function _enabled($level)
    {
        if(!defined("WPJB_LOG")) {
            return false;
        }

        $bin = WPJB_LOG;
        
        if(isset($bin[$level]) && $bin[$level] == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function debug($m)
    {
        return self::log(self::DEBUG, $m);
    }
    
    public static function error($m)
    {
        return self::log(self::ERROR, $m);
    }
    
    public static function mail($m)
    {
        return self::log(self::MAIL, $m);
    }
    
    public static function cron($m)
    {
        return self::log(self::MAIL, $m);
    }
}

?>
