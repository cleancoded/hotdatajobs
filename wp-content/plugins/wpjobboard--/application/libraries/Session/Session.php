<?php

class Wpjb_Session_Session implements Wpjb_Session_Interface {
    
    public function __construct() {
        if(!session_id()) {
            session_start();
        }
    }
    
    public function delete($key) {
        if(isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function get($key) {
        if(isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
            return null;
        }
    }

    public function set($key, $data) {
        if(!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = array();
        }
        
        $_SESSION[$key] = $data;
    }

}
