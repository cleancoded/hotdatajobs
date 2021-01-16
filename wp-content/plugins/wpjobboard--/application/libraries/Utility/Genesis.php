<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Genesis
 *
 * @author Grzegorz
 */
class Wpjb_Utility_Genesis 
{
    public static function connect() 
    {
        $self = new self();
        
        add_action("wp_head", array($self, "canonical"), -9999);
    }
    
    public static function canonical()
    {
        if(!is_wpjb() && !is_wpjr()) {
            return null;
        }
        
        remove_action( 'wp_head','genesis_canonical', 5);
    }
}

?>
