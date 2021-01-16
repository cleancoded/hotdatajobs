<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author greg
 */
interface Daq_Form_Element_Interface 
{
    /**
     * Dumps the input to format readable by visual editor. 
     */
    public function dump();
    
    /**
     * Renders input HTML code 
     */
    public function render();
    
    /**
     * Validates input 
     */
    public function validate();
    
    /**
     * Updates input with data from visual editor 
     */
    public function overload(array $data);
    
    /**
     * Returns field type identifier (string) 
     */
    public function getType();
}

?>
