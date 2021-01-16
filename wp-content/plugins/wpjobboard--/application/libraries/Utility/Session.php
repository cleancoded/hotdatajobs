<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Transient sessions
 *
 * @author greg
 */
class Wpjb_Utility_Session extends Daq_Helper_Flash_Abstract 
{
    protected $_new = false;
    protected $_id = null;

    public function load() 
    {
        if($this->_loaded) {
            return;
        }
        
        $id = "_wpjb_transient_session_".str_replace("-", "_", wpjb_transient_id());
        $flash = wpjb_session()->get($id);
        $this->_id = $id;

        if($flash === "") {
            $this->_new = true;
        } 
        
        if(empty($flash)) {
            $this->_info = array();
            $this->_error = array();
        } else {
            $this->_info = $flash["info"];
            $this->_error = $flash["error"];
        }
        
        $this->_loaded = true;
    }
    
    public function dispose() 
    {
        $this->_info = array();
        $this->_error = array();
    }
    
    public function save() 
    {   
        
        $flash = array(
            "info" => $this->_info,
            "error" => $this->_error
        );

        $id = $this->_id;
        
        if(empty($this->_info) && empty($this->_error)) {
            wpjb_session()->delete($id);
        } else {
            wpjb_session()->set($id, $flash, 3600);
        }
    }
    
}
