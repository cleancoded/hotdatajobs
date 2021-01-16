<?php

class Wpjb_Shortcode_Dynamic extends Wpjb_Shortcode_Abstract {
    
    /**
     * Class contructor
     * 
     * @return void
     */
    public function __construct() {
        $this->view = new stdClass();
    }
}
