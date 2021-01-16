<?php

class Wpjb_Session_Manager {
    
    protected $_session = null;
    
    public function __construct() {
        if(!defined("WPJB_SESSION") || WPJB_SESSION == "transient") {
            $interface = "Wpjb_Session_Transient";
        } else if( WPJB_SESSION == "session") {
            $interface = "Wpjb_Session_Session";
        } else {
            $interface = null;
        }
        
        $interface = apply_filters("wpjb_session_interface", $interface );
        
        $this->_session = new $interface();
    }
    
    public function set($key, $data) {
        return $this->_session->set($key, $data);
    }
    
    public function get($key) {
        return $this->_session->get($key);
    }
    
    public function delete($key) {
        return $this->_session->delete($key);
    }
}