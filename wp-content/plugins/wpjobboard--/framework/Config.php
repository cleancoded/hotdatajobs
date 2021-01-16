<?php
/**
 * Config Class
 * 
 * Allows to manage configs loaded from ini files.
 *
 * @author Greg Winiarski
 * @package WPJobBoard
 * @subpackage Framework
 */

class Daq_Config
{
    /**
     * Parses ini text
     * 
     * @param string $str Ini file content
     * @param bool $ProcessSections
     * @return array Parsed data
     */
    public static function _parse($str, $ProcessSections=false){

        $lines  = explode("\n", $str);
        $return = Array();
        $inSect = false;
        foreach($lines as $line){
            $line = trim($line);
            if(!$line || $line[0] == "#" || $line[0] == ";")
                continue;
            if($line[0] == "[" && $endIdx = strpos($line, "]")){
                $inSect = substr($line, 1, $endIdx-1);
                continue;
            }
            if(!strpos($line, '=')) // (We don't use "=== false" because value 0 is not valid as well)
                continue;
            
            $tmp = explode("=", $line, 2);
			$tmp[1] = ltrim($tmp[1]);
			
			if($tmp[1]{0} == "\"") {
				$tmp[1] = trim($tmp[1], "\"");
			}
			
            if($ProcessSections && $inSect)
                $return[$inSect][trim($tmp[0])] = $tmp[1];
            else
                $return[trim($tmp[0])] = $tmp[1];
        }

        return $return;
    }
    
    /**
     * Parses ini file and returns data as array.
     * 
     * @param string $default Ini file to parse
     * @param string $ext Ini file which extends the $default
     * @param bool $sections Parse sections inside ini file
     * @return array Parsed data
     * @throws Exception If ini file does not exist
     */
    public static function parseIni($default, $ext = null, $sections = false)
    {
        if(!is_file($default)) {
            throw new Exception("Default config file [$default] does not exist.");
        }

        if(function_exists("parse_ini_string")) {
            $ini = parse_ini_string(file_get_contents($default), $sections);
        } elseif(function_exists("parse_ini_file")) {
            $ini = parse_ini_file($default, $sections);
        } else {
            $ini = self::_parse(file_get_contents($default), $sections);
        }

        if(!is_file($ext)) {
            return $ini;
        }

        if(function_exists("parse_ini_string")) {
            $ext = parse_ini_string(file_get_contents($ext), $sections);
        } elseif(function_exists("parse_ini_file")) {
            $ext = parse_ini_file($ext, $sections);
        } else {
            $ext = self::_parse(file_get_contents($ext), $sections);
        }
        
        foreach($ext as $k => $v) {
            if(isset($ini[$k]) && is_array($ini[$k])) {
                $ini[$k] = array_merge($ini[$k], $v);
            } else {
                $ini[$k] = $v;
            }
        }

        return $ini;
    }
}
