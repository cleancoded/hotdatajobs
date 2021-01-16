<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Cash
 *
 * @author greg
 */
class Wpjb_Payment_Cash extends Wpjb_Payment_Abstract
{
    public function getEngine() {
        return "Cash";
    }
    public function getTitle() {
        return __("Cash", "wpjobboard");
        
    }
    public function processTransaction() {
        
    }
    public function render() {
        
    }
    public function getObject() {
        return null;
    }
}

?>
