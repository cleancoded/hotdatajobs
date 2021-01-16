<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Default
 *
 * @author Grzegorz
 */
class Wpjb_Form_Payment_Default extends Daq_Form_Abstract 
{
    public function init() 
    {
        $this->addGroup("default");
        
        $e = $this->create("fullname");
        $e->setLabel(__("Full Name", "wpjobboard"));
        $e->setRequired(true);
        $this->addElement($e, "default");
        
        $e = $this->create("email");
        $e->setLabel(__("Email", "wpjobboard"));
        $e->setRequired(true);
        $this->addElement($e, "default");
        
        apply_filters("wpjb_form_init_payment_default", $this);
    }
}
?>
