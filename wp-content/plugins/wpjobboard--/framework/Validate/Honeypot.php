<?php

class Daq_Validate_Honeypot
    extends Daq_Validate_Abstract implements Daq_Validate_Interface {

    protected static $once = false;
    
    public function isValid($value) {
        if($value !== "") {
            $this->log();
            return false;
        } else {
            return true;
        }
    }
    
    public function log() {
        
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
        
        $m = "%s - %s filled honeypot field.";
        array_unshift($data, sprintf($m, current_time('mysql', false), $_SERVER['REMOTE_ADDR']));

        update_option("wpjb_antispam_log", maybe_serialize($data));
        
        self::$once = true;
        
    }
}