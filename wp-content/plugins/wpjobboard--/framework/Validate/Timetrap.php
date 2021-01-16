<?php

class Daq_Validate_Timetrap
    extends Daq_Validate_Abstract implements Daq_Validate_Interface {
    
    protected static $once = false;
    
    public function isValid($value) {
        $t1 = self::decode($value);
        $t2 = current_time('timestamp');

        if(absint($t1) <= 0) {
            $this->log(false);
            return false;
        } elseif($t2 - $t1 < wpjb_conf("timetrap_delta", 2)) {
            $this->log($t2-$t1);
            return false;
        } else {
            return true;
        }
    }

    public function log($delta) {
        
        if(self::$once === true) {
           return; 
        }
        
        $data = maybe_unserialize(get_option("wpjb_antispam_log"));
        
        if(!is_array($data)) {
            $data = array();
        }
        
        if(count($data) >= 100) {
            array_pop($data);
        }
        
        if($delta === false) {
            $m = "%s - %s provided invalid timetrap code.";
            array_unshift($data, sprintf($m, current_time('mysql', false), $_SERVER['REMOTE_ADDR']));
        } else {
            $m = "%s - %s submitted form to fast [%d seconds]";
            array_unshift($data, sprintf($m, current_time('mysql', false), $_SERVER['REMOTE_ADDR'], $delta));
        }
        update_option("wpjb_antispam_log", maybe_serialize($data));
        
        self::$once = true;
        
    }
    
    public static function current() {
        return current_time('timestamp');
    }

    public static function encode( $value, $key = null ) {
        if($key === null) {
            $key = wpjb_conf("timetrap_key", substr(md5(AUTH_KEY), 4, 12));
        }
        $result = '';
        for($i=0; $i<strlen($value); $i++) {
            $char = substr($value, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char = chr(ord($char)+ord($keychar));
            $result .= $char;
        }
        return apply_filters("wpjb_timetrap_encode", base64_encode($result), $value);
    }

    public static function decode( $value, $key = null ) {
        if($key === null) {
            $key = wpjb_conf("timetrap_key", substr(md5(AUTH_KEY), 4, 12));
        }
        $result = '';
        $string = base64_decode($value);
        for($i=0; $i<strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char = chr(ord($char)-ord($keychar));
            $result.=$char;
        }
        return apply_filters("wpjb_timetrap_decode", $result, $value);
    }
}