<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HelpScreen
 *
 * @author greg
 */
class Wpjb_Utility_HelpScreen 
{
    protected $_menu = array();
    
    public function addPage($key, $value)
    {
        $this->_menu[$key] = $value;
    }
    
    public function __call($k, $v)
    {
        //echo "Unknown [$k]";
    }
    
    public function load_jobs()
    {
        $screen = WP_Screen::get($this->_menu["jobs"]);
        /*
        $screen->add_option( 
            'per_page', 
            array(
                'label' => 'Entries per page', 
                'default' => 20, 
                'option' => 'edit_per_page'
            ) 
        );
*/
        add_meta_box(
            'my_meta_id',
            'My Metabox',
            "myplugin_inner_custom_box",
            $this->_menu["jobs"]
        );
        
    }
}

?>
