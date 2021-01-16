<?php

class Wpjb_Session_Transient implements Wpjb_Session_Interface {
    
    public function delete($key) {
        return delete_transient($key);
    }

    public function get($key) {
        return get_transient($key);
    }

    public function set($key, $data) {
        return set_transient($key, $data, 3600);
    }

}