<?php

interface Wpjb_Session_Interface {
    public function set($key, $data);
    public function get($key);
    public function delete($key);
}