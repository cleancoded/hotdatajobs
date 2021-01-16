<?php

class Wpjb_Shortcode_Employers_Search extends Wpjb_Shortcode_Abstract
{
    /**
     * Class constructor
     * 
     * Registers [wpjb_employers_search] shortcode if not already registered
     * 
     * @since 5.0
     * @return void
     */
    public function __construct() {
        if(!shortcode_exists("wpjb_employers_search")) {
            add_shortcode("wpjb_employers_search", array($this, "main"));
        }
    }
    
    /**
     * Displays login form
     * 
     * This function is executed when [wpjb_employers_search] shortcode is being called.
     * 
     * 
     * @param array     $atts   Shortcode attributes
     * @return string           Shortcode HTML
     */
    public function main($atts = array()) {
        return false;
    }
}
